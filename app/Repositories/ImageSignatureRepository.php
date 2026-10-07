<?php

namespace App\Repositories;

use App\Models\Signatures\ImageSignature;
use Illuminate\Support\Str;

class ImageSignatureRepository
{

    public function __construct(
        private readonly ImageSignature $imageSignatureModel,
    ) {
    }

    /**
     * Permission entries: "all", "type:image|document|video|excel|other",
     * "folder:<name>" or "file:<name>". See CheckImageSignature for precedence.
     */
    public function create($userId, $expireDatetime, $allowPermission = ['all'], $denyPermission = [])
    {
        $signature = new $this->imageSignatureModel;
        $signature->signature = Str::uuid()->toString();
        $signature->user_id = $userId;
        $signature->expire_datetime = $expireDatetime;
        $signature->allow_permission = $allowPermission;
        $signature->deny_permission = $denyPermission;
        $signature->save();
        return $signature;
    }

    public function findValid($signature)
    {
        if (empty($signature) || !is_string($signature)) {
            return null;
        }

        return $this->imageSignatureModel
            ->where('signature', $signature)
            ->where('expire_datetime', '>', now())
            ->first();
    }

    /**
     * Latest unexpired signature for the user, or a fresh one.
     */
    public function checkExpired($userId)
    {
        $signature = $this->imageSignatureModel->where('user_id', $userId)->orderBy('created_at', 'desc')->first();

        if (!$signature || $signature->expire_datetime <= now()) {
            $signature = $this->create($userId, now()->addMinutes((int) config('services.ceph.signature_ttl', 60)));
        }
        return $signature;
    }

}
