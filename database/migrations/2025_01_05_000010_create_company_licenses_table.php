<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Module 7 — GCSM's own regulatory licences (DOS MLA-085, ISO, etc.).
        Schema::create('company_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('license_no')->nullable();
            $table->string('issuing_authority')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['valid', 'expiring', 'expired', 'na'])->default('valid');
            $table->string('scan_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('expiry_date');
        });
    }
    public function down(): void { Schema::dropIfExists('company_licenses'); }
};
