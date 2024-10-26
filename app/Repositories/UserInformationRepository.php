<?php

namespace App\Repositories;

use App\Models\Users\UserInformation;
use Illuminate\Support\Facades\DB;

class UserInformationRepository
{

    public function __construct(
        private readonly UserInformation $userInformationModel,
        private readonly UserInformationTypeRepository $userInformationTypeRepository,
    )
    {
    }

    public function checkIfUserExists($username)
    {
        return $this->userInformationModel
            ->whereIn('user_information_type_id', [5, 6])
            ->where('value', $username)
            ->exists();
    }

    public function createUserInformation($userId, array $userInformation)
    {
            $informationTypes = $this->userInformationTypeRepository->getAllInformationTypes();
            $bulkInsertData = [];
            foreach ($informationTypes as $typeName => $typeId) {
                if (isset($userInformation[$typeName])) {
                    $bulkInsertData[] = [
                        'user_id' => $userId,
                        'user_information_type_id' => $typeId,
                        'value' => $userInformation[$typeName],
                        'verified_at' => null,
                    ];
                }
            }
            $this->userInformationModel->insert($bulkInsertData);
            return true;
    }

    public function updateUserInformation($userId, array $userInformation)
    {
        $informationTypes = $this->userInformationTypeRepository->getAllInformationTypes();
        $bulkUpdateData = [];

        foreach ($informationTypes as $typeName => $typeId) {
            if (isset($userInformation[$typeName])) {
                $bulkUpdateData[] = [
                    'user_id' => $userId,
                    'user_information_type_id' => $typeId,
                    'value' => $userInformation[$typeName],
                    'verified_at' => null,
                ];
            }
        }
        $this->userInformationModel->upsert(
            $bulkUpdateData,
            ['user_id', 'user_information_type_id'],
            ['value', 'verified_at']
        );

        return true;
    }



    public function findUserId($username)
    {
        return $this->userInformationModel
            ->whereIn('user_information_type_id', [5, 6])
            ->where(function($query) use ($username) {
                $query->where('value', $username)
                    ->orWhere('users.username', $username);
            })
            ->leftJoin('users', 'user_information.user_id', '=', 'users.id')
            ->select('user_information.user_id', 'users.password')
            ->first();
    }

    public function findByUserIdAndType($userId,$typeId)
    {
        return $this->userInformationModel->where('user_id', $userId)
            ->where('user_information_type_id', $typeId)
            ->first();
    }

}
