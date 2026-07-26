<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for availability / job-urgency changes on a crew profile.
 * Records who changed it, when, why (reason) and from where (context).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('crew_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('context')->default('manual'); // placement_history | sign_off | personal_details
            $table->string('old_availability')->nullable();
            $table->string('new_availability')->nullable();
            $table->string('old_urgency')->nullable();
            $table->string('new_urgency')->nullable();
            $table->date('old_deadline')->nullable();
            $table->date('new_deadline')->nullable();
            $table->date('old_available_from')->nullable();
            $table->date('new_available_from')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index(['crew_profile_id', 'created_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('crew_status_logs'); }
};
