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

    public function buy_limits(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make(
            [
                'package' => $request->package,
                'company_id' => $request->company_id,
                'type' => $request->type,
            ],
            [
                'package' => ['required','in:starter,basic,pro,premium'],
                'company_id' => $request->company_id ? ['nullable', new ValidCompanyBelongsUser($user->id)] : ['nullable'],
                'type' => ['required','in:individual,shop,pawnshop'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $checkIfContainsLimits = $this->limitService->checkIfContainsLimits($request->company_id ?? $user->id);
        if(!$checkIfContainsLimits->isEmpty() && $checkIfContainsLimits->limit_count>0){
            return $this->apiResponseFail('Already have limits');
        }
        $packages = [
            'starter' => [
                'package' => 'Starter',
                'price' => 475,        // 50 posts × 9.5 GEL
                'limit_count' => 50,
                'price_per_post' => 9.5,
                'savings' => 25,       // (10 - 9.5) × 50 = 25 GEL savings
            ],
            'basic' => [
                'package' => 'Basic',
                'price' => 900,        // 100 posts × 9 GEL
                'limit_count' => 100,
                'price_per_post' => 9,
                'savings' => 100,      // Less attractive savings
            ],
            'pro' => [
                'package' => 'Pro',
                'price' => 2000,       // 250 posts × 8 GEL
                'limit_count' => 250,
                'price_per_post' => 8,
                'savings' => 500,      // Medium savings
            ],
            'premium' => [
                'package' => 'Premium',
                'price' => 2800,       // 400 posts × 7 GEL (Best value)
                'limit_count' => 400,
                'price_per_post' => 7,
                'savings' => 1200,     // Most attractive savings
            ]
        ];

        //TODO: generate payment url
        $payload =[
            'createdBy'=>['id'=>$request->company_id ? $request->company_id : $user->id, 'type'=>$request->type],
            'user'=>['id'=>$user->id,'information'=>['first_name'=>$user->information['first_name'],'last_name'=>$user->information['last_name']]],
            'price'=>$packages[$request->package]['price'],
            'package'=>$packages[$request->package]['package'],
            'bought_limits'=>$packages[$request->package]['limit_count'],
            'limit_count'=>$packages[$request->package]['limit_count'],
            'limit_for'=>$request->type
        ];
        // Simulate activate_limits request with the same payload structure
        $activateRequest = new Request($payload);
        $this->activate_limits($activateRequest);
        return $this->apiResponseSuccess($packages);
    }

    public function activate_limits(Request $request)
    {
        $rules = [
            'createdBy' => ['required', 'array'],
            'createdBy.id' => ['required'],
            'createdBy.type' => ['required', 'in:individual,shop,pawnshop'],
            'user' => ['required', 'array'],
            'user.id' => ['required'],
            'price' => ['required', 'numeric'],
            'package' => ['required', 'string'],
            'limit_count' => ['required', 'numeric'],
            'limit_for' => ['required', 'in:individual,shop,pawnshop'],
        ];

        // Add ValidCompanyBelongsUser rule only for shop/pawnshop types
        if (in_array($request->input('createdBy.type'), ['shop', 'pawnshop'])) {
            $rules['createdBy.id'][] = new ValidCompanyBelongsUser($request->input('user.id'));
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        return $this->apiResponseSuccess(['data' => $this->limitService->buyLimits($request->input('createdBy'),
            (object) $request->input('user'),
            $request->input('price'),
            $request->input('package'),
            $request->input('bought_limits'),
            $request->input('limit_count'),
            $request->input('limit_for'))]);
    }
}
