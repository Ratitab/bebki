<?php

namespace App\Services;

use App\Repositories\AddressRepository;
use App\Repositories\CityRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CompanyUserRepository;
use App\Repositories\CountryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CountryService
{
    public function __construct(private readonly CountryRepository $countryRepository,
    private readonly CityRepository $cityRepository,)
    {
    }

    public function index()
    {
        return $this->countryRepository->get();
    }

    public function findByCountryId($countryId = 1)
    {
        return $this->cityRepository->findByCountryId($countryId);
    }


    public function delete($country_id)
    {
        return $this->countryRepository->delete($country_id);
    }

}
