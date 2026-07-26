<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crew_sea_services', function (Blueprint $table) {
            if (! Schema::hasColumn('crew_sea_services', 'dwt')) {
                $table->string('dwt')->nullable()->after('grt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('crew_sea_services', function (Blueprint $table) {
            if (Schema::hasColumn('crew_sea_services', 'dwt')) {
                $table->dropColumn('dwt');
            }
        });
    }
};
