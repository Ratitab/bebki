<?php

namespace App\Services;

use App\Repositories\CompanyInformationRepository;
use App\Repositories\CompanyRepository;

class CompanyInformationService
{

    public function __construct(private readonly CompanyInformationRepository $companyInformationRepository)
    {
    }

    public function findOneByCompanyAndTypeId($company_id,$company_information)
    {
        return $this->companyInformationRepository->findOneByCompanyAndTypeId($company_id,$company_information);
    }
    public function create($company_id,$company_information)
    {
        return $this->companyInformationRepository->createCompanyInformation($company_id,$company_information);
    }

    public function update($company_id, $companyInformation)
    {
        return $this->companyInformationRepository->updateCompanyInformation($company_id, $companyInformation);
    }
}
