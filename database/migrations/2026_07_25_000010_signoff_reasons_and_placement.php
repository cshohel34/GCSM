<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('sign_off_reasons')) {
            Schema::create('sign_off_reasons', function (Blueprint $table) {
                $table->id();
                $table->string('label')->unique();
                // A note is required for every reason except a clean voyage completion.
                $table->boolean('note_required')->default(true);
                $table->integer('sort_order')->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        // Client's canonical sign-off reason list.
        $now = now();
        $reasons = [
            ['Voyage Completed Successfully', false],
            ['Forced Sign Off', true],
            ['Family emergency or personal reasons', true],
            ['Physical illness or accident', true],
            ['Leave or vacation', true],
            ['Sale or lay-up of the vessel', true],
            ['Disciplinary issues from the company or crew member', true],
            ['Job change or resignation', true],
            ['Need for training or certificate renewal', true],
            ['Vessel change or reprofiling by the company', true],
        ];
        foreach ($reasons as $i => [$label, $noteReq]) {
            DB::table('sign_off_reasons')->updateOrInsert(
                ['label' => $label],
                ['note_required' => $noteReq, 'sort_order' => $i + 1, 'active' => true, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        Schema::table('placements', function (Blueprint $table) {
            if (! Schema::hasColumn('placements', 'sign_off_reason')) {
                $table->string('sign_off_reason')->nullable()->after('sign_off_date');
            }
            if (! Schema::hasColumn('placements', 'sign_off_note')) {
                $table->text('sign_off_note')->nullable()->after('sign_off_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('placements', function (Blueprint $table) {
            foreach (['sign_off_reason', 'sign_off_note'] as $c) {
                if (Schema::hasColumn('placements', $c)) $table->dropColumn($c);
            }
        });
        Schema::dropIfExists('sign_off_reasons');
    }
};
