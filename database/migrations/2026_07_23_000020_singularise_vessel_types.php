<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Earlier seeds inserted plural vessel-type names. Convert them to singular,
     * de-duplicating against any singular already present, and update references
     * stored on sea-service and principal-vessel rows.
     */
    public function up(): void
    {
        $map = [
            'Container Ships'          => 'Container Ship',
            'Bulk Carriers'            => 'Bulk Carrier',
            'Oil Tankers'              => 'Oil Tanker',
            'Chemical Tankers'         => 'Chemical Tanker',
            'LNG & LPG Carriers'       => 'LNG & LPG Carrier',
            'Ro-Ro Ships'              => 'Ro-Ro Ship',
            'Reefer Ships'             => 'Reefer Ship',
            'General Cargo Ships'      => 'General Cargo Ship',
            'Cruise Ships'             => 'Cruise Ship',
            'Ferries'                  => 'Ferry',
            'Ocean Liners'             => 'Ocean Liner',
            'Tugboats'                 => 'Tugboat',
            'Pilot Boats'              => 'Pilot Boat',
            'Offshore Support Vessels' => 'Offshore Support Vessel',
            'Dredgers'                 => 'Dredger',
            'Heavy Lift Ships'         => 'Heavy Lift Ship',
            'Research Vessels'         => 'Research Vessel',
            'Survey Ships'             => 'Survey Ship',
        ];

        foreach ($map as $plural => $singular) {
            $pluralRow = DB::table('vessel_types')->where('type_name', $plural)->first();
            if (! $pluralRow) continue;

            $singularExists = DB::table('vessel_types')->where('type_name', $singular)->exists();
            if ($singularExists) {
                // A singular equivalent is already there — drop the duplicate plural.
                DB::table('vessel_types')->where('id', $pluralRow->id)->delete();
            } else {
                DB::table('vessel_types')->where('id', $pluralRow->id)->update(['type_name' => $singular, 'updated_at' => now()]);
            }

            // Keep any references in sync so dropdowns still match.
            DB::table('crew_sea_services')->where('vessel_type', $plural)->update(['vessel_type' => $singular]);
            DB::table('principal_vessels')->where('vessel_type', $plural)->update(['vessel_type' => $singular]);
        }
    }

    public function down(): void
    {
        // One-way data clean-up; nothing to reverse.
    }
};
