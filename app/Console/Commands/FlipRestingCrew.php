<?php

namespace App\Console\Commands;

use App\Models\CrewProfile;
use Illuminate\Console\Command;

/** When a resting crew's available_from date arrives, flip them to Available. */
class FlipRestingCrew extends Command
{
    protected $signature = 'crew:lifecycle-flip {--dry-run}';
    protected $description = 'Move resting crew to Available once their available_from date has arrived.';

    public function handle(): int
    {
        $n = 0;
        CrewProfile::where('availability', 'resting')
            ->whereNotNull('available_from')
            ->whereDate('available_from', '<=', now()->toDateString())
            ->chunkById(200, function ($rows) use (&$n) {
                foreach ($rows as $crew) {
                    if (! $this->option('dry-run')) {
                        $crew->update(['availability' => 'available', 'available_from' => null]);
                    }
                    $n++;
                }
            });

        $this->info(($this->option('dry-run') ? '[dry-run] ' : '')."Resting crew moved to Available: {$n}");
        return self::SUCCESS;
    }
}
