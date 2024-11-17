<?php

namespace App\Http\Controllers;

use App\DTO\SearchProductsDTO;
use App\DTO\SingleProductDTO;
use App\Rules\ValidUniqueCompanyIdentification;
use App\Rules\ValidUserCompany;
use App\Services\CompanyService;
use App\Services\ProductService;
use App\Services\UploadService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use Resp;

    public function __construct(private readonly ProductService $productService, private readonly UploadService $uploadService)
    {
    }


    public function index(Request $request)
    {
        $searchDTO = SearchProductsDTO::fromRequest($request);
        return $this->apiResponseSuccess($this->productService->findMany($searchDTO));
    }

    public function show(Request $request, string $productId)
    {
        $validator = Validator::make(
            [
                'productId' => $productId,
            ],
            [
                'productId' => ['required'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $productDTO = SingleProductDTO::fromRequest($request,$productId);
        $singleProduct = $this->productService->findOne($productDTO);
        if(!is_null($singleProduct)){
            return $this->apiResponseSuccess($singleProduct);
        }
        return $this->apiResponseFail('product not found');
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
        return $this->apiResponseFail('Out Of Limits');
    }

    public function make_favourite(Request $request)
    {
        $validator = Validator::make(
            [
                'data_id' => $request->data_id,
            ],
            [
                'data_id' => ['required'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        return $this->apiResponseSuccess($this->productService->makeFavourite(auth()->user(),$request->data_id));
    }

    public function count_user_favourites(Request $request)
    {
        $userId = auth()->id();
        return $this->apiResponseSuccess(
            Cache::remember('user_favourites_count_' . $userId, now()->addMinutes(3), function () use ($userId) {
                return $this->productService->countUserFavourites($userId);
            })
        );
    }

    public function user_favourite_products(Request $request)
    {
        $userId = auth()->id();
        return $this->apiResponseSuccess( ['data'=>$this->productService->userFavouriteProducts($userId)]);
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
                'image_for' => ['required','in:individual,shop,pawnshop,store'],
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
