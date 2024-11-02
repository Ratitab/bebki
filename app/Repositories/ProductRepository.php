<?php

namespace App\Repositories;

use App\Models\Products\Product;
use Illuminate\Support\Str;

class ProductRepository
{
    public function __construct(
        private readonly Product $productModel,
    ) {
    }

    public function findMany($type,$createdById)
    {
        $query = $this->productModel;
        if ($type) {
            $query = $query->where('created_by.type', $type);
        }
        if ($createdById) {
            $query = $query->where('created_by._id', $createdById);
        }
        return $query->cursorPaginate(12);
    }
    public function create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags,$imageUrls)
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
            $description,
            $customization,
            $city,
            $price,
            $tags,
            $imageUrls
        );
    }

    public function update($id, $createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags,$imageUrls)
    {
        $product = $this->productModel->find($id);
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
            $description,
            $customization,
            $city,
            $price,
            $tags,
            $imageUrls
        );
    }

    private function setProductAttributes($product, $createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags,$imageUrls)
    {
        $product->created_by = ['id' => $createdBy['id'], 'type' => $createdBy['type']];
        $product->representative = ['user_id' => $user->id, 'name' => $user->information['first_name'] . ' ' . $user->information['last_name']];
        $product->title = $title;
        $product->category = $category;
        $product->material = $material;
        $product->stamp = $stamp;
        $product->weight = $weight;
        $product->gem = $gem;
        $product->size = $size;
        $product->description = $description;
        $product->customization = ['available' => !is_null($customization) ? $customization['available'] :false, 'details' => !is_null($customization)? $customization['details'] : ''];
        $product->city = $city;
        $product->price = $price;
        if (!isset($product->views_count)) {
            $product->views_count = 0;
        }
        $product->tags = $tags;
        $product->image_urls = $imageUrls;

        $product->save();
        return $product;
    }

    public function delete($id)
    {
        return $this->productModel->where('_id', $id)->delete();
    }
}
