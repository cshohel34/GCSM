<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Give ranks a seniority sort order (grouped by department, senior → junior)
 * and deactivate any older duplicate variants so dropdowns stay clean.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ranks', 'sort_order')) {
            Schema::table('ranks', function (Blueprint $table) {
                $table->integer('sort_order')->default(999)->after('department');
            });
        }

        // department => senior-to-junior list. sort_order = deptIndex*100 + position.
        $byDept = [
            'Deck' => ['Master','Chief Officer','2nd Officer','3rd Officer','Junior Officer','Deck Cadet',
                       'Bosun','Able Seaman (AB)','Ordinary Seaman (OS)','Trainee OS','Pumpman','Deck Fitter','Crane Operator','Rigger','Fresh (Deck)'],
            'Engine' => ['Chief Engineer','2nd Engineer','3rd Engineer','4th Engineer','Junior Engineer','Engine Cadet',
                         'Electro-Technical Officer (ETO)','Electrical Engineer','Gas Engineer','Reefer Engineer',
                         'Fitter','Motorman','Oiler','Wiper','Trainee Wiper','Welder','Fresh (Engine)'],
            'Radio' => ['Radio Officer (GMDSS)','Safety Officer'],
            'Catering' => ['Chief Cook','2nd Cook','Chief Steward','Steward','Messman','Trainee Messman'],
            'Other' => ['Purser'],
        ];

        $now = now();
        $canonical = [];
        $deptIndex = 1;
        foreach ($byDept as $dept => $names) {
            $pos = 1;
            foreach ($names as $name) {
                $canonical[] = $name;
                DB::table('ranks')->updateOrInsert(
                    ['rank_name' => $name],
                    ['department' => $dept, 'sort_order' => $deptIndex * 100 + $pos, 'active' => true, 'updated_at' => $now]
                );
                $pos++;
            }
            $deptIndex++;
        }

        // Hide older duplicate/legacy variants (keeps them on existing records).
        DB::table('ranks')->whereNotIn('rank_name', $canonical)->update(['active' => false]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('ranks', 'sort_order')) {
            Schema::table('ranks', function (Blueprint $table) { $table->dropColumn('sort_order'); });
        }
    }
};
