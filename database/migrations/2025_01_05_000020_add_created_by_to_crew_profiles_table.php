<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Track who created each crew profile (Module 6 productivity — TM-11).
        Schema::table('crew_profiles', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('source')->constrained('users')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('crew_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
