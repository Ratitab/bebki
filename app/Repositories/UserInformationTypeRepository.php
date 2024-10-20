<?php

namespace App\Repositories;

use App\Models\Users\UserInformationType;
use Illuminate\Support\Str;

class UserInformationTypeRepository
{

    public function __construct(
        private readonly UserInformationType $userInformationTypeModel,
    )
    {
    }

    public function getAllInformationTypes()
    {
        return $this->userInformationTypeModel->all()->pluck('id', 'name')->toArray();
    }


}
