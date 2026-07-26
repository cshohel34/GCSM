<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // One rank/seat (optionally on a specific vessel) within a requisition.
        Schema::create('requisition_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->foreignId('principal_vessel_id')->nullable()->constrained('principal_vessels')->nullOnDelete();
            $table->unsignedInteger('headcount')->default(1);
            $table->enum('status', ['open', 'filled', 'unfulfilled'])->default('open');
            $table->string('unfulfilled_reason')->nullable(); // why it couldn't be filled (TM-12)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('requisition_positions'); }
};
