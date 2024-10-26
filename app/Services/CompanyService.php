<?php

namespace App\Services;

use App\Repositories\CompanyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyService
{
    public function __construct(private readonly CompanyRepository $companyRepository, private readonly CompanyInformationService $companyInformationService)
    {
    }

    public function create($identification_number, $company_type_id, $company_information)
    {
        $company = $this->companyRepository->create($identification_number, $company_type_id);
        $this->companyInformationService->create($company->id, $company_information);
        return $company;
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
