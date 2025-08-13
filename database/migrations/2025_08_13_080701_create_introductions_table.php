<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('introductions', function (Blueprint $table) {
            $table->string('title', 255);
            $table->text('content');
        });

        DB::table('introductions')->insert([
            'title' => 'Giới thiệu',
            'content' => 'Nội dung giới thiệu ban đầu'
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('introductions');
    }
};
