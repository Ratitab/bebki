<?php

namespace App\Rules;

use App\Models\Users\OtpCode;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class ValidUpdateEmailOrPhoneCode implements ValidationRule
{
    private $identifier;

    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validCode = OtpCode::where('code', $value)
            ->where('identifier', $this->identifier)
            ->where('type', 'update_email_or_phone')
            ->where('is_used', false)
            ->where('created_at', '>', Carbon::now()->subMinutes(5))
            ->exists();

        if (!$validCode) {
            $fail('The registration code is invalid or has expired.');
        }
    }
}
