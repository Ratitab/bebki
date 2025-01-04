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
        $type = request()->input('created_by.type');
        if($type == 'company'){
            if (!$userCompany) {
                $fail('Permission Denied');
            }
        }
        if($type == 'individual'){
            if (!$userPostingAsSelf) {
                $fail('Permission Denied');
            }
        }
        if(!in_array($type, ['individual', 'store','pawnshop','stock_exchange'])) {
            $fail('Permission Denied');
        }
    }
}
