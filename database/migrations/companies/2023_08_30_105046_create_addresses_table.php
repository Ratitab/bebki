<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->string('address');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('postal_code')->nullable();
            $table->boolean('is_same_time')->default(true);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
