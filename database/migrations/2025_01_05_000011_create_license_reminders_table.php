<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('license_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_license_id')->constrained()->cascadeOnDelete();
            $table->date('expiry_date');
            $table->date('sent_for_date');
            $table->json('channels')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['company_license_id', 'sent_for_date'], 'uniq_license_reminder');
        });
    }
    public function down(): void { Schema::dropIfExists('license_reminders'); }
};
