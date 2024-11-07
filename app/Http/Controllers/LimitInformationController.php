<?php

namespace App\Http\Controllers;

use App\Rules\ValidCompanyBelongsUser;
use App\Services\LimitService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LimitInformationController extends Controller
{
    use Resp;

    public function __construct(private readonly LimitService $limitService)
    {
    }

    public function user_limits(Request $request)
    {
        return $this->apiResponseSuccess(['data' => $this->limitService->userLimits(auth()->user())]);
    }

    public function company_limits(Request $request,$company_id)
    {
        $user = auth()->user();

        $validator = Validator::make(
            [
                'company_id' => $company_id,
            ],
            [
                'company_id' => ['required', new ValidCompanyBelongsUser($user->id)],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        return $this->apiResponseSuccess(['data' => $this->limitService->companyLimits($company_id)]);
    }
}
