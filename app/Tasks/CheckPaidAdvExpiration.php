<?php

namespace App\Tasks;

use App\Models\Products\Product;
use Carbon\Carbon;

class CheckPaidAdvExpiration
{
    public function __invoke()
    {
        // Your model import here
        Product::where('paid_adv_expires_at', '<', Carbon::now()->toDateTime())
            ->update([
                'paid_adv_expires_at' => null,
                'is_paid_adv' => 0
            ]);
    }
}
