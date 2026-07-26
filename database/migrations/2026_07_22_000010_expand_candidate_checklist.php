<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('candidate_checklist_items', function (Blueprint $table) {
            if (! Schema::hasColumn('candidate_checklist_items', 'code')) {
                $table->string('code')->nullable()->after('candidate_id');
            }
            if (! Schema::hasColumn('candidate_checklist_items', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('item');
            }
            if (! Schema::hasColumn('candidate_checklist_items', 'auto_source')) {
                $table->string('auto_source')->nullable()->after('is_received');
            }
            if (! Schema::hasColumn('candidate_checklist_items', 'required')) {
                $table->boolean('required')->default(true)->after('auto_source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidate_checklist_items', function (Blueprint $table) {
            foreach (['code', 'sort_order', 'auto_source', 'required'] as $col) {
                if (Schema::hasColumn('candidate_checklist_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
