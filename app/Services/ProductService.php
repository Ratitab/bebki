<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private readonly ProductRepository $productRepository)
    {
    }

    public function create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags)
    {
        return $this->productRepository->create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags);
    }

}
