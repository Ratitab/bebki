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

    public function uploadProductImages($images,$user,$image_for)
    {
        $imageUrls = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                try {
                    // Upload image to DigitalOcean Spaces
                    $imagePath = Storage::disk('spaces')->put($user->id . '/' . $image_for . '/product-images', $image, 'public');
                    $originalUrl = Storage::disk('spaces')->url($imagePath);

                    // Replace the Spaces URL with the CDN URL
                    $cdnUrl = 'https://cdn.gegold.ge';
                    $cdnImageUrl = str_replace('https://fra1.digitaloceanspaces.com', $cdnUrl, $originalUrl);

                    $imageUrls[] = $cdnImageUrl;
                }catch (\Exception $e) {
                    // Log the error or handle it accordingly
                    \Log::error('Image upload failed: ' . $e->getMessage());
                }
            }
        } else {
            \Log::warning('Provided images data is not an array.');
        }
        return $imageUrls;
    }

    public function uploadCompanyCoverImages($images,$user, $image_for)
    {
        $imageUrls = [];
        if (is_array($images)) {
                try {
                    // Upload image to DigitalOcean Spaces
                    $imagePath = Storage::disk('spaces')->put($user->id . '/cover-images', $images[0], 'public');
                    $originalUrl = Storage::disk('spaces')->url($imagePath);

                    // Replace the Spaces URL with the CDN URL
                    $cdnUrl = 'https://cdn.gegold.ge'; // aq bebki cdn url
                    $cdnImageUrl = str_replace('https://fra1.digitaloceanspaces.com', $cdnUrl, $originalUrl);

                    $imageUrls[] = $cdnImageUrl;
                }catch (\Exception $e) {
                    // Log the error or handle it accordingly
                    \Log::error('Image upload failed: ' . $e->getMessage());
                }
        } else {
            \Log::warning('Provided images data is not an array.');
        }
        return $imageUrls;
    }

    public function uploadPortolioImages($images,$user, $image_for)
    {
        $imageUrls = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                try {
                    // Upload image to DigitalOcean Spaces
                    $imagePath = Storage::disk('spaces')->put($user->id . $image_for . '/portofio_images', $image, 'public');
                    $originalUrl = Storage::disk('spaces')->url($imagePath);

                    // Replace the Spaces URL with the CDN URL
                    $cdnUrl = 'https://cdn.gegold.ge';
                    $cdnImageUrl = str_replace('https://fra1.digitaloceanspaces.com', $cdnUrl, $originalUrl);

                    $imageUrls[] = $cdnImageUrl;
                }catch (\Exception $e) {
                    // Log the error or handle it accordingly
                    \Log::error('Image upload failed: ' . $e->getMessage());
                }
            }
        } else {
            \Log::warning('Provided images data is not an array.');
        }
        return $imageUrls;
    }

    public function uploadProfileOrCompanyImage($images,$user,$image_for)
    {
        $imageUrls = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                try {
                    // Upload image to DigitalOcean Spaces
                    $imagePath = Storage::disk('spaces')->put($user->id . '/' . $image_for . '-images', $image, 'public');
                    $originalUrl = Storage::disk('spaces')->url($imagePath);

                    // Replace the Spaces URL with the CDN URL
                    $cdnUrl = 'https://cdn.gegold.ge';
                    $cdnImageUrl = str_replace('https://fra1.digitaloceanspaces.com', $cdnUrl, $originalUrl);

                    $imageUrls[] = $cdnImageUrl;
                }catch (\Exception $e) {
                    // Log the error or handle it accordingly
                    \Log::error('Image upload failed: ' . $e->getMessage());
                }
            }
        } else {
            \Log::warning('Provided images data is not an array.');
        }
        return $imageUrls;
    }



    public function uploadBlogImages($images,$user)
    {
        $imageUrls = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                try {
                    // Upload image to DigitalOcean Spaces
                    $imagePath = Storage::disk('spaces')->put($user->id . '/blogs', $image, 'public');
                    $originalUrl = Storage::disk('spaces')->url($imagePath);

                    // Replace the Spaces URL with the CDN URL
                    $cdnUrl = 'https://cdn.gegold.ge';
                    $cdnImageUrl = str_replace('https://fra1.digitaloceanspaces.com', $cdnUrl, $originalUrl);

                    $imageUrls[] = $cdnImageUrl;
                }catch (\Exception $e) {
                    // Log the error or handle it accordingly
                    \Log::error('Image upload failed: ' . $e->getMessage());
                }
            }
        } else {
            \Log::warning('Provided images data is not an array.');
        }
        return $imageUrls;
    }

    public function uploadAnnouncementImages($images,$user)
    {
        $imageUrls = [];
        if (is_array($images)) {
                try {
                    // Upload image to DigitalOcean Spaces
                    $imagePath = Storage::disk('spaces')->put($user->id . '/announcements', $images[0], 'public');
                    $originalUrl = Storage::disk('spaces')->url($imagePath);

                    // Replace the Spaces URL with the CDN URL
                    $cdnUrl = 'https://cdn.gegold.ge'; // aq bebki cdn url
                    $cdnImageUrl = str_replace('https://fra1.digitaloceanspaces.com', $cdnUrl, $originalUrl);

                    $imageUrls[] = $cdnImageUrl;
                }catch (\Exception $e) {
                    // Log the error or handle it accordingly
                    \Log::error('Image upload failed: ' . $e->getMessage());
                }
        } else {
            \Log::warning('Provided images data is not an array.');
        }
        return $imageUrls;
    }


}
