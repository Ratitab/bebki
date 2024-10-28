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

    public function create()
    {
        $product = new $this->productModel;
        $product->created_by = ['id'=>'','type'=>''];
        $product->representative = ['user_id'=>'','name'=>''];
        $product->title ='';
        $product->category ='';
        $product->material ='';
        $product->stamp ='';
        $product->weight ='';
        $product->gem ='';
        $product->size ='';
        $product->description ='';
        $product->can_make_customization ='';
        $product->can_make_customization_description ='';
        $product->city ='';

        return 1;
    }

    public function delete($id)
    {
        return $this->productModel->where('_id', $id)->delete();
    }

}
