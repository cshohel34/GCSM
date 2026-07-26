<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Replace the single "Fresh" rank with department-specific Fresh (Deck) / Fresh (Engine). */
return new class extends Migration {
    public function up(): void
    {
        $now = now();
        DB::table('ranks')->updateOrInsert(
            ['rank_name' => 'Fresh (Deck)'],
            ['department' => 'Deck', 'sort_order' => 115, 'active' => true, 'updated_at' => $now]
        );
        DB::table('ranks')->updateOrInsert(
            ['rank_name' => 'Fresh (Engine)'],
            ['department' => 'Engine', 'sort_order' => 217, 'active' => true, 'updated_at' => $now]
        );
        // Retire the old generic "Fresh" entries (kept on any existing records).
        DB::table('ranks')->whereIn('rank_name', ['Fresh', 'Fresh (No rank yet)'])->update(['active' => false]);
    }

    public function down(): void {}
};
