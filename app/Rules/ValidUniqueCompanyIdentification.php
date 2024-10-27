<?php

namespace App\Rules;

use App\Models\Companies\Company;
use App\Models\Users\UserInformation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class ValidUniqueCompanyIdentification implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Company::where('identification_number', $value)->exists();
        if($user){
            $fail('Company has already registered');
        }
    }
}
