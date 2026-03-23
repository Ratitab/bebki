<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Any category titled "Other" (case-insensitive) always sorts last.
        DB::table('categories')
            ->whereRaw("LOWER(title) = 'other'")
            ->update(['order_id' => 9999]);
    }

    public function down(): void
    {
        DB::table('categories')
            ->whereRaw("LOWER(title) = 'other'")
            ->update(['order_id' => 0]);
    }
};
