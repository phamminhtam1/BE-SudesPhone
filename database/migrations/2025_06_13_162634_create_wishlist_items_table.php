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
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->foreignId('cust_id')->constrained('customers', 'cust_id')->cascadeOnDelete();
            $table->foreignId('prod_id')->constrained('products', 'prod_id')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['cust_id', 'prod_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
