<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedEmployeesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:employees {count=100 : Số lượng nhân viên cần tạo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo dữ liệu nhân viên mẫu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');

        // Chạy SampleDataSeeder trước để tạo chức vụ
        $this->call('db:seed', ['--class' => 'SampleDataSeeder']);

        // Kiểm tra xem có branch và role nào không
        $branchCount = Branch::count();
        $roleCount = Role::count();

        if ($branchCount === 0) {
            $this->error('Không có chi nhánh nào trong database. Vui lòng tạo chi nhánh trước.');
            return 1;
        }

        if ($roleCount === 0) {
            $this->error('Không có vai trò nào trong database. Vui lòng tạo vai trò trước.');
            return 1;
        }

        $this->info("Bắt đầu tạo {$count} nhân viên...");

        // Tạo 1 giám đốc trước (nếu chưa có)
        $directorRole = Role::where('name', 'Giám đốc')->first();
        if ($directorRole) {
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
                $this->info('Đã tạo 1 giám đốc.');
            } else {
                $this->info('Đã có giám đốc trong hệ thống.');
            }
        }

        // Tạo progress bar cho nhân viên còn lại
        $remainingCount = $count - 1; // Trừ đi 1 giám đốc
        $progressBar = $this->output->createProgressBar($remainingCount);
        $progressBar->start();

        // Tạo nhân viên theo batch để tối ưu hiệu suất
        $batchSize = 20;
        $employees = [];
        $otherRoles = Role::where('name', '!=', 'Giám đốc')->get();

        if ($otherRoles->isEmpty()) {
            $this->error('Không có vai trò nào khác ngoài giám đốc.');
            return 1;
        }

        for ($i = 0; $i < $remainingCount; $i++) {
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

            // Chèn theo batch
            if (count($employees) >= $batchSize) {
                User::insert($employees);
                $employees = [];
            }

            $progressBar->advance();
        }

        // Chèn phần còn lại
        if (!empty($employees)) {
            User::insert($employees);
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('Hoàn thành tạo nhân viên!');

        // Thống kê
        $totalCount = User::count();
        $activeCount = User::where('status', 1)->count();
        $inactiveCount = User::where('status', 0)->count();
        $disabledCount = User::where('manually_disabled', true)->count();
        $directorCount = User::where('role_id', $directorRole?->id)->count();

        $this->info("Thống kê:");
        $this->info("- Tổng số nhân viên: " . $totalCount);
        $this->info("- Số giám đốc: " . $directorCount);
        $this->info("- Nhân viên đang hoạt động: " . $activeCount);
        $this->info("- Nhân viên không hoạt động: " . $inactiveCount);
        $this->info("- Nhân viên bị vô hiệu hóa: " . $disabledCount);

        return 0;
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
