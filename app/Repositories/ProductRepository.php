<?php

namespace App\Repositories;

use App\Models\Products\Product;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProductRepository
{
    public function __construct(
        private readonly Product $productModel,
    ) {
    }

    public function findMany($type, $createdById, $category,$gem,$material,$gender,$min_price,$max_price,$city,$search,$tags,$stamp,$weight,$customization_available,$isPaidAdv=null)
    {
        $query = $this->productModel;

        // Base filters
        if ($type) {
            $query = $query->where('created_by.type', $type);
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
        if ($material) {
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

        // Search term (assuming you want to search in name and description)
        if ($search) {
            $searchTerm = $search;
            $query = $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
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

        if($isPaidAdv == 1){
            $query = $query->where('is_paid_adv', 1);
        }



        // Sort by update_date in descending order by default
        $query = $query->orderBy('is_paid_adv', 'desc')->orderBy('update_date', 'desc');

        return $query->cursorPaginate(12);
    }

        public function findOneById($id)
    {
        return $this->productModel->where('_id',$id)->first();
    }
    public function create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size,$gender, $phoneNumber,$description, $customization, $city, $price, $tags,$imageUrls,$passportUrls)
    {
        $product = new $this->productModel;
        return $this->setProductAttributes(
            $product,
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

    public function update($id, $createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size,$gender,$phoneNumber, $description, $customization, $city, $price, $tags,$imageUrls,$passportUrls)
    {
        $product = $this->findOneById($id);
        if (!$product) {
            return null;
        }
        return $this->setProductAttributes(
            $product,
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

    private function setProductAttributes($product, $createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $gender,$phoneNumber,$description, $customization, $city, $price, $tags,$imageUrls,$passportUrls)
    {
        if (!isset($product->product_sku)) {
            $product->product_sku = str_pad(random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        }
        if(!isset($product->is_paid_adv)){
            $product->is_paid_adv = 0;
        }
        $product->created_by = ['id' => $createdBy['id'], 'type' => $createdBy['type']];
        $product->representative = ['user_id' => $user->id, 'name' => $user->information['first_name'] . ' ' . $user->information['last_name']];
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
        $product->customization = ['available' => !is_null($customization) ? $customization['available'] :false, 'details' => !is_null($customization)? $customization['details'] : ''];
        $product->city = $city;
        $product->price = (float)$price;
        if (!isset($product->views_count)) {
            $product->views_count = 0;
        }
        $product->tags = $tags;
        $product->image_urls = $imageUrls;
        $product->passport_urls = $passportUrls;
        if (!isset($product->update_date)) {
            $product->update_date = Carbon::now()->toDateTime();
        }

        $product->save();
        return $product;
    }

    public function delete($id)
    {
        return $this->productModel->where('_id', $id)->delete();
    }
}
