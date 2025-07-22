<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id'); // ✅ Primary key

            // 🔗 Foreign keys
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('prod_id');

            // 🛒 Item details
            $table->integer('qty');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);

            // 🧷 Constraints
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
            $table->foreign('prod_id')->references('prod_id')->on('products')->onDelete('restrict');

            // 🔍 Optional: Tránh trùng sản phẩm trong 1 đơn
            $table->unique(['order_id', 'prod_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
