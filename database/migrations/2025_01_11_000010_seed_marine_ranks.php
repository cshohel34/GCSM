<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Seed the full set of merchant-navy ranks so they appear in every dropdown. */
return new class extends Migration {
    public function up(): void
    {
        $now = now();
        $ranks = [
            // ---- Deck department ----
            ['Master (Captain)', 'Deck'],
            ['Chief Officer (Chief Mate)', 'Deck'],
            ['Second Officer (2nd Mate)', 'Deck'],
            ['Third Officer (3rd Mate)', 'Deck'],
            ['Junior / Additional Officer', 'Deck'],
            ['Deck Cadet', 'Deck'],
            ['Bosun (Boatswain)', 'Deck'],
            ['Able Seaman (AB)', 'Deck'],
            ['Ordinary Seaman (OS)', 'Deck'],
            ['Trainee OS', 'Deck'],
            ['Deck Fitter', 'Deck'],
            ['Pumpman', 'Deck'],
            ['Crane Operator', 'Deck'],
            ['Rigger', 'Deck'],
            // ---- Engine department ----
            ['Chief Engineer', 'Engine'],
            ['Second Engineer (2nd Engineer)', 'Engine'],
            ['Third Engineer (3rd Engineer)', 'Engine'],
            ['Fourth Engineer (4th Engineer)', 'Engine'],
            ['Fifth / Junior Engineer', 'Engine'],
            ['Engine Cadet', 'Engine'],
            ['Electro-Technical Officer (ETO)', 'Engine'],
            ['Electrical Engineer', 'Engine'],
            ['Gas Engineer', 'Engine'],
            ['Reefer Engineer', 'Engine'],
            ['Fitter', 'Engine'],
            ['Motorman', 'Engine'],
            ['Oiler', 'Engine'],
            ['Wiper', 'Engine'],
            ['Trainee Wiper', 'Engine'],
            ['Turner / Welder', 'Engine'],
            // ---- Radio / Safety ----
            ['Radio Officer (GMDSS)', 'Radio'],
            ['Safety Officer', 'Radio'],
            // ---- Catering / Galley ----
            ['Chief Cook', 'Catering'],
            ['Second Cook', 'Catering'],
            ['Chief Steward', 'Catering'],
            ['Steward', 'Catering'],
            ['Messman', 'Catering'],
            ['Trainee Messman', 'Catering'],
            // ---- Other ----
            ['Purser', 'Other'],
            ['Fresh (No rank yet)', 'Other'],
        ];
        foreach ($ranks as [$name, $dept]) {
            DB::table('ranks')->updateOrInsert(
                ['rank_name' => $name],
                ['department' => $dept, 'active' => true, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        // Keep ranks on rollback (data migration).
    }
};
