<?php

namespace App\Http\Controllers;

use App\Services\CompanyService;
use App\Services\CountryService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    use Resp;

    public function __construct(private readonly CountryService $countryService)
    {
    }

    public function index(Request $request)
    {
        return $this->apiResponseSuccess(['data' => $this->countryService->index()]);
    }

    public function citiesFindByCountryId(Request $request)
    {
        return $this->apiResponseSuccess(['data' => $this->countryService->findByCountryId()]);
    }
}
