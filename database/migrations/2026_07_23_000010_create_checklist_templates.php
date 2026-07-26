<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('checklist_templates')) {
            Schema::create('checklist_templates', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();          // stable identifier
                $table->string('label');                    // shown on screen & PDF
                $table->string('match_rule')->nullable();   // auto-map rule (null = manual)
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        // Seed the standard GCSM "Crew On Board" checklist.
        $now = now();
        $standard = [
            ['cv', 'CV', 'cv'],
            ['photo', 'Photo', 'photo'],
            ['cdc', 'CDC', 'cdc'],
            ['passport', 'Passport', 'passport'],
            ['coc', 'COC (if any)', 'coc'],
            ['short_course', 'Short Course Certificate', 'short_course'],
            ['port_health', 'Port Health Medical', 'port_health'],
            ['sid', 'SID', 'sid'],
            ['appointment', 'Appoint Letter / Contract Letter', null],
            ['sign_on_letter', 'Sign on Letter', null],
            ['emigration', 'Emigration Letter / Agency Letter', null],
            ['next_of_kin', 'Next of Kin Details', 'next_of_kin'],
            ['sign_off_letter', 'Sign Off Letter', null],
            ['ok_to_board', 'Ok to Board', null],
            ['visa', 'Visa', 'visa'],
            ['yellow_fever', 'Yellow Fever / Cholera', 'yellow_fever'],
            ['lg', 'LG / Letter of Guarantee', null],
        ];
        foreach ($standard as $i => [$code, $label, $rule]) {
            DB::table('checklist_templates')->updateOrInsert(
                ['code' => $code],
                ['label' => $label, 'match_rule' => $rule, 'sort_order' => $i + 1, 'active' => true, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_templates');
    }
};
