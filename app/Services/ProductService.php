<?php

namespace App\Services;

use App\Repositories\CompanyRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private readonly ProductRepository $productRepository, private readonly CompanyRepository $companyRepository, private readonly UserRepository $userRepository)
    {
    }

    public function findMany()
    {
        $products = $this->productRepository->findMany();

// Step 2: Separate company and user IDs based on `created_by.type`
        $companyIds = $products->where('created_by.type', 'company')
            ->pluck('created_by.id')
            ->unique()
            ->values()
            ->toArray();

        $userIds = $products->where('created_by.type', 'user')
            ->pluck('created_by.id')
            ->unique()
            ->values()
            ->toArray();

// Step 3: Fetch company and user data in separate queries
        $companies = !empty($companyIds)
            ? $this->companyRepository->findManyById($companyIds)->keyBy('id')
            : collect();

        $users = !empty($userIds)
            ? $this->userRepository->findManyById($userIds)->keyBy('id')
            : collect();

// Step 4: Modify product items directly in the paginator
        $products->getCollection()->transform(function ($product) use ($companies, $users) {
            $entityId = $product->created_by['id'] ?? null;
            $entityType = $product->created_by['type'] ?? null;

            // Prepare creator information based on type
            if ($entityType === 'company') {
                $company_data = $companies->get($entityId);
                $product->creator = [
                    'name' => $company_data->getAttributes()['information']['name'] ?? null,
                    'logo' => $company_data->getAttributes()['information']['logo'] ?? null
                ];
            } elseif ($entityType === 'user') {
                $user_data = $users->get($entityId);
                $product->creator = [
                    'first_name' => $user_data->getAttributes()['information']['first_name'] ?? null,
                    'last_name' => $user_data->getAttributes()['information']['last_name'] ?? null
                ];
            } else {
                $product->creator = null;
            }

            unset($product->created_by);
            unset($product->representative);
            return $product;
        });

        return $products;
    }

    public function create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags,$images)
    {
        return $this->productRepository->create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags,$imageUrls);
    }

}
