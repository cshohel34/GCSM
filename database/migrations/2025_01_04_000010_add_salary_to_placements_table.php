<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Contract figures entered once per crew (SM-02); carried into each monthly sheet.
        Schema::table('placements', function (Blueprint $table) {
            $table->decimal('monthly_salary_usd', 12, 2)->nullable()->after('service_charge');
            $table->decimal('agency_fee_usd', 12, 2)->nullable()->after('monthly_salary_usd');
        });
    }
    public function down(): void
    {
        Schema::table('placements', function (Blueprint $table) {
            $table->dropColumn(['monthly_salary_usd', 'agency_fee_usd']);
        });
    }
};
