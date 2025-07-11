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
        Schema::create('products', function (Blueprint $table) {
            $table->id('prod_id');
            $table->foreignId('cat_id')->constrained('categories');
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('sku', 80)->unique()->nullable();
            $table->string('short_desc', 255)->nullable();
            $table->text('long_desc')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->unsignedInteger('warranty_months')->default(12);
            $table->integer('stock_qty')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('meta_title', 150)->nullable();
            $table->string('meta_description', 255)->nullable();
            $table->string('keywords', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
