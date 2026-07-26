<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Identity & travel documents (Appendix A §A.3). Expiry drives the reminder engine.
        Schema::create('crew_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_profile_id')->constrained()->cascadeOnDelete();
            $table->string('doc_type'); // Passport, CDC, COC, GMDSS, SID, NID, Medical, Flag Licence, MCV...
            $table->string('number')->nullable();
            $table->string('grade')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('place_of_issue')->nullable();
            $table->string('issuing_authority')->nullable();
            $table->string('scan_path')->nullable();
            $table->enum('status', ['valid', 'expiring', 'expired', 'na'])->default('valid');
            $table->timestamps();
            $table->index(['doc_type', 'expiry_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('crew_documents'); }
};
