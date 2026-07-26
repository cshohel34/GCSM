<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('api_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->string('reference')->nullable(); // studentID or date queried
            $table->enum('status', ['success', 'error']);
            $table->integer('records')->default(0);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('api_sync_logs'); }
};
