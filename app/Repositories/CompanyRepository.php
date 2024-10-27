<?php

namespace App\Repositories;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyUser;
use App\Models\Users\User;
use Illuminate\Support\Str;

class CompanyRepository
{

    public function __construct(
        private readonly Company $companyModel
    ) {
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
