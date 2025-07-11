<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Chạy các seeder theo thứ tự
        $this->call([
            SampleDataSeeder::class,  // Tạo branches và roles trước
            EmployeeSeeder::class,    // Sau đó tạo nhân viên
        ]);
    }
}
