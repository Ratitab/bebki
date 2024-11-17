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

    public function findAll($companyTypeId = null)
    {
        $query = $this->companyModel->newQuery()
            ->select(['id','company_type_id'])
            ->when($companyTypeId !== null, fn($q) => $q->where('company_type_id', $companyTypeId));

        $companies = $query->orderBy('id')->cursorPaginate(12);

        if ($companies->count()) {
            $companyIds = array_column($companies->items(), 'id');

            $companyInfo = CompanyInformation::select([
                'company_information.company_id',
                'company_information.value',
                'company_information_types.name'
            ])
                ->join('company_information_types', 'company_information.company_information_type_id', '=', 'company_information_types.id')
                ->whereIn('company_information.company_id', $companyIds)
                ->whereNull('company_information.deleted_at')
                ->whereNotIn('company_information_types.name', ['phone_numbers', 'email'])
                ->get();

            // Pre-build information arrays for all companies
            $infoArray = [];
            foreach($companyInfo as $info) {
                $infoArray[$info->company_id][$info->name] = $info->value;
            }

            // Convert to array with pre-built information
            $companiesArray = array_map(function($company) use ($infoArray) {
                $data = $company->toArray();
                $data['information'] = $infoArray[$company->id] ?? [];
                return $data;
            }, $companies->items());

            $companies->setCollection(collect($companiesArray));
        }

        return $companies;
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
            $company->setAttribute('information',$informationCollection);  // Dynamically set the information attribute
            return $company;
        });
        return $companies;
    }

    public function findOneById(string $companyId)
    {
        // Step 1: Fetch the company
        $company = $this->companyModel->find($companyId);

        if (!$company) {
            return null;
        }

        // Step 2: Fetch company information in a single query
        $companyInformation = $this->companyInformationModel
            ->leftJoin(
                'company_information_types',
                'company_information.company_information_type_id',
                '=',
                'company_information_types.id'
            )
            ->where('company_information.company_id', $companyId)
            ->whereNull('company_information.deleted_at')
            ->select(
                'company_information.value',
                'company_information_types.name'
            )
            ->get();

        // Step 3: Transform information into associative array
        $informationCollection = [];
        foreach ($companyInformation as $info) {
            $informationCollection[$info->name] = $info->value;
        }

        // Step 4: Set the information attribute
        $company->setAttribute('information', $informationCollection);

        return $company;
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
