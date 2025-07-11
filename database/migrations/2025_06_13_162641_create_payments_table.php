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
            $table->bigIncrements('pay_id');
            $table->bigInteger('order_id')->unsigned();
            $table->enum('method', ['cod','bank','momo','visa'])->default('cod');
            $table->decimal('amount', 12, 2);
            $table->enum('pay_status', ['pending','success','failed','refunded'])
                  ->default('pending');
            $table->string('transaction_id', 120)->nullable();
            $table->timestamp('pay_at')->nullable();
            $table->timestamps();
            $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();
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
