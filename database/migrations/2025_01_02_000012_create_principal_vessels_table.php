<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('principal_vessels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('principal_id')->constrained()->cascadeOnDelete();
            $table->string('vessel_name');
            $table->string('vessel_type')->nullable();
            $table->string('imo')->nullable();
            $table->string('flag')->nullable();
            $table->string('grt')->nullable();
            $table->string('dwt')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('principal_vessels'); }
};
