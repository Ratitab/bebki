<?php

namespace App\Services;

use App\DTO\SearchProductsDTO;
use App\DTO\SingleProductDTO;
use App\Models\Products\Product;
use App\Repositories\BoostRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\FavouriteRepository;
use App\Repositories\FreeLimitRepository;
use App\Repositories\LimitRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private readonly ProductRepository   $productRepository, private readonly CompanyRepository $companyRepository,
                                private readonly UserRepository      $userRepository,
                                private readonly FreeLimitRepository $freeLimitRepository,
                                private readonly LimitRepository     $limitRepository,
                                private readonly FavouriteRepository $favouriteRepository,
                                private readonly BoostRepository $boostRepository,
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
            gender: $searchDTO->gender,
            min_price: $searchDTO->minPrice,
            max_price: $searchDTO->maxPrice,
            city: $searchDTO->city,
            search: $searchDTO->search,
            tags: $searchDTO->tags,
            stamp: $searchDTO->stamp,
            weight: $searchDTO->weight,
            customization_available: $searchDTO->customizationAvailable,
            isPaidAdv: $searchDTO->isPaidAdv
        );

// Step 2: Separate company and user IDs based on `created_by.type`
        $companyIds = $products->whereIn('created_by.type', ['store', 'pawnshop'])
            ->pluck('created_by.id')
            ->unique()
            ->values()
            ->toArray();

        $userIds = $products->where('created_by.type', 'individual')
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
            if ($entityType === 'store' || $entityType ==='pawnshop') {
                $company_data = $companies->get($entityId);
                $product->creator = [
                    'name' => $company_data->getAttributes()['information']['name'] ?? null,
                    'logo' => $company_data->getAttributes()['information']['logo'] ?? null
                ];
            } elseif ($entityType === 'individual') {
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

    public function findOne(SingleProductDTO $productDTO)
    {
// Step 1: Fetch the product
        $product = $this->productRepository->findOneById($productDTO->productId);

        if (!$product) {
            return null;
        }

// Step 2: Get entity ID and type from created_by
        $entityId = $product->created_by['id'] ?? null;
        $entityType = $product->created_by['type'] ?? null;
// Step 3: Fetch creator data based on type
        $creator = null;
        if ($entityType === 'store' || $entityType === 'pawnshop') {
            $company = $this->companyRepository->findOneById($entityId);
            $creator = [
                'name' => $company?->getAttributes()['information']['name'] ?? null,
                'logo' => $company?->getAttributes()['information']['logo'] ?? null
            ];
        } elseif ($entityType === 'individual') {
            $user = $this->userRepository->findOneById($entityId);
            $creator = [
                'first_name' => $user?->getAttributes()['information']['first_name'] ?? null,
                'last_name' => $user?->getAttributes()['information']['last_name'] ?? null
            ];
        }
// Step 4: Check if product is favourite for authenticated user
        $isFavourite = false;
        if ($productDTO->isAuthenticated()) {
            $isFavourite = $this->favouriteRepository->findByUserProductIds(
                $productDTO->toArray()['user_id'],
                [$product->id]
            )->isNotEmpty();
        }
// Step 5: Transform the product
        unset($product->created_by);
        unset($product->representative);
        $product->creator = $creator;
        $product->is_favourite = $isFavourite;

        return $product;
    }

    public function makeFavourite($user, $dataId)
    {
        $data = $this->productRepository->findOneById($dataId);
        return $this->favouriteRepository->createOrDelete($user->id, $dataId, $data, 'product');
    }

    public function countUserFavourites($userId)
    {
        return $this->favouriteRepository->countUserFavourites($userId);
    }

    public function userFavouriteProducts($userId)
    {
        return $this->favouriteRepository->userFavouriteProducts($userId);
    }

    public function create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $gender,$phoneNumber,$description, $customization, $city, $price, $tags, $imageUrls, $passportUrls)
    {
        return \DB::transaction(function () use ($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $gender,$phoneNumber,$description, $customization, $city, $price, $tags, $imageUrls, $passportUrls) {

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
                $gender,
                $phoneNumber,
                $description,
                $customization,
                $city,
                $price,
                $tags,
                $imageUrls,
                $passportUrls
            );
        });
    }

    public function update($id, $createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size,$gender,$phoneNumber, $description, $customization, $city, $price, $tags, $imageUrls, $passportUrls)
    {
        return $this->productRepository->update($id,
            $createdBy,
            $user,
            $title,
            $category,
            $material,
            $stamp,
            $weight,
            $gem,
            $size,
            $gender,
            $phoneNumber,
            $description,
            $customization,
            $city,
            $price,
            $tags,
            $imageUrls,
            $passportUrls
        );
    }

    public function update_product_order($id, $createdBy, $user)
    {
        return \DB::transaction(function () use ($id, $createdBy, $user) {
            $freeLimit = $this->freeLimitRepository->useLimit($createdBy, $user);

            if (!$freeLimit) {
                $limit = $this->limitRepository->useLimit($createdBy['id']);
                if (!$limit) {
                    return false;
                }
            }

            return $this->productRepository->setProductUpdateDate($id);
        });
    }

    public function set_paid_adv($id, $paid_adv_expires_at,$createdBy,$user)
    {
        $product = $this->productRepository->findOneById($id);
        if (!$product) {
            return false;
        }
        $premiumDurations = [
            ['days' => '1', 'label' => '24 Hours Boost', 'pearls' => 3],
            ['days' => '5', 'label' => '5 Days Featured', 'pearls' => 8],
            ['days' => '7', 'label' => '1 Week Premium', 'pearls' => 10],
            ['days' => '10', 'label' => '10 Days Pro', 'pearls' => 13],
            ['days' => '15', 'label' => '15 Days Plus', 'pearls' => 17],
            ['days' => '20', 'label' => '20 Days Ultra', 'pearls' => 20],
            ['days' => '30', 'label' => '1 Month Elite', 'pearls' => 25]
        ];

        $pearls = 3 * $paid_adv_expires_at; // Default calculation

        foreach ($premiumDurations as $duration) {
            if ((int)$duration['days'] === (int)$paid_adv_expires_at) {
                $pearls = $duration['pearls'];
                break;
            }
        }
        $freeLimit = $this->freeLimitRepository->useLimit(createdBy:$createdBy, user:$user,pearls:$pearls);

        if (!$freeLimit) {
            $limit = $this->limitRepository->useLimit($createdBy['id'], $pearls);
            if (!$limit) {
                return false;
            }
        }
        $expiryDate = Carbon::now()->addDays($paid_adv_expires_at)->toDateTime();
        if ($product->is_paid_adv === 1 && $product->paid_adv_expires_at > Carbon::now()) {
            $expiryDate = Carbon::parse($product->paid_adv_expires_at)->addDays($paid_adv_expires_at)->toDateTime();
        }
        $this->boostRepository->setPaidAdvAttributes($product,$expiryDate);
        return true;
    }

}
