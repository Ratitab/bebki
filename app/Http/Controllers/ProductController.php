<?php

namespace App\Http\Controllers;

use App\Rules\ValidUniqueCompanyIdentification;
use App\Rules\ValidUserCompany;
use App\Services\CompanyService;
use App\Services\ProductService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use Resp;

    public function __construct(private readonly ProductService $productService)
    {
    }


    public function index(Request $request)
    {
        return $this->apiResponseSuccess($this->productService->findMany());
    }
    public function store(Request $request)
    {

        $user = auth()->user();

        $validator = Validator::make(
            [
                'created_by' => $request->created_by,
                'created_by.id' => $request->input('created_by.id'),
                'created_by.type' => $request->input('created_by.type'),
                'title' => $request->title,
            ],
            [
                'created_by' => ['required'],
                'created_by.id' => ['required', new ValidUserCompany($user->id)],
                'created_by.type' => ['required'],
                'title' => ['required'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $company = $this->productService->create($request->created_by, $user, $request->title, $request->category, $request->material, $request->stamp, $request->weight, $request->gem, $request->size, $request->description, $request->customization, $request->city, $request->price, $request->tags);
        if ($company) {
            return $this->apiResponseSuccess(['data' => $company]);
        }
        return $this->apiResponseFail('Company Already Exists');
    }

}
