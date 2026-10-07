<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemException;
use Symfony\Component\Mime\MimeTypes;

/**
 * Streams objects from the LAN-only Ceph bucket to browsers.
 * Access control lives in the check.image.signature middleware.
 */
class FileAccessController extends Controller
{
    public function show(Request $request, string $path)
    {
        $path = trim($path, '/');

        if ($path === '' || in_array('..', explode('/', $path), true)) {
            return response()->json(['status' => 404, 'message' => 'File not found'], 404);
        }

        try {
            $stream = Storage::disk('ceph')->readStream($path);
        } catch (FilesystemException $e) {
            return response()->json(['status' => 404, 'message' => 'File not found'], 404);
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeType = MimeTypes::getDefault()->getMimeTypes($extension)[0] ?? 'application/octet-stream';
        $isPublic = in_array(basename(dirname($path)), config('services.ceph.public_folders', []), true);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'X-Content-Type-Options' => 'nosniff',
            // Never let a stored file (e.g. an SVG) run script on our origin.
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            // Object keys are random and never overwritten, so public files can
            // be cached for good. Signed files must not outlive the signature.
            'Cache-Control' => $isPublic ? 'public, max-age=31536000, immutable' : 'private, no-store',
        ]);
    }
}
