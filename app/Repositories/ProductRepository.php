<?php

namespace App\Repositories;

use App\Models\Products\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductRepository
{
    public function __construct(
        private readonly Product $productModel,
    )
    {
    }

    public function findMany($type, $createdById, $category, $gem, $material, $gender, $min_price, $max_price, $city, $search, $tags, $stamp, $weight, $customization_available, $isPaidAdv = null, $excludeCompanyIds = [])
    {
        $query = $this->productModel->where('is_sold', 0);

        if (!empty($excludeCompanyIds)) {
            $query = $query->whereNotIn('created_by.id', $excludeCompanyIds);
        }

        // Base filters
        if ($type && is_array($type) && count($type) >= 1) {
            if (count($type) > 1 || (count($type) == 1 && $type[0] !== '' && $type[0] !== null)) {
                $query = $query->whereIn('created_by.type', $type);
            }
        }
        if ($createdById) {
            $query = $query->where('created_by._id', $createdById);
        }

        // Category filter
        if ($category) {
            $query = $query->whereIn('category', $category);
        }

        // Gem filter
        if ($gem) {
            $query = $query->whereIn('gem', $gem);
        }

        // Material filter
        if ($material) {
            $query = $query->whereIn('material', $material);
        }

        // Gender filter
        if ($gender) {
            $query = $query->whereIn('gender', $gender);
        }

        // Price range filter
        if ($min_price) {
            $query = $query->where('price', '>=', (float)$min_price);
        }
        if ($max_price) {
            $query = $query->where('price', '<=', (float)$max_price);
        }

        // City filter
        if ($city) {
            $query = $query->whereIn('city', $city);
        }

        // Search term — matches title, description, or any tag
        if ($search) {
            $searchTerm = $search;
            $query = $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('tags', 'like', "%{$searchTerm}%");
            });
        }

        // Tags filter (assuming tags is an array)
        if ($tags) {
            if (is_array($tags) && !empty($tags)) {
                $query = $query->whereIn('tags', $tags);
            } else if (!is_array($tags)) {
                $query = $query->where('tags', $tags);
            }
        }

        if ($stamp) {
            $query = $query->whereIn('stamp', $stamp);
        }

        if ($weight) {
            $query = $query->whereIn('weight', (string)$weight);
        }

        if ($customization_available !== null) {
            $query = $query->where('customization.available',
                (bool)$customization_available);
        }

        if ($isPaidAdv == 1) {
            $query = $query->where('is_paid_adv', 1);
        }
        if ($isPaidAdv === 0) {
            $query = $query->where('is_paid_adv', 0);
        }


        // Sort by update_date in descending order by default
        $query = $query->orderBy('is_paid_adv', 'desc')->orderBy('update_date', 'desc');

        return $query->paginate(12);
    }

    public function findOneById($id)
    {
        return $this->productModel->where('_id', $id)->first();
    }

    public function findOneBySlug($slug)
    {
        return $this->productModel->where('slug', $slug)->first();
    }


    public function findOneByIdOrSlug($identifier)
    {
        // Determine if it's a MongoDB ID or slug
        if ($this->isValidMongoId($identifier)) {
            return $this->findOneById($identifier);
        } else {
            return $this->findOneBySlug($identifier);
        }
    }

    private function isValidMongoId($id)
    {
        // MongoDB ObjectId validation
        return is_string($id) && preg_match('/^[0-9a-fA-F]{24}$/', $id);
    }

    public function create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $gender, $phoneNumber, $description, $customization, $city, $price, $tags, $imageUrls, $passportUrls, $variants = null, $leadTime = null, $quantity = null, $stockMode = null)
    {
        $product = new $this->productModel;
        return $this->setProductAttributes(
            $product, $createdBy, $user, $title, $category, $material, $stamp, $weight,
            $gem, $size, $gender, $phoneNumber, $description, $customization, $city,
            $price, $tags, $imageUrls, $passportUrls, $variants, $leadTime, $quantity, $stockMode
        );
    }

    public function update($id, $createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $gender, $phoneNumber, $description, $customization, $city, $price, $tags, $imageUrls, $passportUrls, $variants = null, $leadTime = null, $quantity = null, $stockMode = null)
    {
        $product = $this->findOneById($id);
        if (!$product) {
            return null;
        }
        return $this->setProductAttributes(
            $product, $createdBy, $user, $title, $category, $material, $stamp, $weight,
            $gem, $size, $gender, $phoneNumber, $description, $customization, $city,
            $price, $tags, $imageUrls, $passportUrls, $variants, $leadTime, $quantity, $stockMode
        );
    }


    public function setProductUpdateDate($id)
    {
        $product = $this->findOneById($id);

        if (is_null($product)) {
            return null;
        }

        $product->update_date = Carbon::now()->toDateTime();
        $product->save();

        return $product;
    }

    private function setProductAttributes($product, $createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $gender, $phoneNumber, $description, $customization, $city, $price, $tags, $imageUrls, $passportUrls, $variants = null, $leadTime = null, $quantity = null, $stockMode = null)
    {
        if (!isset($product->product_sku)) {
            $product->product_sku = str_pad(random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        }
        if (!isset($product->is_paid_adv)) {
            $product->is_paid_adv = 0;
        }
        $product->created_by = ['id' => $createdBy['id'], 'type' => $createdBy['type']];
        $shopName = DB::table('company_information')
            ->join('company_information_types', 'company_information.company_information_type_id', '=', 'company_information_types.id')
            ->where('company_information.company_id', $createdBy['id'])
            ->where('company_information_types.name', 'name')
            ->whereNull('company_information.deleted_at')
            ->value('company_information.value');
        $product->representative = [
            'user_id'   => $user->id,
            'name'      => $user->information['first_name'] . ' ' . $user->information['last_name'],
            'shop_name' => $shopName ?? '',
        ];
        $product->title = $title;
        $product->category = $category;
        $product->material = $material;
        $product->stamp = $stamp;
        $product->weight = $weight;
        $product->gem = $gem;
        $product->size = $size;
        $product->gender = $gender;
        $product->phone_number = $phoneNumber;
        $product->description = $description;
        $product->customization = ['available' => !is_null($customization) ? $customization['available'] : false, 'details' => !is_null($customization) ? $customization['details'] : ''];
        $product->city = $city;
        $product->price = (float)$price;
        if (!isset($product->views_count)) {
            $product->views_count = 0;
        }
        if (!isset($product->favorite_count)) {
            $product->favorite_count = 0;
        }
        if (!isset($product->is_sold)) {
            $product->is_sold = 0;
        }
        $product->tags = $tags;
        $product->image_urls = $imageUrls;
        $product->passport_urls = $passportUrls;
        $product->variants = is_array($variants) ? $variants : [];
        if (!is_null($stockMode)) {
            $product->stock_mode = $stockMode;
        }
        if (!is_null($quantity)) {
            $product->quantity = (int)$quantity;
            $product->lead_time = null;
        } elseif (!is_null($leadTime) && $leadTime !== '') {
            $product->lead_time = $leadTime;
            $product->quantity = null;
        }
        if (!isset($product->update_date)) {
            $product->update_date = Carbon::now()->toDateTime();
        }

        $product->save();
        return $product;
    }

    public function homepageFeed(array $excludeCompanyIds = []): array
    {
        $fields = ['_id', 'title', 'price', 'image_urls', 'category', 'material', 'city',
                   'views_count', 'favorite_count', 'created_by', 'representative', 'tags', 'variants'];

        $baseQuery = fn() => $this->productModel->where('is_sold', 0)
            ->when(!empty($excludeCompanyIds), fn($q) => $q->whereNotIn('created_by.id', $excludeCompanyIds));

        $popular = $baseQuery()
            ->orderBy('views_count', 'desc')
            ->orderBy('update_date', 'desc')
            ->limit(12)
            ->get($fields);

        $new = $baseQuery()
            ->orderBy('update_date', 'desc')
            ->limit(12)
            ->get($fields);

        // Use raw MongoDB aggregation so null favorite_count is treated as 0
        $featuredRaw = $this->productModel->raw(function ($collection) use ($fields, $excludeCompanyIds) {
            $project = array_fill_keys($fields, 1);
            $project['favorite_count'] = ['$ifNull' => ['$favorite_count', 0]];
            $match = ['is_sold' => 0, 'deleted_at' => null];
            if (!empty($excludeCompanyIds)) {
                $match['created_by.id'] = ['$nin' => $excludeCompanyIds];
            }
            return $collection->aggregate([
                ['$match'   => $match],
                ['$project' => $project],
                ['$sort'    => ['favorite_count' => -1, 'views_count' => -1]],
                ['$limit'   => 12],
            ]);
        });

        $unique = $baseQuery()
            ->where('stock_mode', 'unique')
            ->orderBy('update_date', 'desc')
            ->limit(12)
            ->get($fields);

        return [
            'popular'  => $popular,
            'new'      => $new,
            'featured' => $featuredRaw,
            'unique'   => $unique,
        ];
    }

    public function delete($id)
    {
        return $this->productModel->where('_id', $id)->delete();
    }

    public function sold($id)
    {
        $product = $this->productModel->where('_id', $id)->first();
        $product->is_sold = 1;
        $product->save();
        return $product;
    }
}
