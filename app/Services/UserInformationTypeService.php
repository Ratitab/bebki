<?php

namespace App\Services;

use App\Models\Users\UserInformationType;
use App\Services\UploadNodeService;
use App\Services\UploadService;
use Illuminate\Http\Request;

class UserInformationTypeService
{
    private $model;

    public function __construct(UserInformationType $model)
    {
        $this->model = $model;
    }

    public function all($order_by = 'ASC', $data_per_page = 20,)
    {
        return $this->model->orderBy('id', $order_by)->paginate($data_per_page);
    }

    public function by_key($key)
    {
        return $this->model->where('name', $key)->first();
    }

    public function single($userInformationType)
    {
        return $userInformationType;
    }

    public function create($name)
    {
        $userInformationType = new $this->model;
        $userInformationType->name = $name;
        $userInformationType->save();
        return $userInformationType;
    }

    public function update($userInformationType, $name)
    {
        $userInformationType->name = $name;
        $userInformationType->save();
        return $userInformationType;
    }

    public function delete($userInformationType)
    {
        return $userInformationType->delete();
    }

}
