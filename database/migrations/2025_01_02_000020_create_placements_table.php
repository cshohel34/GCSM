<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Shared entity: a crew placed at a principal/vessel. Module 3 reads it
        // (crew per company/vessel); Module 2 (Crew Selection) will create it.
        Schema::create('placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('principal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('principal_vessel_id')->nullable()->constrained('principal_vessels')->nullOnDelete();
            $table->string('rank')->nullable();
            $table->date('sign_on_date')->nullable();
            $table->date('sign_off_date')->nullable();
            $table->enum('status', ['onboard', 'signed_off'])->default('onboard');
            $table->foreignId('arranged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('service_charge', 12, 2)->nullable();
            $table->boolean('has_dues')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['principal_id', 'status']);
            $table->index('crew_profile_id');
        });
    }
    public function down(): void { Schema::dropIfExists('placements'); }
};
