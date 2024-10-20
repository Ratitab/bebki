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

    public function create($username, $password)
    {
        $user = new $this->userModel;
        $user->id = Str::uuid();
        $user->username = $username;
        $user->password = \Hash::make($password);
        $user->is_active = 0;
        $user->save();
        return $user;
    }

}
