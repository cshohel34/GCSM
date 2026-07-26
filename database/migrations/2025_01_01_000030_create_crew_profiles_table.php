<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crew_profiles', function (Blueprint $table) {
            $table->id();
            // Identity — Admission ID is the unique key shared with OMA (studentID).
            $table->string('admission_id')->unique();
            $table->enum('source', ['oma', 'manual'])->default('manual');

            // Personal
            $table->string('name');
            $table->string('name_chinese')->nullable();
            $table->string('photo_path')->nullable();
            $table->foreignId('rank_applied_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->foreignId('current_rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('nationality')->default('Bangladeshi');
            $table->string('religion')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->enum('marital_status', ['Single','Married','Widowed','Separated','Divorced','Not specified'])->nullable();
            $table->string('height_cm')->nullable();
            $table->string('weight_kg')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('shoe_size')->nullable();
            $table->string('coverall_size')->nullable();
            $table->string('hair_colour')->nullable();
            $table->string('eye_colour')->nullable();
            $table->string('identification_mark')->nullable();
            $table->text('medical_history')->nullable();

            // Contact & address
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('emergency_contact')->nullable();

            // Core identity numbers (also live as documents, kept here for fast search)
            $table->string('cdc_no')->nullable()->index();
            $table->string('passport_no')->nullable()->index();
            $table->string('coc_no')->nullable()->index();
            $table->string('nid_no')->nullable();
            $table->string('indos_no')->nullable();

            // English proficiency
            $table->string('english_listening')->nullable();
            $table->string('english_speaking')->nullable();
            $table->string('english_reading')->nullable();
            $table->string('english_writing')->nullable();

            // Operational
            $table->enum('availability', ['available', 'not_available'])->default('not_available');
            $table->enum('job_urgency', ['normal', 'high', 'urgent'])->default('normal');
            $table->enum('blacklist_status', ['active', 'blacklisted'])->default('active');
            $table->string('blacklist_reason')->nullable();
            $table->date('blacklist_date')->nullable();

            $table->timestamp('oma_synced_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('name');
            $table->index('current_rank_id');
        });
    }
    public function down(): void { Schema::dropIfExists('crew_profiles'); }
};
