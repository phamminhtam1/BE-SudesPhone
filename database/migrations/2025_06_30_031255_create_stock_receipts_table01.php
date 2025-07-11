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
        Schema::create('stock_receipts', function (Blueprint $table) {
            $table->id('receipt_id');

            // Thông tin người tạo và chi nhánh
            $table->foreignId('branch_id')->constrained('branches', 'branch_id');
            $table->foreignId('user_id')->constrained('users');

            // Nhà cung cấp (nếu có)
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers', 'supp_id');

            // Thông tin sản phẩm
            $table->foreignId('product_id')->constrained('products', 'prod_id');
            $table->string('sku'); // mã sản phẩm
            $table->unsignedInteger('qty');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_cost', 14, 2); // = qty * unit_price

            // Quản lý và theo dõi
            $table->text('note')->nullable();
            $table->enum('status', ['chờ duyệt', 'đã duyệt', 'đã nhập', 'đã hủy'])->default('chờ duyệt');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('received_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_receipts');
    }
};
