<?php

namespace App\Services;

use App\Repositories\OtpCodeRepository;

class OtpCodeService
{

    public function __construct(private readonly OtpCodeRepository $otpCodeRepository)
    {
    }

    public function create($identifier,$code,$type,$phone=null)
    {
        return $this->otpCodeRepository->create($identifier,$code,$type, $phone);
    }

    public function makeUsed($identifier, $code,$type,$is_used)
    {
        return $this->otpCodeRepository->makeUsed($identifier, $code,$type,$is_used);
    }

}
