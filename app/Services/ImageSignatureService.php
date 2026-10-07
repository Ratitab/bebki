<?php

namespace App\Services;

use App\Repositories\ImageSignatureRepository;

class ImageSignatureService
{
    public function __construct(private readonly ImageSignatureRepository $imageSignatureRepository)
    {
    }

    public function checkExpired($userId)
    {
        return $this->imageSignatureRepository->checkExpired($userId);
    }

    public function findValid($signature)
    {
        return $this->imageSignatureRepository->findValid($signature);
    }

}
