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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');

            $table->string('transaction_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();


            $table->string('customer_email')->nullable();
            $table->string('customer_name')->nullable();

            $table->string('payment_provider')->nullable();
            $table->string('provider_transaction_id')->nullable();

            $table->string('status')->default('PENDING');

            $table->string('status_description')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->text('notes')->nullable();

            $table->json('payment_data');
            $table->float('total_amount')->default(0);
            $table->string('currency')->nullable();
            $table->float('exchange_rate')->nullable();
            $table->string('invoice_url')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
