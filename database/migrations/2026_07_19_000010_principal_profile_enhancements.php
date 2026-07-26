<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('principals', function (Blueprint $table) {
            if (! Schema::hasColumn('principals', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('type');
            }
        });

        Schema::table('principal_contacts', function (Blueprint $table) {
            foreach ([
                'photo_path', 'wechat_id', 'whatsapp', 'linkedin', 'facebook',
            ] as $col) {
                if (! Schema::hasColumn('principal_contacts', $col)) {
                    $table->string($col)->nullable();
                }
            }
            if (! Schema::hasColumn('principal_contacts', 'office_address')) {
                $table->text('office_address')->nullable();
            }
        });

        Schema::table('principal_vessels', function (Blueprint $table) {
            foreach (['engine_type', 'bhp', 'trading_area'] as $col) {
                if (! Schema::hasColumn('principal_vessels', $col)) {
                    $table->string($col)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('principals', function (Blueprint $table) {
            if (Schema::hasColumn('principals', 'logo_path')) {
                $table->dropColumn('logo_path');
            }
        });
        Schema::table('principal_contacts', function (Blueprint $table) {
            foreach (['photo_path', 'wechat_id', 'whatsapp', 'linkedin', 'facebook', 'office_address'] as $col) {
                if (Schema::hasColumn('principal_contacts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('principal_vessels', function (Blueprint $table) {
            foreach (['engine_type', 'bhp', 'trading_area'] as $col) {
                if (Schema::hasColumn('principal_vessels', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
