<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $ranks = [
            // Deck
            ['Master', 'Deck'], ['Chief Officer', 'Deck'], ['2nd Officer', 'Deck'],
            ['3rd Officer', 'Deck'], ['Deck Cadet', 'Deck'], ['Bosun', 'Deck'],
            ['Able Seaman (AB)', 'Deck'], ['Ordinary Seaman (OS)', 'Deck'],
            // Engine
            ['Chief Engineer', 'Engine'], ['2nd Engineer', 'Engine'], ['3rd Engineer', 'Engine'],
            ['4th Engineer', 'Engine'], ['Engine Cadet', 'Engine'], ['Electrical Officer (ETO)', 'Engine'],
            ['Fitter', 'Engine'], ['Oiler', 'Engine'], ['Wiper', 'Engine'],
            // Catering
            ['Chief Cook', 'Catering'], ['Steward', 'Catering'], ['Messman', 'Catering'],
            ['Fresh', null],
        ];
        foreach ($ranks as [$name, $dept]) {
            DB::table('ranks')->updateOrInsert(
                ['rank_name' => $name],
                ['department' => $dept, 'active' => true, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
