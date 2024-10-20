<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{

    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function checkIfUserExists($username)
    {
        return $this->userRepository->checkIfUserExists($username);
    }

    public function create($username, $password)
    {
        return $this->userRepository->create($username, $password);
    }

}
