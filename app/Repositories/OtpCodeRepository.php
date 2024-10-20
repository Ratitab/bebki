<?php

namespace App\Repositories;

use App\Models\Users\OtpCode;
use App\Models\Users\User;
use Illuminate\Support\Str;

class OtpCodeRepository
{

    public function __construct(
        private readonly OtpCode $otpCodeModel,
    ) {
    }

    public function create($identifier, $code,$type)
    {
        $otp = new $this->otpCodeModel;
        $otp->id = Str::uuid();
        $otp->identifier = $identifier;
        $otp->code = $code;
        $otp->is_used = 0;
        $otp->type = $type;
        $otp->save();
        return $otp;
    }

    public function makeUsed($identifier, $code,$type,$is_used)
    {
        $otp = $this->otpCodeModel->where('identifier', $identifier)->where('is_used', 0)->where('code',$code)->where('type',$type)->first();
        $otp->is_used = $is_used;
        $otp->save();
        return $otp;
    }

}
