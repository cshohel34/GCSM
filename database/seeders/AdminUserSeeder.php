<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@goldencareerbd.com'],
            [
                'name' => 'GCSM Super Admin',
                'password' => Hash::make('password'),   // CHANGE after first login
                'user_type' => 'staff',
                'status' => 'active',
                'office' => 'Dhaka',
            ]
        );
        $admin->syncRoles(['Super Admin']);
    }
}
