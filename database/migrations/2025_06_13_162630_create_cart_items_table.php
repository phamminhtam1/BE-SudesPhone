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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('cart_id');
            $table->foreignId('prod_id')->constrained('products', 'prod_id')->cascadeOnDelete();
            $table->integer('qty')->default(1);
            $table->primary(['cart_id', 'prod_id']);
            $table->foreign('cart_id')->references('cart_id')->on('carts')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
