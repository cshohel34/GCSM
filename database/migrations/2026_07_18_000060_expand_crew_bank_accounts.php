<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extra bank-account fields: SWIFT, mobile, cheque-book scan, and — for third-party
 * accounts — the owner's NID scan and passport-size photo.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('crew_bank_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('crew_bank_accounts', 'swift_code')) {
                $table->string('swift_code')->nullable()->after('routing_number');
            }
            if (! Schema::hasColumn('crew_bank_accounts', 'mobile_number')) {
                $table->string('mobile_number')->nullable()->after('swift_code');
            }
            if (! Schema::hasColumn('crew_bank_accounts', 'cheque_scan_path')) {
                $table->string('cheque_scan_path')->nullable()->after('mobile_number');
            }
            if (! Schema::hasColumn('crew_bank_accounts', 'owner_nid_scan_path')) {
                $table->string('owner_nid_scan_path')->nullable()->after('owner_nid');
            }
            if (! Schema::hasColumn('crew_bank_accounts', 'owner_photo_path')) {
                $table->string('owner_photo_path')->nullable()->after('owner_nid_scan_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('crew_bank_accounts', function (Blueprint $table) {
            foreach (['swift_code', 'mobile_number', 'cheque_scan_path', 'owner_nid_scan_path', 'owner_photo_path'] as $c) {
                if (Schema::hasColumn('crew_bank_accounts', $c)) $table->dropColumn($c);
            }
        });
    }
};
