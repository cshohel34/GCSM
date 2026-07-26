<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('salary_sheets', function (Blueprint $table) {
            $table->string('company_sheet_path')->nullable()->after('reference');
            $table->foreignId('accounting_txn_id')->nullable()->after('approved_at');
        });
        Schema::table('salary_lines', function (Blueprint $table) {
            $table->decimal('company_amount', 12, 2)->nullable()->after('remarks'); // company-sent USD net
        });
    }
    public function down(): void
    {
        Schema::table('salary_sheets', fn (Blueprint $t) => $t->dropColumn(['company_sheet_path', 'accounting_txn_id']));
        Schema::table('salary_lines', fn (Blueprint $t) => $t->dropColumn('company_amount'));
    }
};
