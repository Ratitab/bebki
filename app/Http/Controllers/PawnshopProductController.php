<?php

namespace App\Http\Controllers;

use App\Rules\ValidCompany;
use App\Rules\ValidCompanyBelongsUser;
use App\Services\PawnshopProductService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PawnshopProductController extends Controller
{
    use Resp;

    public function __construct(private readonly PawnshopProductService $pawnshopProductService)
    {
    }

    public function find_many_by_pawnshop_id(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make(
            [
                'company_id' => $request->company_id,
            ],
            [
                'company_id' => ['required', new ValidCompanyBelongsUser($user->id)],
            ]
        );


        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $products = $this->pawnshopProductService->findMany(
            company_id: $request->company_id,
            search: $request->search
        );

        return $this->apiResponseSuccess($products);
    }

    public function store(Request $request)
    {

        $validator = Validator::make(
            [
                'company_id' => $request->company_id,
                'title' => $request->title,
            ],
            [
                'company_id' => ['required', new ValidCompany()],
                'title' => ['required'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $created = $this->pawnshopProductService->create(
            $request->company_id,
            $request->title,
            $request->material,
            $request->stamp,
            $request->weight,
            $request->gem,
            $request->size,
            $request->phone_number,
            $request->description,
            $request->image_urls
        );

        return $this->apiResponseSuccess(['data' => $created]);
    }

    public function change_status(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make(
            [
                'company_id' => $request->company_id,
                'status' => $request->status,
            ],
            [
                'company_id' => ['required', new ValidCompanyBelongsUser($user->id)],
                'status' => ['required', 'in:pending,rejected,accepted'],
            ]
        );


        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $products = $this->pawnshopProductService->changeStatus(
            $request->company_id,
            $request->status
        );

        return $this->apiResponseSuccess($products);
    }

}
