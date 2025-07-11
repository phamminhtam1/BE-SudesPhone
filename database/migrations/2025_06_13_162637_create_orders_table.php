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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->foreignId('cust_id')->constrained('customers', 'cust_id');
            $table->foreignId('branch_id')->nullable()->constrained('branches', 'branch_id');
            $table->enum('order_status', ['pending','paid','shipped','completed','cancelled'])
                  ->default('pending');
            $table->enum('payment_status', ['unpaid','paid','refunded'])
                  ->default('unpaid');
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('sub_total', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->timestamps();           // placed_at = created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
