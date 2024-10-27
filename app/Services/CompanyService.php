<?php

namespace App\Services;

use App\Repositories\AddressRepository;
use App\Repositories\CompanyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyService
{
    public function __construct(private readonly CompanyRepository $companyRepository,
                                private readonly AddressService $addressService,
                                private readonly CompanyInformationService $companyInformationService)
    {
    }

    public function create($identification_number, $company_type_id, $company_information, $company_address)
    {
        \DB::transaction(function ()use($identification_number, $company_type_id, $company_information, $company_address) {
        $company = $this->companyRepository->create($identification_number, $company_type_id);
        $this->companyInformationService->create($company->id, $company_information);
        $this->addressService->createOrUpdate($company->id, $company_address['address'],$company_address['city'],$company_address['state'],$company_address['lat'],
            $company_address['long'],$company_address['email'],
            $company_address['phone'],
            $company_address['postal_code'],$company_address['is_same_time'],$company_address['start_time'],$company_address['end_time'],null);
            return $company;
        });
       return 0;
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
