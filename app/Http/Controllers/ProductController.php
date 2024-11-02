<?php

namespace App\Http\Controllers;

use App\Rules\ValidUniqueCompanyIdentification;
use App\Rules\ValidUserCompany;
use App\Services\CompanyService;
use App\Services\ProductService;
use App\Services\UploadService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use Resp;

    public function __construct(private readonly ProductService $productService, private readonly UploadService $uploadService)
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
        $company = $this->productService->create($request->created_by, $user, $request->title, $request->category, $request->material, $request->stamp, $request->weight, $request->gem, $request->size, $request->description, $request->customization, $request->city, $request->price, $request->tags,$request->image_urls);
        if ($company) {
            return $this->apiResponseSuccess(['data' => $company]);
        }
        return $this->apiResponseFail('Company Already Exists');
    }

    public function upload_images(Request $request)
    {
        $validator = Validator::make(
            [
                'images' => $request->file('images'),
                'image_for' => $request->image_for,
            ],
            [
                'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Add image validation rules for each image
                'image_for' => ['required','in:individual,company'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $images = $request->file('images');
        $user = auth()->user();
        return $this->apiResponseSuccess(['data' => $this->uploadService->uploadProductImages($images,$user,$request->image_for)]);
    }

}
