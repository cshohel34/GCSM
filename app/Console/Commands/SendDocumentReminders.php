<?php

namespace App\Console\Commands;

use App\Models\CrewCourse;
use App\Models\CrewDocument;
use App\Models\DocumentReminder;
use App\Services\NotificationService;
use Illuminate\Console\Command;

/**
 * Document / certificate expiry reminder engine (§7.1).
 * Confirmed cadence: 180, 90, 30, 15, 7 days before expiry, then DAILY after expiry
 * until the document is updated. Reminders stop once the record is renewed.
 */
class SendDocumentReminders extends Command
{
    protected $signature = 'crew:document-reminders {--dry-run}';
    protected $description = 'Send document/certificate expiry reminders (email + WhatsApp + panel).';

    public function handle(NotificationService $notifier): int
    {
        $offsets = collect(explode(',', (string) env('REMINDER_OFFSETS_DAYS', '180,90,30,15,7')))
            ->map(fn ($d) => (int) trim($d))->filter()->sort()->values()->all();

        $today = now()->startOfDay();
        $sent = 0;

        CrewDocument::with('crewProfile')
            ->whereNotNull('expiry_date')
            ->chunkById(300, function ($docs) use ($offsets, $today, &$sent) {
                foreach ($docs as $doc) {
                    $label = $this->dueLabel($doc->expiry_date->startOfDay(), $today, $offsets);
                    if (! $label) continue;

                    // Dedupe: one reminder per (document, offset-label, expiry). For post-expiry
                    // "expired" reminders we allow one per day via sent_for_date.
                    $exists = DocumentReminder::where('crew_document_id', $doc->id)
                        ->where('offset_label', $label)
                        ->where('expiry_date', $doc->expiry_date->toDateString())
                        ->when($label === 'expired', fn ($q) => $q->whereDate('sent_for_date', $today))
                        ->exists();
                    if ($exists) continue;

                    if (! $this->option('dry-run')) {
                        $crew = $doc->crewProfile;
                        $title = "Document expiring: {$doc->doc_type}";
                        $body = ($crew?->name ?? 'Crew')." — {$doc->doc_type} expires {$doc->expiry_date->toDateString()} ({$label}).";
                        $link = $crew ? url('/crew/'.$crew->id) : null;
                        $notifier->notify($notifier->admins(), 'document_expiry', $title, $body, $link, ['panel','email','whatsapp']);
                        if ($crew) $notifier->notifyContact($crew->email, $crew->mobile, $title, $body, ['email','whatsapp']);
                        DocumentReminder::create([
                            'crew_profile_id' => $doc->crew_profile_id,
                            'crew_document_id' => $doc->id,
                            'offset_label' => $label,
                            'expiry_date' => $doc->expiry_date->toDateString(),
                            'sent_for_date' => $today->toDateString(),
                            'channels' => ['email', 'whatsapp', 'panel'],
                            'sent_at' => now(),
                        ]);
                    }
                    $sent++;
                }
            });

        // Certificates (crew_courses) — same cadence, deduped by crew_course_id.
        CrewCourse::with('crewProfile')
            ->whereNotNull('expiry_date')
            ->chunkById(300, function ($courses) use ($offsets, $today, &$sent) {
                foreach ($courses as $c) {
                    $label = $this->dueLabel($c->expiry_date->startOfDay(), $today, $offsets);
                    if (! $label) continue;

                    $exists = DocumentReminder::where('crew_course_id', $c->id)
                        ->where('offset_label', $label)
                        ->where('expiry_date', $c->expiry_date->toDateString())
                        ->when($label === 'expired', fn ($q) => $q->whereDate('sent_for_date', $today))
                        ->exists();
                    if ($exists) continue;

                    if (! $this->option('dry-run')) {
                        $crew = $c->crewProfile;
                        $name = $c->category ?: $c->course_name;
                        $title = "Certificate expiring: {$name}";
                        $body = ($crew?->name ?? 'Crew')." — {$name} expires {$c->expiry_date->toDateString()} ({$label}).";
                        $link = $crew ? url('/crew/'.$crew->id) : null;
                        $notifier->notify($notifier->admins(), 'certificate_expiry', $title, $body, $link, ['panel','email','whatsapp']);
                        if ($crew) $notifier->notifyContact($crew->email, $crew->mobile, $title, $body, ['email','whatsapp']);
                        DocumentReminder::create([
                            'crew_profile_id' => $c->crew_profile_id,
                            'crew_course_id' => $c->id,
                            'offset_label' => $label,
                            'expiry_date' => $c->expiry_date->toDateString(),
                            'sent_for_date' => $today->toDateString(),
                            'channels' => ['email', 'whatsapp', 'panel'],
                            'sent_at' => now(),
                        ]);
                    }
                    $sent++;
                }
            });

        $this->info(($this->option('dry-run') ? '[dry-run] ' : '') . "Reminders to send: {$sent}");
        return self::SUCCESS;
    }

    protected function dueLabel(\Illuminate\Support\Carbon $expiry, \Illuminate\Support\Carbon $today, array $offsets): ?string
    {
        if ($expiry->lt($today)) return 'expired';          // daily after expiry
        $daysLeft = $today->diffInDays($expiry);
        foreach ($offsets as $o) {
            if ($daysLeft === $o) return "{$o}d";
        }
        return null;
    }
}
