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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id('po_id');
            $table->foreignId('supp_id')->constrained('suppliers', 'supp_id');
            $table->foreignId('branch_id')->constrained('branches', 'branch_id');
            $table->foreignId('id')->nullable()->constrained('users');
            $table->decimal('total_cost', 14, 2)->nullable();
            $table->enum('status', ['pending', 'received', 'cancelled'])
                  ->default('pending');
            $table->timestamps();
            $table->timestamp('received_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
