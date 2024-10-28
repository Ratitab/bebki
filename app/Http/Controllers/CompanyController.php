<?php

namespace App\Http\Controllers;

use App\Rules\ValidUniqueCompanyIdentification;
use App\Services\CompanyService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    use Resp;

    public function __construct(private readonly CompanyService $companyService)
    {
    }

    public function index(Request $request)
    {
        return $this->apiResponseSuccess(['data' => $this->companyService->findManyByUser(auth()->user())]);
    }

    public function show(Request $request,$company_id)
    {
        return $this->apiResponseSuccess(['data' => $this->companyService->findOneByUser(auth()->user(),$company_id)]);
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
}
