<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo vai trò mẫu
        $roles = [
            ['name' => 'Giám đốc', 'description' => 'Giám đốc công ty'],
            ['name' => 'Quản lý', 'description' => 'Quản lý chi nhánh'],
            ['name' => 'Nhân viên bán hàng', 'description' => 'Nhân viên bán hàng'],
            ['name' => 'Nhân viên kho', 'description' => 'Nhân viên quản lý kho'],
            ['name' => 'Kế toán', 'description' => 'Nhân viên kế toán'],
            ['name' => 'Nhân viên IT', 'description' => 'Nhân viên công nghệ thông tin'],
            ['name' => 'Nhân viên marketing', 'description' => 'Nhân viên marketing'],
            ['name' => 'Nhân viên chăm sóc khách hàng', 'description' => 'Nhân viên CSKH'],
            ['name' => 'Thực tập sinh', 'description' => 'Thực tập sinh'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        $this->command->info('Đã tạo dữ liệu mẫu cho roles!');
        $this->command->info('- Số vai trò: ' . Role::count());
    }
}
