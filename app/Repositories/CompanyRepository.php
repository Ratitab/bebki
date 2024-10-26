<?php

namespace App\Repositories;

use App\Models\Companies\Company;
use App\Models\Users\User;
use Illuminate\Support\Str;

class CompanyRepository
{

    public function __construct(
        private readonly Company $companyModel,
    ) {
    }
    public function create($identification_number, $company_type_id)
    {
        $user = new $this->companyModel;
        $user->id = Str::uuid();
        $user->identification_number = $identification_number;
        $user->company_type_id = $company_type_id;
        $user->is_active = 1;
        $user->save();
        return $user;
    }

    public function delete($id)
    {
        return $this->companyModel->where('id', $id)->delete();
    }

}
