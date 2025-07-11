<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Chạy SampleDataSeeder trước để tạo chức vụ
        $this->call(SampleDataSeeder::class);

        // Kiểm tra xem có branch và role nào không
        $branchCount = Branch::count();
        $roleCount = Role::count();

        if ($branchCount === 0) {
            $this->command->error('Không có chi nhánh nào trong database. Vui lòng tạo chi nhánh trước.');
            return;
        }

        if ($roleCount === 0) {
            $this->command->error('Không có vai trò nào trong database. Vui lòng tạo vai trò trước.');
            return;
        }

        $this->command->info('Bắt đầu tạo 100 nhân viên...');

        // Tạo 1 giám đốc trước
        $directorRole = Role::where('name', 'Giám đốc')->first();
        if ($directorRole) {
            // Kiểm tra xem đã có giám đốc chưa
            $existingDirector = User::where('role_id', $directorRole->id)->first();

            if (!$existingDirector) {
                User::create([
                    'branch_id' => Branch::inRandomOrder()->first()->branch_id,
                    'role_id' => $directorRole->id,
                    'name' => $this->generateVietnameseName(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone' => fake()->regexify('0[0-9]{9}'),
                    'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
                    'salary' => fake()->randomFloat(2, 80000000, 150000000), // 80M - 150M VND cho giám đốc
                    'email_verified_at' => null,
                    'password' => bcrypt('password'),
                    'remember_token' => null,
                    'status' => 1,
                    'manually_disabled' => false,
                ]);
                $this->command->info('Đã tạo 1 giám đốc.');
            } else {
                $this->command->info('Đã có giám đốc trong hệ thống.');
            }
        }

        // Tạo 99 nhân viên còn lại (không phải giám đốc)
        $employees = [];
        $otherRoles = Role::where('name', '!=', 'Giám đốc')->get();

        if ($otherRoles->isEmpty()) {
            $this->command->error('Không có vai trò nào khác ngoài giám đốc.');
            return;
        }

        for ($i = 0; $i < 99; $i++) {
            $employees[] = [
                'branch_id' => Branch::inRandomOrder()->first()->branch_id,
                'role_id' => $otherRoles->random()->id,
                'name' => $this->generateVietnameseName(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->regexify('0[0-9]{9}'),
                'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
                'salary' => fake()->randomFloat(2, 5000000, 50000000), // 5M - 50M VND
                'email_verified_at' => null,
                'password' => bcrypt('password'),
                'remember_token' => null,
                'status' => fake()->randomElement([0, 1]), // 0: inactive, 1: active
                'manually_disabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Hiển thị tiến trình mỗi 20 nhân viên
            if (($i + 1) % 20 === 0) {
                $this->command->info("Đã tạo " . ($i + 1) . " nhân viên...");
            }
        }

        // Chèn dữ liệu theo batch để tối ưu hiệu suất
        User::insert($employees);

        $this->command->info('Hoàn thành tạo 100 nhân viên!');

        // Thống kê
        $totalCount = User::count();
        $activeCount = User::where('status', 1)->count();
        $inactiveCount = User::where('status', 0)->count();
        $disabledCount = User::where('manually_disabled', true)->count();
        $directorCount = User::where('role_id', $directorRole?->id)->count();

        $this->command->info("Thống kê:");
        $this->command->info("- Tổng số nhân viên: " . $totalCount);
        $this->command->info("- Số giám đốc: " . $directorCount);
        $this->command->info("- Nhân viên đang hoạt động: " . $activeCount);
        $this->command->info("- Nhân viên không hoạt động: " . $inactiveCount);
        $this->command->info("- Nhân viên bị vô hiệu hóa: " . $disabledCount);
    }

    /**
     * Tạo tên Việt Nam
     */
    private function generateVietnameseName(): string
    {
        $ho = [
            'Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng',
            'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý', 'Đinh', 'Tô', 'Trịnh', 'Đoàn',
            'Lưu', 'Tăng', 'Hà', 'Tạ', 'Châu', 'Tống', 'Thái', 'Hứa', 'Phùng', 'Quách',
            'Lâm', 'Từ', 'Vương', 'Hồng', 'Tô', 'Đoàn', 'Lưu', 'Tăng', 'Hà', 'Tạ'
        ];

        $tenDem = [
            'Văn', 'Thị', 'Hoàng', 'Minh', 'Thành', 'Công', 'Đức', 'Hữu', 'Quang', 'Anh',
            'Tuấn', 'Hùng', 'Dũng', 'Nam', 'Phương', 'Linh', 'Hương', 'Thảo', 'Nga', 'Mai',
            'Lan', 'Hoa', 'Trang', 'Huyền', 'Ngọc', 'Bích', 'Tuyết', 'Hạnh', 'Dung', 'Nhung',
            'Thủy', 'Hà', 'Thu', 'Đông', 'Xuân', 'Hạ', 'Thu', 'Đông', 'Tân', 'Cường'
        ];

        $ten = [
            'An', 'Bình', 'Cường', 'Dũng', 'Em', 'Phương', 'Giang', 'Hùng', 'Ivan', 'Khang',
            'Linh', 'Minh', 'Nam', 'Oanh', 'Phương', 'Quân', 'Rồng', 'Sơn', 'Tâm', 'Uyên',
            'Vân', 'Xuân', 'Yến', 'Zin', 'Anh', 'Bảo', 'Cẩm', 'Dương', 'Eva', 'Fiona',
            'Giang', 'Hoa', 'Iris', 'Jade', 'Khanh', 'Linh', 'Mai', 'Nga', 'Oanh', 'Phượng'
        ];

        return $ho[array_rand($ho)] . ' ' . $tenDem[array_rand($tenDem)] . ' ' . $ten[array_rand($ten)];
    }
}
