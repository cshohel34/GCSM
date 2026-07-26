<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('placements', function (Blueprint $table) {
            if (! Schema::hasColumn('placements', 'expected_joining_date')) {
                $table->date('expected_joining_date')->nullable()->after('sign_on_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('placements', function (Blueprint $table) {
            if (Schema::hasColumn('placements', 'expected_joining_date')) {
                $table->dropColumn('expected_joining_date');
            }
        });
    }
};
