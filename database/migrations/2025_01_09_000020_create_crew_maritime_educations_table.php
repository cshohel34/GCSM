<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // CV template — "Maritime Education Details" (multiple rows allowed).
        Schema::create('crew_maritime_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_profile_id')->constrained()->cascadeOnDelete();
            $table->string('institute');
            $table->string('department')->nullable();
            $table->string('year_of_graduation')->nullable();
            $table->enum('source', ['oma', 'manual'])->default('manual');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('crew_maritime_educations'); }
};
