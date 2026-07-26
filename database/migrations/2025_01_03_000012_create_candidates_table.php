<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // A crew in the pipeline for one position. Stage advances through the funnel.
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crew_profile_id')->constrained()->cascadeOnDelete();
            $table->enum('stage', [
                'wishlisted', 'shortlisted', 'forwarded',
                'interview_selected', 'interview_passed', 'interview_failed',
                'final_selected', 'signed_on',
            ])->default('wishlisted');
            $table->timestamp('forwarded_at')->nullable();
            $table->date('interview_date')->nullable();
            $table->text('fail_reason')->nullable();
            $table->decimal('service_charge', 12, 2)->nullable();
            $table->boolean('service_charge_received')->default(false);
            $table->boolean('documents_complete')->default(false);
            $table->foreignId('placement_id')->nullable()->constrained('placements')->nullOnDelete();
            $table->foreignId('arranged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['requisition_position_id', 'crew_profile_id'], 'uniq_position_crew');
        });
    }
    public function down(): void { Schema::dropIfExists('candidates'); }
};
