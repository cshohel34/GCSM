<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candidate_checklist_items', function (Blueprint $table) {
            if (! Schema::hasColumn('candidate_checklist_items', 'evidence_path')) {
                $table->string('evidence_path')->nullable()->after('auto_source');
            }
            if (! Schema::hasColumn('candidate_checklist_items', 'remark_by')) {
                $table->foreignId('remark_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('candidate_checklist_items', 'remark_at')) {
                $table->timestamp('remark_at')->nullable()->after('remark_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidate_checklist_items', function (Blueprint $table) {
            if (Schema::hasColumn('candidate_checklist_items', 'remark_by')) {
                $table->dropConstrainedForeignId('remark_by');
            }
            foreach (['evidence_path', 'remark_at'] as $col) {
                if (Schema::hasColumn('candidate_checklist_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
