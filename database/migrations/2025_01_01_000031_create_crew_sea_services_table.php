<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crew_sea_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_profile_id')->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable()->index();
            $table->string('vessel_name')->nullable()->index();
            $table->string('vessel_type')->nullable();
            $table->string('grt')->nullable();
            $table->string('engine_type')->nullable();
            $table->string('bhp')->nullable();
            $table->string('flag')->nullable();
            $table->string('trading_area')->nullable();
            $table->string('rank')->nullable();
            $table->string('owner')->nullable();
            $table->date('sign_on')->nullable();
            $table->date('sign_off')->nullable();
            $table->integer('duration_days')->nullable();
            $table->string('reason_sign_off')->nullable();
            $table->enum('source', ['oma', 'manual', 'placement'])->default('manual');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('crew_sea_services'); }
};
