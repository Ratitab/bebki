<?php

namespace App\Repositories;

use App\Models\Products\Boost;
use Illuminate\Support\Str;

class BoostRepository
{
    public function __construct(
        private readonly Boost $boostModel,
    )
    {
    }


    public function setPaidAdvAttributes($product, $paid_adv_expires_at)
    {
        $product->paid_adv_expires_at = $paid_adv_expires_at;
        $product->save();
        return $product;
    }

}
