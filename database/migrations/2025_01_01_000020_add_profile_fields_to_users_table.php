<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('user_type', ['staff', 'partner'])->default('staff')->after('phone');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('user_type');
            $table->enum('office', ['Dhaka', 'Chittagong'])->nullable()->after('status');
            $table->date('date_of_joining')->nullable()->after('office');
            $table->date('date_of_resignation')->nullable()->after('date_of_joining');
            // Partner fee-share (per-partner, configurable). Percentages 0-100.
            $table->decimal('share_service_charge_pct', 5, 2)->nullable()->after('date_of_resignation');
            $table->decimal('share_agency_fee_pct', 5, 2)->nullable();
            $table->decimal('share_net_profit_pct', 5, 2)->nullable();
            $table->text('share_notes')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone','user_type','status','office','date_of_joining','date_of_resignation',
                'share_service_charge_pct','share_agency_fee_pct','share_net_profit_pct','share_notes',
            ]);
        });
    }
};
