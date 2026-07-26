<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        $now = now();
        foreach (['Managing Director','General Manager','Manager','Assistant Manager','Crewing Officer',
                  'Crewing Assistant','Operations Officer','Accountant','Accounts Officer','Documentation Officer',
                  'Receptionist','Office Assistant','IT Officer'] as $d) {
            DB::table('designations')->updateOrInsert(['name' => $d], ['active' => true, 'updated_at' => $now, 'created_at' => $now]);
        }
    }

    public function down(): void { Schema::dropIfExists('designations'); }
};
