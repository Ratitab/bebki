<?php

namespace App\Http\Controllers;

use App\Services\GoogleMerchantService;
use Illuminate\Http\Response;

class GoogleMerchantController extends Controller
{
    public function __construct(
        private GoogleMerchantService $googleMerchantService
    ) {}

    public function feed(): Response
    {
        return $this->googleMerchantService->index();
    }
}
