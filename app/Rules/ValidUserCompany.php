<?php

namespace App\Rules;

use App\Models\Companies\CompanyUser;
use App\Models\Users\OtpCode;
use App\Models\Users\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class ValidUserCompany implements ValidationRule
{
    private $identifier;

    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userCompany = CompanyUser::where('company_id', $value)
            ->where('user_id', $this->identifier)
            ->exists();
        $userPostingAsSelf = $this->identifier == $value;

        if (!$userCompany || !$userPostingAsSelf) {
            $fail('Permission Denied');
        }
    }
}
