<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VesselTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $types = [
            'Bulk Carrier','General Cargo','Container','Oil Tanker','Chemical Tanker',
            'LPG Tanker','LNG Tanker','Crude Oil Tanker','Product Tanker','Car Carrier',
            'Reefer','Passenger','Ro-Ro','Offshore/OSV','Fishing Vessel','Tug','Others',
        ];
        foreach ($types as $t) {
            DB::table('vessel_types')->updateOrInsert(
                ['type_name' => $t],
                ['active' => true, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
