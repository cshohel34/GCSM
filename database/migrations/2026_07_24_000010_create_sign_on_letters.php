<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('sign_on_letters')) {
            Schema::create('sign_on_letters', function (Blueprint $table) {
                $table->id();
                $table->string('reference_no')->unique();      // GCSM/Crew/SignOn/YYYY/NNNN — immutable
                $table->unsignedInteger('letter_no');           // sequential within the year
                $table->unsignedInteger('letter_year');
                $table->date('letter_date');                    // issue date — immutable

                $table->foreignId('candidate_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('crew_profile_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('principal_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('principal_vessel_id')->nullable()->constrained('principal_vessels')->nullOnDelete();

                // Snapshot fields so the register is searchable & stable on its own.
                $table->string('crew_name')->nullable();
                $table->string('cdc_no')->nullable();
                $table->string('passport_no')->nullable();
                $table->string('mobile')->nullable();
                $table->string('rank')->nullable();
                $table->string('vessel_name')->nullable();
                $table->string('company_name')->nullable();
                $table->date('joining_date')->nullable();
                $table->string('salary')->nullable();
                $table->string('place_of_joining')->nullable();
                $table->date('passport_issue')->nullable();

                $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index('letter_date');
                $table->index('crew_profile_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sign_on_letters');
    }
};
