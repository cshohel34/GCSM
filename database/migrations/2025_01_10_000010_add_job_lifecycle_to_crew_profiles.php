<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crew_profiles', function (Blueprint $table) {
            // Placement deadline for High/Urgent crew, and the date a resting crew
            // becomes available again after sign-off.
            $table->date('job_deadline')->nullable()->after('job_urgency');
            $table->date('available_from')->nullable()->after('job_deadline');
        });

        // Widen availability so it can also hold 'onboard' and 'resting'.
        DB::statement("ALTER TABLE crew_profiles MODIFY availability VARCHAR(20) NOT NULL DEFAULT 'not_available'");
    }

    public function down(): void
    {
        Schema::table('crew_profiles', function (Blueprint $table) {
            $table->dropColumn(['job_deadline', 'available_from']);
        });
        DB::statement("ALTER TABLE crew_profiles MODIFY availability VARCHAR(20) NOT NULL DEFAULT 'not_available'");
    }
};
