<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salary_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_line_id')->nullable()->constrained('salary_lines')->nullOnDelete();
            $table->string('month')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['held', 'released'])->default('held');
            $table->foreignId('held_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('salary_holds'); }
};
