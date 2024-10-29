<?php

namespace App\Services;

use App\Repositories\CompanyRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
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
            ->values()  // This ensures we get a clean array
            ->toArray();

        $userIds = $products->where('created_by.type', 'user')
            ->pluck('created_by.id')
            ->unique()
            ->values()
            ->toArray();

        // Step 3: Fetch company and user data in separate queries
        // Only fetch if we have IDs to prevent unnecessary queries
        $companies = !empty($companyIds)
            ? $this->companyRepository->findManyById($companyIds)->keyBy('id')
            : collect();

        $users = !empty($userIds)
            ? $this->userRepository->findManyById($userIds)->keyBy('id')
            : collect();

        // Step 4: Map the respective data (company or user) to each product
        $productsWithDetails = $products->map(function ($product) use ($companies, $users) {
            $entityId = $product->created_by['id'] ?? null;
            $entityType = $product->created_by['type'] ?? null;

            $product = $product->toArray(); // Convert to array if you need it as JSON

            if ($entityType === 'company') {
                $company_data = $companies->get($entityId);
                $product['creator'] = [
                    'name' => $company_data?->information['name'] ?? null,
                    'logo' => $company_data?->information['logo'] ?? null
                ];
            } elseif ($entityType === 'user') {
                $user_data = $users->get($entityId);
                $product['creator'] = [
                    'first_name' => $user_data?->information['first_name'] ?? null,
                    'last_name' => $user_data?->information['last_name'] ?? null
                ];
            } else {
                $product['creator'] = null;
            }

            return $product;
        });

        return $productsWithDetails;
    }

    public function create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags)
    {
        return $this->productRepository->create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags);
    }

}
