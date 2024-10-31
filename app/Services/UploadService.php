<?php

namespace App\Services;

use App\Repositories\AddressRepository;
use App\Repositories\CityRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CompanyUserRepository;
use App\Repositories\CountryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadService
{
    public function __construct()
    {
    }

    public function uploadProductImages($images)
    {
        $imageUrls = [];

        if ($images) {
            foreach ($images as $image) {
                $imagePath = Storage::disk('spaces')->put('product-images', $image, 'public');
                $imageUrls[] = Storage::disk('spaces')->url($imagePath);
            }
        }
        return $imageUrls;
    }



}
