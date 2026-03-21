<?php

namespace App\Services;

use App\Repositories\AddressRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CompanyUserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyService
{
    public function __construct(private readonly CompanyRepository         $companyRepository,
                                private readonly CompanyUserRepository     $companyUserRepository,
                                private readonly AddressService            $addressService,
                                private readonly CompanyInformationService $companyInformationService,
                                private readonly LimitService $limitService )
    {
    }

    public function findAll($companyTypeId)
    {
        return $this->companyRepository->findAll($companyTypeId);
    }
    public function findOne($companyId)
    {
        return $this->companyRepository->findOneById($companyId);
    }
    public function findManyByUser($user)
    {
        return $this->companyUserRepository->findManyByUser($user->id);
    }

    public function findManyByUserWithLimits($user)
    {
        $companies = $this->companyUserRepository->findManyByUser($user->id);
        if (empty($companies)) {
            return [];
        }
        $companyIds = array_column($companies, 'company_id');
        // Initialize result array
        $result = [];
        //combine here, it must fetch all company, then companies limits and combine
        $limitsData = $this->limitService->multipleCompanyLimits($companyIds);
        // Create a mapped result array to ensure correct matching
        $result = [];
        foreach ($companies as $company) {
            $companyId = $company['company_id'];

            // Create a new company array with all original data
            $mappedCompany = $company;

            // Add limits data by explicit company ID matching
            $mappedCompany['limits'] = [
                'free_limits' => $limitsData['free_limits'][$companyId] ?? 3,
                'package_limits' => $limitsData['package_limits'][$companyId] ?? null,
                'package' => $limitsData['packages'][$companyId] ?? null
            ];

            $result[] = $mappedCompany;
        }

        return $result;
    }

    public function findOneByUser($user,$company_id)
    {
        return $this->companyUserRepository->findOneByUser($user->id,$company_id);
    }

    public function create($user, $identification_number, $company_type_id, $company_information, $company_address)
    {
       
        return \DB::transaction(function () use ($user, $identification_number, $company_type_id, $company_information, $company_address) {
            $company = $this->companyRepository->create($identification_number, $company_type_id);
            $this->companyInformationService->create($company->id, $company_information);
            foreach ($company_address as $companyAddress) {
                $this->addressService->createOrUpdate($company->id, $companyAddress['address'], $companyAddress['city'], $companyAddress['state'], $companyAddress['lat'],
                    $companyAddress['long'], $companyAddress['email'],
                    $companyAddress['phone'],
                    $companyAddress['postal_code'], $companyAddress['is_same_time'], $companyAddress['start_time'], $companyAddress['end_time'], null);

            }
            $this->companyUserRepository->create($company->id, $user->id);
            return $company;
        });
    }

    public function update($company_id, $companyInformation)
    {
        return $this->companyInformationService->update($company_id, $companyInformation);
    }

    public function delete($company_id)
    {
        $this->companyUserRepository->deleteByCompanyId($company_id);
        return $this->companyRepository->delete($company_id);
    }

}
