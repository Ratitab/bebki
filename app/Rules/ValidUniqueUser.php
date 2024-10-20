<?php

namespace App\Rules;

use App\Models\Users\User;
use App\Models\Users\UserInformation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class ValidUniqueUser implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::where('username', $value)->exists();
        $userInformation = UserInformation::whereIn('user_information_type_id', [5, 6])
            ->where('value', $value)
            ->exists();
        if($user || $userInformation){
            $fail('The username has already been taken.');
        }
    }
}
