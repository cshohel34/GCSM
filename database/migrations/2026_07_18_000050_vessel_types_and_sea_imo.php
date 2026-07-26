<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replace the vessel-type list with the client's canonical list (older types are
 * deactivated, not deleted, so existing sea-service records keep their value),
 * and add a Ship IMO number to sea-service records.
 */
return new class extends Migration {
    public function up(): void
    {
        // Hide the old list; the client's list below becomes the active set.
        DB::table('vessel_types')->update(['active' => false]);

        $now = now();
        $types = [
            'Container Ships', 'Bulk Carriers', 'Oil Tankers', 'Chemical Tankers',
            'LNG & LPG Carriers', 'Ro-Ro Ships', 'Reefer Ships', 'General Cargo Ships',
            'Cruise Ships', 'Ferries', 'Ocean Liners', 'Tugboats', 'Pilot Boats',
            'Offshore Support Vessels', 'Dredgers', 'Heavy Lift Ships',
            'Research Vessels', 'Survey Ships', 'Fishing Vessel',
        ];
        foreach ($types as $t) {
            DB::table('vessel_types')->updateOrInsert(
                ['type_name' => $t],
                ['active' => true, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        if (! Schema::hasColumn('crew_sea_services', 'imo_no')) {
            Schema::table('crew_sea_services', function (Blueprint $table) {
                $table->string('imo_no')->nullable()->after('vessel_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('crew_sea_services', 'imo_no')) {
            Schema::table('crew_sea_services', function (Blueprint $table) {
                $table->dropColumn('imo_no');
            });
        }
    }
};
