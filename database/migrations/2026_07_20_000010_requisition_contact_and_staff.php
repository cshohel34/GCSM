<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            if (! Schema::hasColumn('requisitions', 'principal_contact_id')) {
                $table->foreignId('principal_contact_id')->nullable()->after('principal_id')
                    ->constrained('principal_contacts')->nullOnDelete();
            }
        });

        // Staff / partners who manage a requirement (two or more allowed).
        if (! Schema::hasTable('requisition_staff')) {
            Schema::create('requisition_staff', function (Blueprint $table) {
                $table->id();
                $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('note')->nullable();
                $table->timestamps();
                $table->unique(['requisition_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('requisition_staff');
        Schema::table('requisitions', function (Blueprint $table) {
            if (Schema::hasColumn('requisitions', 'principal_contact_id')) {
                $table->dropConstrainedForeignId('principal_contact_id');
            }
        });
    }
};
