<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('placements', function (Blueprint $table) {
            if (! Schema::hasColumn('placements', 'place_of_joining')) {
                $table->string('place_of_joining')->nullable()->after('sign_on_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('placements', function (Blueprint $table) {
            if (Schema::hasColumn('placements', 'place_of_joining')) {
                $table->dropColumn('place_of_joining');
            }
        });
    }
};
