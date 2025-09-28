<?php

namespace App\Repositories;

use App\Models\Countries\Country;
use App\Models\PlatformFeatures\PlatformFeature;
use Illuminate\Support\Str;

class PlatformFeatureRepository
{

    public function __construct(
        private readonly PlatformFeature $platformFeatureModel,
    ) {
    }

    public function findByUserId($userId)
    {
        return $this->platformFeatureModel->where('user_id', $userId)->get();
    }

}
