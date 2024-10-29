<?php

namespace App\Repositories;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyInformation;
use App\Models\Companies\CompanyUser;
use App\Models\Users\User;
use Illuminate\Support\Str;

class CompanyRepository
{

    public function __construct(
        private readonly Company $companyModel,
        private readonly CompanyInformation $companyInformationModel,
    ) {
    }

    public function findManyById($companyIds)
    {
        $companies = $this->companyModel->whereIn('id', $companyIds)->get();

        // Step 2: Fetch all related company information in a single query
        $allCompanyInformation = $this->companyInformationModel->leftJoin('company_information_types', 'company_information.company_information_type_id', '=', 'company_information_types.id')
            ->whereIn('company_information.company_id', $companyIds)
            ->whereNull('company_information.deleted_at')
            ->select('company_information.company_id', 'company_information.value', 'company_information_types.name')
            ->get();
        $companyInformationGrouped = $allCompanyInformation->groupBy('company_id');
        $companies->map(function ($company) use ($companyInformationGrouped) {
            $informationCollection = [];
            if (isset($companyInformationGrouped[$company->id])) {
                foreach ($companyInformationGrouped[$company->id] as $info) {
                    $informationCollection[$info->name] = $info->value;
                }
            }
            $company->information = $informationCollection;  // Dynamically set the information attribute
            return $company;
        });
        return $companies;
    }
    public function create($identification_number, $company_type_id)
    {
        $company = new $this->companyModel;
        $company->id = Str::uuid();
        $company->identification_number = $identification_number;
        $company->company_type_id = $company_type_id;
        $company->is_active = 1;
        $company->save();
        return $company;
    }

    public function delete($id)
    {
        return $this->companyModel->where('id', $id)->delete();
    }

}
