<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crew_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_profile_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('routing_number')->nullable();
            $table->boolean('is_own_account')->default(true);
            // Third-party account (CM-18)
            $table->string('owner_relationship')->nullable();
            $table->string('owner_nid')->nullable();
            $table->string('declaration_path')->nullable(); // written consent scan
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('crew_bank_accounts'); }
};
