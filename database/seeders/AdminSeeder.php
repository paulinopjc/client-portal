<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate is idempotent: if the user already exists, it does nothing.
        // This means you can run the seeder multiple times without creating duplicates.
        User::firstOrCreate(
            ['email' => 'paulinopjc@gmail.com'],
            [
                'name' => 'Paulino Awino',
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}