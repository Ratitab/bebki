<?php

namespace App\Rules;

use App\Models\Users\OtpCode;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class ValidRegistrationCode implements ValidationRule
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
            ->where('type', 'registration')
            ->where('is_used', false)
            ->where('created_at', '>', Carbon::now('UTC')->subMinutes(5))
            ->exists();

        if (!$validCode) {
            $fail('The registration code is invalid or has expired.');
        }
    }
}
