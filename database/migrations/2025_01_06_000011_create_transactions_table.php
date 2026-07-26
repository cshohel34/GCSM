<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Voucher header (a balanced journal entry). Dr total = Cr total.
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->enum('voucher_type', ['receipt', 'payment', 'journal', 'contra'])->default('journal');
            $table->date('date');
            $table->string('reference')->nullable();
            $table->text('narration')->nullable();
            $table->enum('status', ['posted', 'void'])->default('posted');
            // Optional link back to a source document (salary sheet, payout, placement...)
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index('date');
            $table->index(['source_type', 'source_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('transactions'); }
};
