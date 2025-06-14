<?php

namespace App\Rules;

use App\Models\Companies\Company;
use App\Models\Companies\CompanyUser;
use App\Models\Users\OtpCode;
use App\Models\Users\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class ValidCompany implements ValidationRule
{

    public function __construct()
    {
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $company = Company::where('id', $value)
            ->exists();
        if (!$company) {
            $fail('Permission Denied');
        }
    }
}
