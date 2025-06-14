<?php

namespace App\Repositories;

use App\Models\Products\PawnshopProduct;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PawnshopProductRepository
{
    public function __construct(
        private readonly PawnshopProduct $pawnshopProductModel,
    )
    {
    }

    public function findManyByPawnshopId($company_id, $search = null)
    {
        $query = $this->pawnshopProductModel;

        $query = $query->where('company_id', $company_id);
        if ($search) {
            $searchTerm = $search;
            $query = $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        $query = $query->orderBy('created_at', 'desc');

        return $query->paginate(12);
    }

    public function findOneById($id)
    {
        return $this->pawnshopProductModel->where('_id', $id)->first();
    }

    public function create($company_id,$title, $material, $stamp, $weight, $gem, $size, $phoneNumber, $description,  $imageUrls)
    {
        $product = new $this->pawnshopProductModel;
        return $this->setPawnshopProductAttributes(
            $product,
            $company_id,
            $title,
            $material,
            $stamp,
            $weight,
            $gem,
            $size,
            $phoneNumber,
            $description,
            $imageUrls,
        );
    }

    public function update($id,  $company_id, $title, $material, $stamp, $weight, $gem, $size, $phoneNumber, $description,  $imageUrls)
    {
        $product = $this->findOneById($id);
        if (!$product) {
            return null;
        }
        return $this->setPawnshopProductAttributes(
            $product,
            $company_id,
            $title,
            $material,
            $stamp,
            $weight,
            $gem,
            $size,
            $phoneNumber,
            $description,
            $imageUrls,
        );
    }


    private function setPawnshopProductAttributes($product, $company_id, $title, $material, $stamp, $weight, $gem, $size,  $phoneNumber, $description, $imageUrls, $review = 'pending')
    {

        $product->company_id = $company_id;
        $product->title = $title;
        $product->material = $material;
        $product->stamp = $stamp;
        $product->weight = $weight;
        $product->gem = $gem;
        $product->size = $size;
        $product->phone_number = $phoneNumber;
        $product->description = $description;
        $product->image_urls = $imageUrls;
        $product->review = $review;
        if (!isset($product->update_date)) {
            $product->update_date = Carbon::now()->toDateTime();
        }

        $product->save();
        return $product;
    }

    public function changeStatus($id, $status)
    {
        $product = $this->findOneById($id);
        if (!$product) {
            return null;
        }
        $product->review = $status;
        $product->save();
        return $product;
    }

    public function delete($id)
    {
        return $this->pawnshopProductModel->where('_id', $id)->delete();
    }
}
