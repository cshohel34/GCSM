<?php

namespace App\Console\Commands;

use App\Services\CrewSyncService;
use App\Services\OmaApiClient;
use Illuminate\Console\Command;

class OmaSyncUpdates extends Command
{
    protected $signature = 'oma:sync-updates {date? : dd-mm-yyyy (defaults to today)}';
    protected $description = 'Refresh Crew Profiles for OMA students changed on a date (INT-04, one-way).';

    public function handle(OmaApiClient $api, CrewSyncService $sync): int
    {
        $date = $this->argument('date') ?: now()->format('d-m-Y');
        $this->info("Fetching updated OMA students for {$date} ...");
        $students = $api->updatedStudents($date);
        $n = $sync->syncMany($students, 'update-students', $date);
        $this->info("Refreshed {$n} crew profile(s).");
        return self::SUCCESS;
    }
}
