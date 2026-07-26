<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('principals', function (Blueprint $table) {
            if (! Schema::hasColumn('principals', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
        });

        if (! Schema::hasTable('principal_notes')) {
            Schema::create('principal_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('principal_id')->constrained()->cascadeOnDelete();
                $table->text('note');
                $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('principal_offences')) {
            Schema::create('principal_offences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('principal_id')->constrained()->cascadeOnDelete();
                $table->date('offence_date')->nullable();
                $table->text('description');
                $table->string('source')->nullable();
                $table->string('action_taken')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('principal_offences');
        Schema::dropIfExists('principal_notes');
        Schema::table('principals', function (Blueprint $table) {
            if (Schema::hasColumn('principals', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};
