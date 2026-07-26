<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crew_profiles', function (Blueprint $table) {
            // Next of kin (from the GCSM CV template "Personal Details").
            $table->string('next_of_kin_name')->nullable()->after('emergency_contact');
            $table->string('next_of_kin_relation')->nullable()->after('next_of_kin_name');
            $table->string('next_of_kin_contact')->nullable()->after('next_of_kin_relation');
            $table->text('next_of_kin_address')->nullable()->after('next_of_kin_contact');
        });
    }

    public function down(): void
    {
        Schema::table('crew_profiles', function (Blueprint $table) {
            $table->dropColumn(['next_of_kin_name', 'next_of_kin_relation', 'next_of_kin_contact', 'next_of_kin_address']);
        });
    }
};
