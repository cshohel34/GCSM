<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            RankSeeder::class,
            VesselTypeSeeder::class,
            CourseCatalogueSeeder::class,
            ChartOfAccountsSeeder::class,
        ]);
    }
}
