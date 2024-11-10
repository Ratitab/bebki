<?php

namespace App\Services;

use App\DTO\SearchProductsDTO;
use App\Models\Products\Product;
use App\Repositories\CompanyRepository;
use App\Repositories\FavouriteRepository;
use App\Repositories\FreeLimitRepository;
use App\Repositories\LimitRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private readonly ProductRepository   $productRepository, private readonly CompanyRepository $companyRepository,
                                private readonly UserRepository      $userRepository,
                                private readonly FreeLimitRepository $freeLimitRepository,
                                private readonly LimitRepository     $limitRepository,
                                private readonly FavouriteRepository $favouriteRepository
    )
    {
    }

    public function findMany(SearchProductsDTO $searchDTO)
    {
        $products = $this->productRepository->findMany(
            type: $searchDTO->type,
            createdById: $searchDTO->createdById,
            category: $searchDTO->category,
            gem: $searchDTO->gem,
            material: $searchDTO->material,
            min_price: $searchDTO->minPrice,
            max_price: $searchDTO->maxPrice,
            city: $searchDTO->city,
            search: $searchDTO->search,
            tags: $searchDTO->tags,
            stamp: $searchDTO->stamp,
            weight: $searchDTO->weight,
            customization_available: $searchDTO->customizationAvailable
        );

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
// Fetch favorites if user is authenticated
        $favouriteIds = collect();
        if ($searchDTO->isAuthenticated()) {
            $productIds = $products->pluck('id')->values()->toArray();
            if (!empty($productIds)) {
                $favouriteIds = $this->favouriteRepository->findByUserProductIds(
                    $searchDTO->toArray()['user_id'],
                    $productIds
                )->pluck('data_id');
            }
        }
// Step 4: Modify product items directly in the paginator
        $products->getCollection()->transform(function ($product) use ($companies, $users, $favouriteIds) {
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

            $product->is_favourite = $favouriteIds->contains($product->id);
            unset($product->created_by);
            unset($product->representative);
            return $product;
        });

        return $products;
    }

    public function makeFavourite($user,$dataId)
    {
        $data = $this->productRepository->findOneById($dataId);
        return $this->favouriteRepository->createOrDelete($user->id,$dataId,$data,'product');
    }
    public function countUserFavourites($userId)
    {
        return $this->favouriteRepository->countUserFavourites($userId);
    }

    public function userFavouriteProducts($userId)
    {
        return $this->favouriteRepository->userFavouriteProducts($userId);
    }

    public function create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags, $imageUrls)
    {
        return \DB::transaction(function () use ($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags, $imageUrls) {

            $freeLimit = $this->freeLimitRepository->useLimit($createdBy, $user);

            if (!$freeLimit) {
                $limit = $this->limitRepository->useLimit($createdBy['id']);
                if (!$limit) {
                    return false;
                }
            }

            return $this->productRepository->create(
                $createdBy,
                $user,
                $title,
                $category,
                $material,
                $stamp,
                $weight,
                $gem,
                $size,
                $description,
                $customization,
                $city,
                $price,
                $tags,
                $imageUrls
            );
        });
    }

}
