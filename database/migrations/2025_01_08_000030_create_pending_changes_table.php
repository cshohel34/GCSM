<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Edits by staff/partners wait here until Super Admin / Manager approves (goes live).
        Schema::create('pending_changes', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type');           // e.g. App\Models\CrewProfile
            $table->unsignedBigInteger('subject_id');
            $table->string('label')->nullable();       // human summary e.g. crew name
            $table->json('changes');                   // { field: {old, new} }
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
            $table->index('status');
        });
    }
    public function down(): void { Schema::dropIfExists('pending_changes'); }
};
