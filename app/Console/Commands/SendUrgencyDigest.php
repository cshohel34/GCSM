<?php

namespace App\Console\Commands;

use App\Models\CrewProfile;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

/** Weekly reminder to Super Admins about High/Urgent crew still awaiting placement. */
class SendUrgencyDigest extends Command
{
    protected $signature = 'crew:urgency-digest {--dry-run}';
    protected $description = 'Weekly placement-deadline digest to Super Admin for High/Urgent crew.';

    public function handle(NotificationService $notifier): int
    {
        $today = now()->startOfDay();

        $crew = CrewProfile::whereIn('job_urgency', ['high', 'urgent'])
            ->whereNotNull('job_deadline')
            ->where('availability', '!=', 'onboard')
            ->orderBy('job_deadline')
            ->get();

        if ($crew->isEmpty()) {
            $this->info('No High/Urgent crew with deadlines.');
            return self::SUCCESS;
        }

        $overdue = $crew->filter(fn ($c) => $c->job_deadline->startOfDay()->lt($today))->count();
        $lines = $crew->take(15)->map(function ($c) use ($today) {
            $days = (int) $today->diffInDays($c->job_deadline->startOfDay(), false);
            $when = $days < 0 ? abs($days).'d overdue' : $days.'d left';
            return "• {$c->name} ({$c->display_id}) — ".strtoupper($c->job_urgency).", {$when} (deadline {$c->job_deadline->toDateString()})";
        })->implode("\n");

        $title = "Placement reminder: {$crew->count()} High/Urgent crew awaiting jobs".($overdue ? " ({$overdue} overdue)" : '');
        $body = $lines."\n\nOpen Crew Management to action these placements.";

        if (! $this->option('dry-run')) {
            $supers = User::whereHas('roles', fn ($r) => $r->where('name', 'Super Admin'))->get();
            if ($supers->isNotEmpty()) {
                $notifier->notify($supers, 'urgency_digest', $title, $body, url('/crew'), ['panel', 'email']);
            }
        }

        $this->info(($this->option('dry-run') ? '[dry-run] ' : '')."Urgency digest: {$crew->count()} crew, {$overdue} overdue");
        return self::SUCCESS;
    }
}
