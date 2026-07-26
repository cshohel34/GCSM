<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pre-built, editable lists for Maritime Education: marine academies/institutes and
 * education departments. Managed from Settings; used as searchable dropdowns and
 * synced everywhere the list appears.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('marine_academies')) {
            Schema::create('marine_academies', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('category')->nullable();   // Govt. | Private
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('marine_departments')) {
            Schema::create('marine_departments', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('category')->nullable();   // Cadet Course | Rating Course
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        $now = now();

        $academies = [
            'Govt.' => [
                'Bangladesh Marine Academy, Chattogram',
                'Bangladesh Marine Academy, Pabna',
                'Bangladesh Marine Academy, Barishal',
                'Bangladesh Marine Academy, Rangpur',
                'Bangladesh Marine Academy, Sylhet',
                'Marine Fisheries Academy, Chattogram',
                'National Maritime Institute (NMI), Chattogram',
                'National Maritime Institute (NMI), Madaripur',
            ],
            'Private' => [
                'Ocean Maritime Academy (OMA)',
                'International Maritime Academy (IMA)',
                'MAS Maritime Academy (MAS)',
                'Western Maritime Academy (WMA)',
                'Bangladesh Maritime Training Institute (BMTI)',
                'International Maritime Training Academy (IMTA)',
            ],
        ];
        foreach ($academies as $cat => $names) {
            foreach ($names as $n) {
                DB::table('marine_academies')->updateOrInsert(
                    ['name' => $n],
                    ['category' => $cat, 'active' => true, 'updated_at' => $now, 'created_at' => $now]
                );
            }
        }

        $departments = [
            'Cadet Course' => [
                'Department of Nautical Science',
                'Department of Marine Engineering',
                'Department of Marine Fisheries',
            ],
            'Rating Course' => [
                'Deck', 'Engine', 'FCW', 'Cook', 'Steward', 'Electrician', 'ETO',
            ],
        ];
        foreach ($departments as $cat => $names) {
            foreach ($names as $n) {
                DB::table('marine_departments')->updateOrInsert(
                    ['name' => $n],
                    ['category' => $cat, 'active' => true, 'updated_at' => $now, 'created_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marine_academies');
        Schema::dropIfExists('marine_departments');
    }
};
