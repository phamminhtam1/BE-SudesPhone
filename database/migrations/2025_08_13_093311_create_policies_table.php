<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->text('content');
        });

        DB::table('policies')->insert([
            'content' => 'Nội dung giới thiệu ban đầu'
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
