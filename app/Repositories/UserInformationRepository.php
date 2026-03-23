<?php

namespace App\Repositories;

use App\Models\Users\ExclusiveUser;
use App\Models\Users\User;
use App\Models\Users\UserInformation;
use Illuminate\Support\Facades\DB;

class UserInformationRepository
{

    public function __construct(
        private readonly UserInformation $userInformationModel,
        private readonly ExclusiveUser $exclusiveUserModel,
        private readonly User $userModel,
        private readonly UserInformationTypeRepository $userInformationTypeRepository,
    )
    {
    }

    public function addExclusiveUser($username)
    {
        $exclusiveUser = $this->exclusiveUserModel
            ->where('email', strtolower(trim($username)))
            ->exists();

        if($exclusiveUser){
            return false;
        }
        $exclusiveUser = new $this->exclusiveUserModel;
        $exclusiveUser->email = strtolower(trim($username));
        $exclusiveUser->pearls = 5;
        $exclusiveUser->save();
        return true;
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
        \DB::transaction(function () use ($userId, $bulkUpdateData) {
            $this->userInformationModel
                ->where('user_id', $userId)
                ->whereIn('user_information_type_id', array_column($bulkUpdateData, 'user_information_type_id'))
                ->delete();

            $this->userInformationModel->insert($bulkUpdateData);
        });

        return true;
    }


    public function findUserId($username)
    {
        // First try to find the user by username in the users table
        $user = $this->userModel->where('username', $username)
            ->select('id as user_id', 'password')
            ->first();

        if ($user) {
            return $user;
        }

        // If not found in users table, try to find in user_information table
        return $this->userInformationModel
            ->whereIn('user_information_type_id', [5, 6])
            ->where('value', $username)
            ->join('users', 'user_information.user_id', '=', 'users.id')
            ->select('user_information.user_id', 'users.password')
            ->first();
    }

    public function updateShopStatus(string $userId, string $status): void
    {
        $informationTypes = $this->userInformationTypeRepository->getAllInformationTypes();
        $typeId = $informationTypes['shop_status'] ?? null;

        if (!$typeId) {
            return;
        }

        $this->userInformationModel
            ->where('user_id', $userId)
            ->where('user_information_type_id', $typeId)
            ->update(['value' => $status]);
    }

    public function findByUserIdAndType($userId,$typeId)
    {
        return $this->userInformationModel->where('user_id', $userId)
            ->where('user_information_type_id', $typeId)
            ->first();
    }

}
