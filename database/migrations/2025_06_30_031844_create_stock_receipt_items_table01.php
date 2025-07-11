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
        Schema::create('stock_receipt_items', function (Blueprint $table) {
            $table->foreignId('receipt_id')->constrained('stock_receipts', 'receipt_id')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products', 'prod_id');
            $table->integer('qty')->unsigned();
            $table->decimal('unit_price', 12, 2);
            $table->primary(['receipt_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_receipt_items');
    }
};
