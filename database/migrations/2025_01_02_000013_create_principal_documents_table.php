<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Contract (mandatory to activate) + other company documents (PM-02).
        Schema::create('principal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('principal_id')->constrained()->cascadeOnDelete();
            $table->enum('doc_type', ['contract', 'other'])->default('other');
            $table->string('title');
            $table->string('file_path');
            $table->date('signed_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('principal_documents'); }
};
