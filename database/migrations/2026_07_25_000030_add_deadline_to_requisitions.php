<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            if (! Schema::hasColumn('requisitions', 'deadline')) {
                $table->date('deadline')->nullable()->after('requirement_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            if (Schema::hasColumn('requisitions', 'deadline')) {
                $table->dropColumn('deadline');
            }
        });
    }
};
