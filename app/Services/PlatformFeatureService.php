<?php

namespace App\Services;

use App\Repositories\AddressRepository;
use App\Repositories\CityRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CompanyUserRepository;
use App\Repositories\CountryRepository;
use App\Repositories\PlatformFeatureRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlatformFeatureService
{
    public function __construct(private readonly PlatformFeatureRepository $platformFeatureRepository)
    {
    }


    public function findByUserId($userId)
    {
        return $this->platformFeatureRepository->findByUserId($userId);
    }

}
