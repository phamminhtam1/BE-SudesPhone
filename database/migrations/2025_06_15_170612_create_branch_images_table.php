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
    Schema::create('branch_images', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('branch_id');
        $table->string('path');
        $table->string('type')->nullable();
        $table->timestamps();

        $table->foreign('branch_id')
              ->references('branch_id')->on('branches')
              ->onDelete('cascade')
              ->onUpdate('cascade');
    });
}

};
