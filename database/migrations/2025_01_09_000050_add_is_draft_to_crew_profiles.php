<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crew_profiles', function (Blueprint $table) {
            // A profile stays a Draft (saved, never lost) until every CV field is
            // filled; it auto-flips to Complete at 100%.
            $table->boolean('is_draft')->default(true)->after('job_urgency');
        });
    }

    public function down(): void
    {
        Schema::table('crew_profiles', function (Blueprint $table) {
            $table->dropColumn('is_draft');
        });
    }
};
