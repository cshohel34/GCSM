<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // One row per reminder actually sent, so we can (a) avoid duplicates and
        // (b) show reminder counts per crew profile (CM-19 / NT-02).
        Schema::create('document_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crew_document_id')->nullable()->constrained('crew_documents')->cascadeOnDelete();
            $table->foreignId('crew_course_id')->nullable()->constrained('crew_courses')->cascadeOnDelete();
            $table->string('offset_label');           // e.g. "180d", "90d", "expired"
            $table->date('expiry_date');
            $table->date('sent_for_date');             // the date the reminder represents (dedupe key)
            $table->json('channels')->nullable();      // ["email","whatsapp","panel"]
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['crew_document_id', 'offset_label', 'expiry_date'], 'uniq_doc_reminder');
        });
    }
    public function down(): void { Schema::dropIfExists('document_reminders'); }
};
