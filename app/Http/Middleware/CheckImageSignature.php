<?php

namespace App\Http\Middleware;

use App\Services\ImageSignatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards /api/files/{path}. Files in a public folder (services.ceph.public_folders)
 * pass without a signature; everything else needs ?signature=<uuid> issued by
 * /auth/check whose allow/deny permissions cover the requested file.
 */
class CheckImageSignature
{
    public function __construct(private readonly ImageSignatureService $imageSignatureService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $path = (string) $request->route('path');
        $file = basename($path);
        $folder = $this->parentFolder($path);

        if ($folder !== null && in_array($folder, config('services.ceph.public_folders', []), true)) {
            return $next($request);
        }

        $imageSignature = $this->imageSignatureService->findValid($request->query('signature'));

        if (!$imageSignature) {
            return response()->json([
                'status'  => 403,
                'message' => 'Invalid or expired signature'
            ], 403);
        }

        if (!$this->checkPermissions($imageSignature, $folder, $file)) {
            return response()->json([
                'status'  => 403,
                'message' => 'Access denied for this file type or path'
            ], 403);
        }

        return $next($request);
    }

    private function parentFolder(string $path): ?string
    {
        $segments = explode('/', trim($path, '/'));

        return count($segments) >= 2 ? $segments[count($segments) - 2] : null;
    }

    private function checkPermissions($signature, ?string $folder, string $file): bool
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $fileType  = $this->getFileType($extension);

        $allow = $signature->allow_permission ?? [];
        $deny  = $signature->deny_permission ?? [];

        $fileKey   = "file:$file";
        $folderKey = $folder ? "folder:$folder" : null;
        $typeKey   = "type:$fileType";

        // --- FILE rules (checked first) ---
        if (in_array($fileKey, $deny, true) && in_array($fileKey, $allow, true)) {
            return false; // both present → deny
        }
        if (in_array($fileKey, $allow, true)) {
            return true; // explicit file allow wins
        }
        if (in_array($fileKey, $deny, true)) {
            return false;
        }

        // --- FOLDER rules ---
        if ($folderKey && in_array($folderKey, $deny, true)) {
            return false; // folder deny > global allow
        }
        if ($folderKey && in_array($folderKey, $allow, true)) {
            return true;
        }

        // --- TYPE rules ---
        if (in_array($typeKey, $deny, true)) {
            return false; // type deny > global allow
        }
        if (in_array($typeKey, $allow, true)) {
            return true;
        }

        // --- GLOBAL rules ---
        if (in_array('all', $deny, true)) {
            return false;
        }
        if (in_array('all', $allow, true)) {
            return true;
        }

        // Default deny
        return false;
    }

    private function getFileType(string $extension): string
    {
        $imageTypes    = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
        $documentTypes = ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt'];
        $videoTypes    = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];
        $excelTypes    = ['xls', 'xlsx', 'csv', 'ods'];

        if (in_array($extension, $imageTypes, true)) {
            return 'image';
        }
        if (in_array($extension, $documentTypes, true)) {
            return 'document';
        }
        if (in_array($extension, $videoTypes, true)) {
            return 'video';
        }
        if (in_array($extension, $excelTypes, true)) {
            return 'excel';
        }

        return 'other';
    }
}
