<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Chart of Accounts — 5 classes, tree (group vs postable leaf).
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'income', 'expense']);
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->boolean('is_group')->default(false);       // group headers are not postable
            $table->boolean('is_cash_bank')->default(false);   // for cash/bank book
            $table->enum('currency', ['BDT', 'USD'])->default('BDT');
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->enum('opening_side', ['debit', 'credit'])->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index('type');
        });
    }
    public function down(): void { Schema::dropIfExists('accounts'); }
};
