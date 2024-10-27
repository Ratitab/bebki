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

    public function updateUserInformation($user_id,$userInformation)
    {
        return $this->userInformationRepository->updateUserInformation($user_id, $userInformation);
    }

    public function findUserId($username)
    {
        return $this->userInformationRepository->findUserId($username);
    }

    public function findByUserIdAndType($userId,$typeId)
    {
        return $this->userInformationRepository->findByUserIdAndType($userId,$typeId);
    }


}
