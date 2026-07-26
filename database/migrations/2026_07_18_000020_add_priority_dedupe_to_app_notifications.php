<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Priority (high for document/certificate expiry) and a per-day dedupe key so the
 * daily expiry reminders keep coming — one per item per day — until renewed.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('app_notifications', 'priority')) {
                $table->string('priority')->default('normal')->after('type'); // normal | high
            }
            if (! Schema::hasColumn('app_notifications', 'dedupe_key')) {
                $table->string('dedupe_key')->nullable()->after('link')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropColumn(['priority', 'dedupe_key']);
        });
    }
};
