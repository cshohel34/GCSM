<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * After a CV is forwarded, the company reviews it and either selects the crew
     * for interview or rejects them. This adds the "rejected_by_company" stage.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE candidates MODIFY stage ENUM(
            'wishlisted','shortlisted','forwarded','rejected_by_company',
            'interview_selected','interview_passed','interview_failed',
            'final_selected','signed_on'
        ) NOT NULL DEFAULT 'wishlisted'");
    }

    public function down(): void
    {
        // Any rows still holding the new stage are moved back to 'forwarded' first.
        DB::table('candidates')->where('stage', 'rejected_by_company')->update(['stage' => 'forwarded']);
        DB::statement("ALTER TABLE candidates MODIFY stage ENUM(
            'wishlisted','shortlisted','forwarded',
            'interview_selected','interview_passed','interview_failed',
            'final_selected','signed_on'
        ) NOT NULL DEFAULT 'wishlisted'");
    }
};
