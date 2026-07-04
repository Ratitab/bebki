<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class UploadService
{
    public function __construct()
    {
    }

    /**
     * Compress an uploaded image to ≤500KB using GD.
     * Resizes to max 1500px wide, then iteratively reduces JPEG quality.
     * Returns path to temp file (caller must unlink) or original path if already small.
     */
    private function compressImage(\Illuminate\Http\UploadedFile $file): string
    {
        $maxBytes = 500 * 1024;

        if ($file->getSize() <= $maxBytes) {
            return $file->getRealPath();
        }

        $mime = $file->getMimeType();
        $path = $file->getRealPath();

        $source = match (true) {
            in_array($mime, ['image/jpeg', 'image/jpg']) => imagecreatefromjpeg($path),
            $mime === 'image/png'  => imagecreatefrompng($path),
            $mime === 'image/webp' => imagecreatefromwebp($path),
            $mime === 'image/gif'  => imagecreatefromgif($path),
            default                => null,
        };

        if (!$source) {
            return $path;
        }

        $origW = imagesx($source);
        $origH = imagesy($source);
        $maxWidth = 1500;

        if ($origW > $maxWidth) {
            $ratio   = $maxWidth / $origW;
            $newW    = $maxWidth;
            $newH    = (int) ($origH * $ratio);
            $canvas  = imagecreatetruecolor($newW, $newH);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($source);
            $source = $canvas;
            $curW   = $newW;
            $curH   = $newH;
        } else {
            // Flatten transparency for non-JPEG sources
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

        $tempPath = tempnam(sys_get_temp_dir(), 'bebki_img_') . '.jpg';

        // Step down quality until ≤500KB
        for ($quality = 85; $quality >= 30; $quality -= 10) {
            imagejpeg($source, $tempPath, $quality);
            if (filesize($tempPath) <= $maxBytes) {
                imagedestroy($source);
                return $tempPath;
            }
        }

        // Still too big — halve the dimensions and retry
        $newW   = (int) ($curW / 2);
        $newH   = (int) ($curH / 2);
        $canvas = imagecreatetruecolor($newW, $newH);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newW, $newH, $curW, $curH);
        imagedestroy($source);
        $source = $canvas;

        for ($quality = 85; $quality >= 30; $quality -= 10) {
            imagejpeg($source, $tempPath, $quality);
            if (filesize($tempPath) <= $maxBytes) {
                break;
            }
        }

        imagedestroy($source);
        return $tempPath;
    }

    private function uploadOne(string $storagePath, \Illuminate\Http\UploadedFile $file): string
    {
        $compressed = $this->compressImage($file);
        $isTemp     = $compressed !== $file->getRealPath();

        $imagePath = Storage::disk('spaces')->putFile(
            $storagePath,
            new \Illuminate\Http\File($compressed),
            'public'
        );

        if ($isTemp) {
            unlink($compressed);
        }

        $url = Storage::disk('spaces')->url($imagePath);
        return str_replace('https://fra1.digitaloceanspaces.com', 'https://cdn.gegold.ge', $url);
    }

    public function uploadProductImages($images, $user, $image_for)
    {
        $imageUrls = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                try {
                    $imageUrls[] = $this->uploadOne($user->id . '/' . $image_for . '/product-images', $image);
                } catch (\Exception $e) {
                    \Log::error('Image upload failed: ' . $e->getMessage());
                }
            }
        }
        return $imageUrls;
    }

    public function uploadCompanyCoverImages($images, $user, $image_for)
    {
        $imageUrls = [];
        if (is_array($images) && isset($images[0])) {
            try {
                $imageUrls[] = $this->uploadOne($user->id . '/cover-images', $images[0]);
            } catch (\Exception $e) {
                \Log::error('Image upload failed: ' . $e->getMessage());
            }
        }
        return $imageUrls;
    }

    public function uploadPortolioImages($images, $user, $image_for)
    {
        $imageUrls = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                try {
                    $imageUrls[] = $this->uploadOne($user->id . $image_for . '/portofio_images', $image);
                } catch (\Exception $e) {
                    \Log::error('Image upload failed: ' . $e->getMessage());
                }
            }
        }
        return $imageUrls;
    }

    public function uploadProfileOrCompanyImage($images, $user, $image_for)
    {
        $imageUrls = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                try {
                    $imageUrls[] = $this->uploadOne($user->id . '/' . $image_for . '-images', $image);
                } catch (\Exception $e) {
                    \Log::error('Image upload failed: ' . $e->getMessage());
                }
            }
        }
        return $imageUrls;
    }

    public function uploadBlogImages($images, $user)
    {
        $imageUrls = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                try {
                    $imageUrls[] = $this->uploadOne($user->id . '/blogs', $image);
                } catch (\Exception $e) {
                    \Log::error('Image upload failed: ' . $e->getMessage());
                }
            }
        }
        return $imageUrls;
    }

    public function uploadFeedbackImages($images, $user, $image_for)
    {
        $imageUrls = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                try {
                    $imageUrls[] = $this->uploadOne($user->id . '/' . $image_for . '/feedback-images', $image);
                } catch (\Exception $e) {
                    \Log::error('Feedback image upload failed: ' . $e->getMessage());
                }
            }
        }
        return $imageUrls;
    }

    public function uploadAnnouncementImages($images, $user)
    {
        $imageUrls = [];
        if (is_array($images) && isset($images[0])) {
            try {
                $imageUrls[] = $this->uploadOne($user->id . '/announcements', $images[0]);
            } catch (\Exception $e) {
                \Log::error('Image upload failed: ' . $e->getMessage());
            }
        }
        return $imageUrls;
    }
}
