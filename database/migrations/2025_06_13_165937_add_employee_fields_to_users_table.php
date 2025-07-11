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
        Schema::table('users', function (Blueprint $table) {
            /*— Liên kết chi nhánh & vai trò (có thể null cho tài khoản khách) —*/
            $table->foreignId('branch_id')
                  ->nullable()
                  ->after('id')           // nằm ngay sau id
                  ->constrained('branches')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('role_id')
                  ->nullable()
                  ->after('branch_id')
                  ->constrained('roles')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            /*— Thông tin nhân viên —*/
            $table->string('phone', 20)
                  ->nullable()
                  ->after('email');

            $table->date('hire_date')
                  ->nullable()
                  ->after('phone');

            $table->decimal('salary', 12, 2)
                  ->nullable()
                  ->after('hire_date');
        });
    }

    /**
     * Gỡ các cột vừa thêm (khi rollback).
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            /* Phải drop FK trước khi drop cột */
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['role_id']);

            $table->dropColumn([
                'branch_id',
                'role_id',
                'phone',
                'hire_date',
                'salary',
            ]);
        });
    }
};
