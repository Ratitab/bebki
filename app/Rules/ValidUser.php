<?php

namespace App\Rules;

use App\Models\Users\User;
use App\Models\Users\UserInformation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class ValidUser implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::where('username', $value)->exists();
        if(!$user){
            $fail('account not found');
        }
    }
}
