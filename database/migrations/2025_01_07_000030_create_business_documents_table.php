<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->nullable();     // agreement, insurance, registration...
            $table->string('number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['valid', 'expiring', 'expired', 'na'])->default('valid');
            $table->string('scan_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('expiry_date');
        });
    }
    public function down(): void { Schema::dropIfExists('business_documents'); }
};
