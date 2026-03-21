<?php

namespace App\Http\Controllers;

use App\Rules\ValidUniqueCompanyIdentification;
use App\Services\CompanyService;
use App\Services\UploadService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    use Resp;

    public function __construct(private readonly CompanyService $companyService, private readonly UploadService $uploadService)
    {
    }

    public function findAll(Request $request)
    {
        $validator = Validator::make(
            [
                'type_id' => $request->type_id,
            ],
            [
                'type_id' => ['nullable','in:1,2,3'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        return $this->apiResponseSuccess(['data' => $this->companyService->findAll($request->type_id)]);
    }

    public function findSingle(Request $request,$company_id)
    {
        $validator = Validator::make(
            [
                'company_id' => $company_id,
            ],
            [
                'company_id' => ['required'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        return $this->apiResponseSuccess(['data' => $this->companyService->findOne($company_id)]);
    }

    public function index(Request $request)
    {
        return $this->apiResponseSuccess(['data' => $this->companyService->findManyByUser(auth()->user())]);
    }

    public function show(Request $request,$company_id)
    {
        return $this->apiResponseSuccess(['data' => $this->companyService->findOneByUser(auth()->user(),$company_id)]);
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
                'image_for' => ['required','in:individual,store,pawnshop,stock_exchange'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $images = $request->file('images');
        $user = auth()->user();
        return $this->apiResponseSuccess(['data' => $this->uploadService->uploadProfileOrCompanyImage($images,$user,$request->image_for)]);
    }

    public function upload_cover_image(Request $request) {
        $validator = Validator::make(
            [
                'images' => $request->file('images'),
                'image_for' => $request->image_for,
            ],
            [
                'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Add image validation rules for each image
                'image_for' => ['required','in:individual,store,pawnshop,stock_exchange'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $images = $request->file('images');
        $user = auth()->user();
        return $this->apiResponseSuccess(['data' => $this->uploadService->uploadCompanyCoverImages($images,$user,$request->image_for)]);
    }

    public function upload_portfolio_images(Request $request) {
        $validator = Validator::make(
            [
                'images' => $request->file('images'),
                'image_for' => $request->image_for,
            ],
            [
                'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Add image validation rules for each image
                'image_for' => ['required','in:individual,store,pawnshop,stock_exchange'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $images = $request->file('images');
        $user = auth()->user();
        return $this->apiResponseSuccess(['data' => $this->uploadService->uploadPortolioImages($images,$user,$request->image_for)]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make(
            [
                'identification_number' => $request->identification_number,
                'company_type_id' => $request->company_type_id,
                'company_information' => $request->company_information,
            ],
            [
                'identification_number' => ['required', new ValidUniqueCompanyIdentification()],
                'company_type_id' => ['required'],
                'company_information' => ['required'],
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $company = $this->companyService->create(auth()->user(), $request->identification_number, $request->company_type_id, $request->company_information, $request->addresses);
        if ($company) {
            return $this->apiResponseSuccess(['data' => $company]);
        }
        return $this->apiResponseFail('Company Already Exists');
    }

    public function update(Request $request, $company_id)
    {

        $validator = Validator::make(
            [
                'company_id' => $company_id,
                'company_information' => $request->company_information,
            ],
            [
                'company_id' => ['required'],
                'company_information' => ['required'],
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $company = $this->companyService->update($company_id, $request->company_information);

        if ($company) {
            return $this->apiResponseSuccess(['data' => $company]);
        }
        return $this->apiResponseFail('Company Already Exists');
    }

        public function company_limits(Request $request)
    {
        $user = auth()->user();
//
        return $this->apiResponseSuccess(['data' => $this->companyService->findManyByUserWithLimits($user)]);
    }
    public function delete(Request $request, $company_id)
    {

        $validator = Validator::make(
            [
                'company_id' => $company_id,
            ],
            [
                'company_id' => ['required'],
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $company = $this->companyService->delete($company_id);

        if ($company) {
            return $this->apiResponseSuccess(['data' => $company]);
        }
        return $this->apiResponseFail('Company Already Delted');
    }
}
