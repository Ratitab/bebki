<?php

namespace App\Repositories;

use App\Models\Users\User;
use Illuminate\Support\Str;

class UserRepository
{

    public function __construct(
        private readonly User $userModel,
    ) {
    }

    public function checkIfUserExists($username)
    {
        return $this->userModel
            ->where('username', $username)
            ->exists();
    }

    public function findManyById($userId)
    {
        return $this->userModel->whereIn('id',$userId)->get();
    }
    public function create($username, $password)
    {
        $user = new $this->userModel;
        $user->id = Str::uuid();
        $user->username = $username;
        $user->password = \Hash::make($password);
        $user->is_active = 1;
        $user->save();
        return $user;
    }


    public function changePassword($username, $password)
    {
        $user = $this->userModel->where('username', $username)->first();
        $user->password = \Hash::make($password);
        $user->save();
        return $user;
    }

}
