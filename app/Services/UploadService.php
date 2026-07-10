<?php

namespace App\Services;

use Aws\CommandPool;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class UploadService
{
    /** Target maximum size for a stored image. */
    private const MAX_BYTES = 500 * 1024;

    /** Images wider than this are downscaled before compression. */
    private const MAX_WIDTH = 1500;

    /** Max simultaneous in-flight uploads to Spaces. */
    private const UPLOAD_CONCURRENCY = 5;

    public function __construct()
    {
    }

    /**
     * Compress an uploaded image to ≤500KB using GD.
     * Resizes to max 1500px wide, then iteratively reduces JPEG quality.
     *
     * @return array{0: string, 1: bool} [path, isTemp] — when isTemp is true the
     *         caller owns the returned temp file and must delete it.
     */
    private function compressImage(UploadedFile $file): array
    {
        if ($file->getSize() <= self::MAX_BYTES) {
            return [$file->getRealPath(), false];
        }

        $mime = $file->getMimeType();
        $path = $file->getRealPath();

        // Cheap header read (no full decode) so we can guarantee enough memory.
        $info = @getimagesize($path);
        if ($info) {
            $this->ensureMemoryFor((int) $info[0], (int) $info[1]);
        }

        $source = match (true) {
            in_array($mime, ['image/jpeg', 'image/jpg']) => @imagecreatefromjpeg($path),
            $mime === 'image/png'  => @imagecreatefrompng($path),
            $mime === 'image/webp' => @imagecreatefromwebp($path),
            $mime === 'image/gif'  => @imagecreatefromgif($path),
            default                => null,
        };

        if (!$source) {
            // Unknown/undecodable format — upload the original untouched.
            return [$path, false];
        }

        $origW = imagesx($source);
        $origH = imagesy($source);

        if ($origW > self::MAX_WIDTH) {
            $ratio  = self::MAX_WIDTH / $origW;
            $newW   = self::MAX_WIDTH;
            $newH   = (int) ($origH * $ratio);
            $canvas = imagecreatetruecolor($newW, $newH);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($source);
            $source = $canvas;
            $curW   = $newW;
            $curH   = $newH;
        } else {
            // Flatten transparency for non-JPEG sources.
            if ($mime !== 'image/jpeg' && $mime !== 'image/jpg') {
                $canvas = imagecreatetruecolor($origW, $origH);
                imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
                imagecopy($canvas, $source, 0, 0, 0, 0, $origW, $origH);
                imagedestroy($source);
                $source = $canvas;
            }
            $curW = $origW;
            $curH = $origH;
        }

        // NOTE: use the tempnam() path directly. Appending an extension would
        // orphan the zero-byte file tempnam() creates, leaking it into the
        // system temp dir on every compressed upload. The stored object's
        // extension is derived from the file's contents, not its name.
        $tempPath = tempnam(sys_get_temp_dir(), 'bebki_img_');

        // Step down quality until ≤500KB.
        for ($quality = 85; $quality >= 30; $quality -= 10) {
            imagejpeg($source, $tempPath, $quality);
            if (filesize($tempPath) <= self::MAX_BYTES) {
                imagedestroy($source);
                return [$tempPath, true];
            }
        }

        // Still too big — halve the dimensions and retry.
        $newW   = max(1, (int) ($curW / 2));
        $newH   = max(1, (int) ($curH / 2));
        $canvas = imagecreatetruecolor($newW, $newH);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newW, $newH, $curW, $curH);
        imagedestroy($source);
        $source = $canvas;

        for ($quality = 85; $quality >= 30; $quality -= 10) {
            imagejpeg($source, $tempPath, $quality);
            if (filesize($tempPath) <= self::MAX_BYTES) {
                break;
            }
        }

        imagedestroy($source);
        return [$tempPath, true];
    }

    /**
     * Upload a batch of images to a storage path.
     *
     * Images are compressed sequentially (CPU-bound) and then uploaded to
     * Spaces concurrently (network-bound), which is the big win for
     * multi-image requests. Failures are logged with context. If images were
     * provided but every one failed, a RuntimeException is thrown so the caller
     * returns an error instead of a misleading "success" with missing images.
     * Partial success returns the URLs that did upload.
     *
     * @param  mixed  $images     Array of UploadedFile (from $request->file()).
     * @param  bool   $onlyFirst  Only upload the first file (single-image fields).
     * @return array<int, string>
     */
    private function uploadMany($images, string $storagePath, bool $onlyFirst = false): array
    {
        if (!is_array($images)) {
            return [];
        }

        if ($onlyFirst) {
            $images = isset($images[0]) ? [$images[0]] : [];
        }

        $images = array_values(array_filter($images, fn ($i) => $i instanceof UploadedFile));

        if ($images === []) {
            return [];
        }

        // 1. Compress every image up front (GD is CPU-bound; running these in
        //    parallel in a single PHP process would not help).
        $jobs = [];
        foreach ($images as $image) {
            [$compressed, $isTemp] = $this->compressImage($image);
            $ext = $isTemp
                ? 'jpg'
                : (strtolower($image->getClientOriginalExtension()) ?: ($image->guessExtension() ?: 'bin'));

            $jobs[] = [
                'key'          => trim($storagePath, '/') . '/' . Str::random(40) . '.' . $ext,
                'path'         => $compressed,
                'isTemp'       => $isTemp,
                'contentType'  => $isTemp ? 'image/jpeg' : ($image->getMimeType() ?: 'application/octet-stream'),
                'originalName' => $image->getClientOriginalName(),
            ];
        }

        try {
            $succeeded = $this->uploadJobsConcurrently($jobs);

            // Safety net: if the concurrent path uploaded nothing at all (e.g.
            // a subtle raw-SDK parameter issue), retry through the proven
            // Flysystem path before reporting failure. Worst case, behavior
            // degrades to exactly how it worked before this optimization.
            if (array_filter($succeeded) === []) {
                $succeeded = $this->uploadJobsSequentially($jobs);
            }
        } catch (\Throwable $e) {
            // Any problem wiring up the raw S3 client / pool: fall back to the
            // battle-tested sequential Flysystem path so uploads still work.
            Log::warning('Concurrent upload unavailable, using sequential fallback', [
                'error' => $e->getMessage(),
            ]);
            $succeeded = $this->uploadJobsSequentially($jobs);
        } finally {
            foreach ($jobs as $job) {
                if ($job['isTemp'] && is_file($job['path'])) {
                    @unlink($job['path']);
                }
            }
        }

        $urls = [];
        foreach ($jobs as $i => $job) {
            if (!empty($succeeded[$i])) {
                $urls[] = $this->toCdnUrl(Storage::disk('spaces')->url($job['key']));
            }
        }

        if ($urls === []) {
            throw new RuntimeException('All image uploads failed.');
        }

        return $urls;
    }

    /**
     * Upload all jobs in parallel via the AWS CommandPool.
     *
     * @param  array<int, array{key:string,path:string,contentType:string,originalName:string}>  $jobs
     * @return array<int, bool>  Map of job index => success.
     */
    private function uploadJobsConcurrently(array $jobs): array
    {
        $client = Storage::disk('spaces')->getClient();
        $bucket = config('filesystems.disks.spaces.bucket');
        $succeeded = [];

        $commands = function () use ($client, $bucket, $jobs) {
            foreach ($jobs as $i => $job) {
                yield $i => $client->getCommand('PutObject', [
                    'Bucket'      => $bucket,
                    'Key'         => $job['key'],
                    'SourceFile'  => $job['path'],
                    'ACL'         => 'public-read',
                    'ContentType' => $job['contentType'],
                ]);
            }
        };

        (new CommandPool($client, $commands(), [
            'concurrency' => self::UPLOAD_CONCURRENCY,
            'fulfilled'   => function ($result, $i) use (&$succeeded) {
                $succeeded[$i] = true;
            },
            'rejected'    => function ($reason, $i) use ($jobs) {
                Log::error('Image upload failed', [
                    'key'      => $jobs[$i]['key'],
                    'original' => $jobs[$i]['originalName'],
                    'error'    => $reason instanceof \Throwable ? $reason->getMessage() : (string) $reason,
                ]);
            },
        ]))->promise()->wait();

        return $succeeded;
    }

    /**
     * Sequential Flysystem upload fallback.
     *
     * @param  array<int, array{key:string,path:string,contentType:string,originalName:string}>  $jobs
     * @return array<int, bool>  Map of job index => success.
     */
    private function uploadJobsSequentially(array $jobs): array
    {
        $succeeded = [];
        foreach ($jobs as $i => $job) {
            try {
                $stored = Storage::disk('spaces')->put($job['key'], fopen($job['path'], 'r'), 'public');
                if ($stored !== false) {
                    $succeeded[$i] = true;
                }
            } catch (\Throwable $e) {
                Log::error('Image upload failed', [
                    'key'      => $job['key'],
                    'original' => $job['originalName'],
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        return $succeeded;
    }

    /**
     * Rewrite a raw Spaces endpoint URL to the CDN host. Falls back to the
     * production defaults when the env is not configured, and returns the URL
     * untouched when it does not start with the Spaces endpoint.
     */
    private function toCdnUrl(string $url): string
    {
        $endpoint = rtrim((string) (config('filesystems.disks.spaces.endpoint') ?: 'https://fra1.digitaloceanspaces.com'), '/');
        $cdn      = rtrim((string) (config('filesystems.disks.spaces.cdn_url') ?: 'https://cdn.gegold.ge'), '/');

        if ($cdn !== '' && $endpoint !== '' && str_starts_with($url, $endpoint)) {
            return $cdn . substr($url, strlen($endpoint));
        }

        return $url;
    }

    /**
     * Raise memory_limit if decoding an image of the given dimensions would
     * likely exceed the current limit. GD decodes to a 4-bytes-per-pixel
     * truecolor bitmap plus working copies.
     */
    private function ensureMemoryFor(int $width, int $height): void
    {
        if ($width <= 0 || $height <= 0) {
            return;
        }

        $needed  = (int) ($width * $height * 4 * 2.2) + 48 * 1024 * 1024;
        $current = $this->memoryLimitBytes();

        if ($current > 0 && $current < $needed) {
            @ini_set('memory_limit', (string) $needed);
        }
    }

    private function memoryLimitBytes(): int
    {
        $limit = trim((string) ini_get('memory_limit'));

        if ($limit === '' || $limit === '-1') {
            return -1;
        }

        $unit  = strtolower(substr($limit, -1));
        $value = (int) $limit;

        return match ($unit) {
            'g'     => $value * 1024 * 1024 * 1024,
            'm'     => $value * 1024 * 1024,
            'k'     => $value * 1024,
            default => $value,
        };
    }

    public function uploadProductImages($images, $user, $image_for)
    {
        return $this->uploadMany($images, $user->id . '/' . $image_for . '/product-images');
    }

    public function uploadCompanyCoverImages($images, $user, $image_for)
    {
        return $this->uploadMany($images, $user->id . '/cover-images', onlyFirst: true);
    }

    public function uploadPortolioImages($images, $user, $image_for)
    {
        return $this->uploadMany($images, $user->id . $image_for . '/portofio_images');
    }

    public function uploadProfileOrCompanyImage($images, $user, $image_for)
    {
        return $this->uploadMany($images, $user->id . '/' . $image_for . '-images');
    }

    public function uploadBlogImages($images, $user)
    {
        return $this->uploadMany($images, $user->id . '/blogs');
    }

    public function uploadFeedbackImages($images, $user, $image_for)
    {
        return $this->uploadMany($images, $user->id . '/' . $image_for . '/feedback-images');
    }

    public function uploadAnnouncementImages($images, $user)
    {
        return $this->uploadMany($images, $user->id . '/announcements', onlyFirst: true);
    }
}
