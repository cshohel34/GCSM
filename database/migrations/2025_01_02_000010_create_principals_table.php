<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('principals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['principal', 'management'])->default('principal');
            $table->string('country')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            // Current managing staff (history kept in principal_staff_assignments).
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('inactive'); // active needs a contract
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('name');
        });
    }
    public function down(): void { Schema::dropIfExists('principals'); }
};
