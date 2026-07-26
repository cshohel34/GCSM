<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // One monthly sheet per company (optionally per vessel). States:
        // draft -> reconciled -> locked (approved by Super Admin). Locked = immutable.
        Schema::create('salary_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('principal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('principal_vessel_id')->nullable()->constrained('principal_vessels')->nullOnDelete();
            $table->string('month');                 // e.g. "FEB-26"
            $table->decimal('usd_rate', 10, 4)->default(0);
            $table->string('reference')->nullable();
            $table->enum('status', ['draft', 'reconciled', 'locked'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['principal_id', 'month']);
        });
    }
    public function down(): void { Schema::dropIfExists('salary_sheets'); }
};
