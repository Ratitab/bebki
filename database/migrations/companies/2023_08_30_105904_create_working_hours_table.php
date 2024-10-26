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
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->uuid('address_id');
            $table->string('day_of_week', 20);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_selected')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
