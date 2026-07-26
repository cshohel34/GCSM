<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\CrewCourse;
use App\Models\CrewDocument;
use App\Models\CrewProfile;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Single source of truth for document / certificate validity, and the engine that
 * keeps expiry notifications coming every day (login-driven — no cron required)
 * until the item is renewed. Notifications are high priority and append-only.
 */
class ExpiryMonitor
{
    /** Validity of an expiry date: expired | expiring (<= 30 days) | valid | na. */
    public function status($expiry): string
    {
        if (! $expiry) return 'na';
        $e = $expiry instanceof Carbon ? $expiry->copy()->startOfDay() : Carbon::parse($expiry)->startOfDay();
        $today = now()->startOfDay();
        if ($e->lt($today)) return 'expired';
        if ($e->lte($today->copy()->addDays(30))) return 'expiring';
        return 'valid';
    }

    /** Combined document + certificate validity counts for one crew profile (computed live). */
    public function crewCounts(CrewProfile $crew): array
    {
        $c = ['valid' => 0, 'expiring' => 0, 'expired' => 0];
        foreach ($crew->documents as $d) { $s = $this->status($d->expiry_date); if (isset($c[$s])) $c[$s]++; }
        foreach ($crew->courses as $r)   { $s = $this->status($r->expiry_date); if (isset($c[$s])) $c[$s]++; }
        return $c;
    }

    /**
     * Ensure today's expiry notifications exist for this user, covering every
     * expired/expiring document and certificate. A fresh dedupe key per day means
     * the reminder is re-created each day (even if yesterday's was read) and stops
     * automatically once the item becomes valid again.
     */
    public function runForUser(User $user): void
    {
        $today = now()->toDateString();

        CrewDocument::with('crewProfile')->whereNotNull('expiry_date')
            ->chunkById(300, function ($docs) use ($user, $today) {
                foreach ($docs as $d) {
                    $s = $this->status($d->expiry_date);
                    if ($s !== 'expired' && $s !== 'expiring') continue;
                    $when = optional($d->expiry_date)->toDateString();
                    $this->ensure(
                        $user, 'document_expiry', 'doc:'.$d->id.':'.$today,
                        ($s === 'expired' ? 'Document EXPIRED: ' : 'Document expiring: ').$d->doc_type,
                        (optional($d->crewProfile)->name ?: 'Crew').' — '.$d->doc_type.' '.($s === 'expired' ? 'expired on ' : 'expires ').$when.'.',
                        $d->crew_profile_id ? url('/crew/'.$d->crew_profile_id) : null,
                        $d->crew_profile_id
                    );
                }
            });

        CrewCourse::with('crewProfile')->whereNotNull('expiry_date')
            ->chunkById(300, function ($courses) use ($user, $today) {
                foreach ($courses as $r) {
                    $s = $this->status($r->expiry_date);
                    if ($s !== 'expired' && $s !== 'expiring') continue;
                    $name = $r->category ?: $r->course_name;
                    $when = optional($r->expiry_date)->toDateString();
                    $this->ensure(
                        $user, 'certificate_expiry', 'cert:'.$r->id.':'.$today,
                        ($s === 'expired' ? 'Certificate EXPIRED: ' : 'Certificate expiring: ').$name,
                        (optional($r->crewProfile)->name ?: 'Crew').' — '.$name.' '.($s === 'expired' ? 'expired on ' : 'expires ').$when.'.',
                        $r->crew_profile_id ? url('/crew/'.$r->crew_profile_id) : null,
                        $r->crew_profile_id
                    );
                }
            });
    }

    protected function ensure(User $user, string $type, string $key, string $title, string $body, ?string $link, ?int $crewId = null): void
    {
        if (AppNotification::where('user_id', $user->id)->where('dedupe_key', $key)->exists()) {
            return;
        }
        AppNotification::create([
            'user_id'         => $user->id,
            'crew_profile_id' => $crewId,
            'type'            => $type,
            'priority'        => 'high',
            'title'           => $title,
            'body'            => $body,
            'link'            => $link,
            'dedupe_key'      => $key,
        ]);
    }
}
