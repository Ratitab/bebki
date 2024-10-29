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

    public function findMany()
    {

        return $this->productModel->cursorPaginate(12);
    }
    public function create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags)
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
            $tags
        );
    }

    public function update($id, $createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags)
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
            $tags
        );
    }

    private function setProductAttributes($product, $createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $description, $customization, $city, $price, $tags)
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
        $product->customization = ['available' => $customization['available'], 'details' => $customization['details']];
        $product->city = $city;
        $product->price = $price;
        if (!isset($product->views_count)) {
            $product->views_count = 0;
        }
        $product->tags = $tags;

        $product->save();
        return $product;
    }

    public function delete($id)
    {
        return $this->productModel->where('_id', $id)->delete();
    }
}
