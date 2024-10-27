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
                                private readonly CompanyInformationService $companyInformationService)
    {
    }

    public function findByUser($user)
    {
        return $this->companyUserRepository->findByUser($user->id);
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
        return $this->companyRepository->delete($company_id);
    }

}
