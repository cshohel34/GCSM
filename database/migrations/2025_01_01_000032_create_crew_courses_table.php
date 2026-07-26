<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Certificates & courses (Appendix A §A.7). OMA-completed -> source=oma (auto via API);
        // other academies -> source=manual.
        Schema::create('crew_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_catalogue_id')->nullable()->constrained('course_catalogue')->nullOnDelete();
            $table->string('course_name', 500);          // denormalised for free-text / non-catalogue
            $table->string('course_code', 60)->nullable();
            $table->date('completion_date')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('issuing_authority')->nullable();
            $table->string('dos_registration_no')->nullable();
            $table->string('certificate_no')->nullable();
            $table->string('certificate_full_format')->nullable();
            $table->string('scan_path')->nullable();
            $table->enum('source', ['oma', 'manual'])->default('manual');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('crew_courses'); }
};
