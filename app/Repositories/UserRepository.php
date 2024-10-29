<?php

namespace App\Repositories;

use App\Models\Users\User;
use App\Models\Users\UserInformation;
use Illuminate\Support\Str;

class UserRepository
{

    public function __construct(
        private readonly User $userModel,
        private readonly UserInformation $userInformationModel,
    ) {
    }

    public function checkIfUserExists($username)
    {
        return $this->userModel
            ->where('username', $username)
            ->exists();
    }

    public function findManyById($userIds)
    {
        // Step 1: Fetch users based on IDs
        $users = $this->userModel->whereIn('id', $userIds)->get();

        // Step 2: Fetch all related user information in a single query
        $allUserInformation = $this->userInformationModel->leftJoin('user_information_types', 'user_information.user_information_type_id', '=', 'user_information_types.id')
            ->whereIn('user_information.user_id', $userIds)
            ->whereNull('user_information.deleted_at')
            ->select('user_information.user_id', 'user_information.value', 'user_information_types.name')
            ->get();

        // Step 3: Group user information by `user_id`
        $userInformationGrouped = $allUserInformation->groupBy('user_id');

        // Step 4: Append manually built information to each user
        $users->map(function ($user) use ($userInformationGrouped) {
            $informationCollection = [];
            if (isset($userInformationGrouped[$user->id])) {
                foreach ($userInformationGrouped[$user->id] as $info) {
                    $informationCollection[$info->name] = $info->value;
                }
            }
            // Attach information directly to the user without using an accessor
            $user->attributes['information'] = $informationCollection;
            return $user;
        });

        return $users;
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
