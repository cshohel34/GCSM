<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Module 6 — partner earnings, only for placements the partner arranged (TM-04).
        Schema::create('partner_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('placement_id')->nullable()->constrained('placements')->nullOnDelete();
            $table->enum('basis', ['service_charge', 'agency_fee', 'net_profit', 'negotiated'])->default('service_charge');
            $table->decimal('base_amount', 12, 2)->nullable();   // the amount the % applies to
            $table->decimal('percent', 5, 2)->nullable();
            $table->decimal('amount', 12, 2)->default(0);        // payout amount
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['partner_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('partner_payouts'); }
};
