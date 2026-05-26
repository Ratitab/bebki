<?php

namespace App\Console\Commands;

use App\Models\Products\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillProductShopName extends Command
{
    protected $signature = 'products:backfill-shop-name';
    protected $description = 'Backfill representative.shop_name on all existing products';

    public function handle(): void
    {
        $products = Product::whereNotNull('created_by')->get();
        $updated = 0;

        foreach ($products as $product) {
            $companyId = data_get($product->created_by, 'id');
            if (!$companyId) continue;

            $shopName = DB::table('company_information')
                ->join('company_information_types', 'company_information.company_information_type_id', '=', 'company_information_types.id')
                ->where('company_information.company_id', $companyId)
                ->where('company_information_types.name', 'name')
                ->whereNull('company_information.deleted_at')
                ->value('company_information.value');

            if (!$shopName) continue;

            $rep = $product->representative ?? [];
            if (is_array($rep)) {
                $rep['shop_name'] = $shopName;
            } else {
                $rep = (array) $rep;
                $rep['shop_name'] = $shopName;
            }

            $product->representative = $rep;
            $product->save();
            $updated++;
            $this->line("Updated: {$product->_id} → {$shopName}");
        }

        $this->info("Done. Updated {$updated} products.");
    }
}
