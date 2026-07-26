<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // CV template — "Educational Qualification" (S.S.C / H.S.C / Others, multiple rows).
        Schema::create('crew_academics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_profile_id')->constrained()->cascadeOnDelete();
            $table->string('description');   // S.S.C, H.S.C, Diploma, B.Sc, Others…
            $table->string('board')->nullable();
            $table->string('group')->nullable();
            $table->string('passing_year')->nullable();
            $table->string('gpa')->nullable();
            $table->enum('source', ['oma', 'manual'])->default('manual');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('crew_academics'); }
};
