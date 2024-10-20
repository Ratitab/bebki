<?php

namespace App\Services;

use App\Repositories\UserInformationRepository;
class UserInformationService
{

    public function __construct(private readonly UserInformationRepository $userInformationRepository)
    {
    }

    public function checkIfUserExists($username)
    {
        return $this->userInformationRepository->checkIfUserExists($username);
    }

    public function create($user_id,$user_information)
    {
        return $this->userInformationRepository->createUserInformation($user_id,$user_information);
    }

    public function findUserId($username)
    {
        return $this->userInformationRepository->findUserId($username);
    }


}
