<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (! Schema::hasColumn('candidates', 'confirmed_at')) {
                $table->date('confirmed_at')->nullable()->after('interview_date');
            }
            if (! Schema::hasColumn('candidates', 'service_charge_decided')) {
                $table->boolean('service_charge_decided')->default(false)->after('service_charge_received');
            }
            if (! Schema::hasColumn('candidates', 'no_charge_reason')) {
                $table->string('no_charge_reason')->nullable()->after('service_charge_decided');
            }
            if (! Schema::hasColumn('candidates', 'service_charge_txn_id')) {
                $table->foreignId('service_charge_txn_id')->nullable()->after('no_charge_reason')
                    ->constrained('transactions')->nullOnDelete();
            }
        });

        // Allow accounting drafts (auto-created from a confirmed service charge).
        DB::statement("ALTER TABLE transactions MODIFY status ENUM('posted','void','draft') NOT NULL DEFAULT 'posted'");
    }

    public function down(): void
    {
        DB::table('transactions')->where('status', 'draft')->update(['status' => 'void']);
        DB::statement("ALTER TABLE transactions MODIFY status ENUM('posted','void') NOT NULL DEFAULT 'posted'");

        Schema::table('candidates', function (Blueprint $table) {
            if (Schema::hasColumn('candidates', 'service_charge_txn_id')) {
                $table->dropConstrainedForeignId('service_charge_txn_id');
            }
            foreach (['confirmed_at', 'service_charge_decided', 'no_charge_reason'] as $col) {
                if (Schema::hasColumn('candidates', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
