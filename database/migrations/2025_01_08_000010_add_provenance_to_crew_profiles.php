<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crew_profiles', function (Blueprint $table) {
            // OMA students keep admission_id; manual/website crew have none -> nullable.
            $table->string('admission_id')->nullable()->change();
            $table->string('gc_id')->nullable()->unique()->after('admission_id');
            $table->string('father_name')->nullable()->after('name_chinese');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('sid_no')->nullable()->after('nid_no');
            $table->string('birth_registration_no')->nullable()->after('sid_no');
        });
    }

    public function down(): void
    {
        Schema::table('crew_profiles', function (Blueprint $table) {
            $table->dropColumn(['gc_id', 'father_name', 'mother_name', 'sid_no', 'birth_registration_no']);
        });
    }
};
