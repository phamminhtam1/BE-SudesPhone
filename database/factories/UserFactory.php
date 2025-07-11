<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::inRandomOrder()->first()?->branch_id,
            'role_id' => Role::inRandomOrder()->first()?->id,
            'name' => $this->generateVietnameseName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->regexify('0[0-9]{9}'),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'salary' => fake()->randomFloat(2, 5000000, 50000000), // 5M - 50M VND
            'email_verified_at' => null,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => null,
            'status' => fake()->randomElement([0, 1]), // 0: inactive, 1: active
            'manually_disabled' => false,
        ];
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

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Tạo nhân viên đang hoạt động
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
            'manually_disabled' => false,
        ]);
    }

    /**
     * Tạo nhân viên không hoạt động
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    /**
     * Tạo nhân viên bị vô hiệu hóa thủ công
     */
    public function manuallyDisabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'manually_disabled' => true,
        ]);
    }

    /**
     * Tạo giám đốc
     */
    public function director(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('name', 'Giám đốc')->first()?->id,
            'status' => 1,
            'manually_disabled' => false,
        ]);
    }
}
