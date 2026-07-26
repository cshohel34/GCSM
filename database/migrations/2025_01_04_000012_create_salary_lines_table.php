<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Columns per PRD Appendix B. Inputs are stored; computed columns are
        // recalculated on save and frozen when the sheet is locked.
        Schema::create('salary_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_sheet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crew_profile_id')->nullable()->constrained('crew_profiles')->nullOnDelete();
            $table->foreignId('placement_id')->nullable()->constrained('placements')->nullOnDelete();
            $table->unsignedInteger('sl_no')->default(0);
            $table->string('crew_name');
            $table->string('ship_name')->nullable();
            $table->string('rank')->nullable();
            $table->string('month')->nullable();
            $table->decimal('usd_rate', 10, 4)->default(0);
            // Inputs
            $table->decimal('salary_usd', 12, 2)->default(0);
            $table->decimal('bonus_usd', 12, 2)->default(0);
            $table->date('joining_date')->nullable();
            $table->unsignedInteger('total_days')->default(30);
            $table->unsignedInteger('working_days')->default(30);
            $table->unsignedInteger('deduct_days')->default(0);
            $table->decimal('transfer_charge_usd', 12, 2)->default(0); // conversion charge (crew-borne)
            $table->decimal('agent_fee_usd', 12, 2)->default(0);
            $table->decimal('agent_fee_charge_usd', 12, 2)->default(0);
            $table->text('remarks')->nullable(); // bank A/C name, no, branch, routing
            // Computed (frozen on lock)
            $table->decimal('gross_usd', 12, 2)->default(0);
            $table->decimal('net_usd', 12, 2)->default(0);
            $table->decimal('net_bdt', 14, 2)->default(0);
            $table->decimal('agent_gross_usd', 12, 2)->default(0);
            $table->decimal('agent_net_usd', 12, 2)->default(0);
            $table->decimal('agent_net_bdt', 14, 2)->default(0);
            // Payment / hold state
            $table->boolean('is_paid')->default(false);
            $table->date('paid_date')->nullable();
            $table->boolean('is_held')->default(false);
            $table->timestamps();
            $table->index('crew_profile_id');
        });
    }
    public function down(): void { Schema::dropIfExists('salary_lines'); }
};
