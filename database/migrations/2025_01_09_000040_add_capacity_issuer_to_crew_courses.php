<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // CV template — "Certificates & Travelling Documents": Category | Capacity |
        // Certificate Number | Date of Issue | Date of Expiry | Issuer | Issuing Authority.
        Schema::table('crew_courses', function (Blueprint $table) {
            $table->string('category')->nullable()->after('course_name');   // e.g. COC, GMDSS, Passport, STCW course…
            $table->string('capacity')->nullable()->after('category');      // grade / capacity
            $table->string('issuer')->nullable()->after('issuing_authority');
        });
    }

    public function down(): void
    {
        Schema::table('crew_courses', function (Blueprint $table) {
            $table->dropColumn(['category', 'capacity', 'issuer']);
        });
    }
};
