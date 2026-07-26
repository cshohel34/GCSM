<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            // Subsidiary/party ledger attribution (debtor/creditor tracking).
            $table->string('party_type')->nullable(); // principal | crew | partner | staff | other
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('memo')->nullable();
            $table->timestamps();
            $table->index('account_id');
            $table->index(['party_type', 'party_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('transaction_lines'); }
};
