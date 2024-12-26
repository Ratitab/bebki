<?php

namespace App\Services;

use App\Repositories\AddressRepository;
use App\Repositories\CityRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CompanyUserRepository;
use App\Repositories\CountryRepository;
use App\Repositories\FreeLimitRepository;
use App\Repositories\LimitRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LimitService
{
    public function __construct(private readonly FreeLimitRepository $freeLimitRepository,
                                private readonly LimitRepository     $limitRepository)
    {
    }

    public function userLimits($user)
    {
        $result = [
            'free_limits' => null,
            'package_limits' => null,
            'package' => null,
        ];
        $freeLimits = $this->freeLimitRepository->findById($user->id);
        if(!$freeLimits){
            $result['free_limits'] = 3;
        }else{
            $result['free_limits'] = $freeLimits->freeLimit_count;
        }
        $packageLimits = $this->limitRepository->findById($user->id);
        if ($packageLimits) {
            $result['package_limits'] = $packageLimits->limit_count;
            $result['package'] = $packageLimits;
        }
        return $result;
    }

    public function companyLimits($companyId)
    {
        $result = [
            'free_limits' => null,
            'package_limits' => null,
            'package' => null,
        ];
        $freeLimits = $this->freeLimitRepository->findById($companyId);
        if(!$freeLimits){
            $result['free_limits'] = 3;
        }else{
            $result['free_limits'] = $freeLimits->freeLimit_count;
        }
        $packageLimits = $this->limitRepository->findById($companyId);
        if ($packageLimits) {
            $result['package_limits'] = $packageLimits->limit_count;
            $result['package'] = $packageLimits;
        }
        return $result;
    }
    public function checkIfContainsLimits($createdById)
    {
        return $this->limitRepository->findById($createdById);
    }
    public function buyLimits($createdBy, $user, $price, $package,$bought_limits, $limit_count, $limit_for)
    {
        $first = $this->checkIfContainsLimits($createdBy);
        if ($first) {
            $limit_count += $first->limit_count;
        }
        $expires_at = Carbon::now()->addDays(30)->toDateTime();
        return $this->limitRepository->create($createdBy, $user, $price, $package,$bought_limits, $limit_count, $limit_for, $expires_at);
    }

}
