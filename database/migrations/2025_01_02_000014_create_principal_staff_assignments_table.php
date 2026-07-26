<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Full history of who managed a company and why it changed (PM-05).
        Schema::create('principal_staff_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('principal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('unassigned_at')->nullable(); // null = current
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('principal_staff_assignments'); }
};
