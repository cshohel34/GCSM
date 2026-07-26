<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Link expiry notifications to the crew profile they concern, so each profile can
 * show how many reminders were sent and a full "see all" sent-log.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('app_notifications', 'crew_profile_id')) {
                $table->foreignId('crew_profile_id')->nullable()->after('user_id')
                    ->constrained('crew_profiles')->nullOnDelete();
                $table->index('crew_profile_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('crew_profile_id');
        });
    }
};
