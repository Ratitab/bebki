<?php

namespace App\Repositories;

use App\Models\Companies\CompanyInformationType;
use Illuminate\Support\Str;

class CompanyInformationTypeRepository
{

    public function __construct(
        private readonly CompanyInformationType $companyInformationTypeModel,
    )
    {
    }

    public function getAllInformationTypes()
    {
        return $this->companyInformationTypeModel->all()->pluck('id', 'name')->toArray();
    }


}
