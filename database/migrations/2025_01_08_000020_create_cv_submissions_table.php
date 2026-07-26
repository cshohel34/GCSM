<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Website (goldencareerbd.com/career) CV submissions -> reviewed -> approved to a crew profile.
        Schema::create('cv_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('rank_text')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('cdc_no')->nullable();
            $table->string('passport_no')->nullable();
            $table->string('sid_no')->nullable();
            $table->string('coc_no')->nullable();
            $table->string('nid_no')->nullable();
            $table->string('birth_registration_no')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('cv_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('source')->default('website');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('crew_profile_id')->nullable()->constrained('crew_profiles')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }
    public function down(): void { Schema::dropIfExists('cv_submissions'); }
};
