<?php

namespace App\Console\Commands;

use App\Services\CrewSyncService;
use App\Services\OmaApiClient;
use Illuminate\Console\Command;

class OmaSyncNew extends Command
{
    protected $signature = 'oma:sync-new {date? : dd-mm-yyyy (defaults to today)}';
    protected $description = 'Create Crew Profiles for OMA students admitted on a date (INT-01).';

    public function handle(OmaApiClient $api, CrewSyncService $sync): int
    {
        $date = $this->argument('date') ?: now()->format('d-m-Y');
        $this->info("Fetching new OMA students for {$date} ...");
        $students = $api->newStudents($date);
        $n = $sync->syncMany($students, 'new-students', $date);
        $this->info("Synced {$n} crew profile(s).");
        return self::SUCCESS;
    }
}
