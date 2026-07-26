<?php

namespace App\Services;

use App\Models\Placement;
use Carbon\Carbon;

/**
 * Single source of truth for signing a crew off a voyage. Closes the placement,
 * records the reason/note, writes a sea-service row (so the crew CV auto-updates),
 * and updates the crew profile's availability/urgency (which unlocks the profile's
 * placement-status editor, since the crew is no longer "onboard").
 *
 * @param array $d validated data: sign_off_date, reason, note, has_dues,
 *                 availability, available_from, job_urgency, job_deadline
 */
class SignOffService
{
    public function apply(Placement $placement, array $d, ?int $userId): void
    {
        $reason = trim((string) ($d['reason'] ?? '')) ?: null;
        $note   = trim((string) ($d['note'] ?? '')) ?: null;

        $placement->update([
            'sign_off_date'   => $d['sign_off_date'],
            'status'          => 'signed_off',
            'has_dues'        => (bool) ($d['has_dues'] ?? false),
            'sign_off_reason' => $reason,
            'sign_off_note'   => $note,
        ]);

        $placement->loadMissing('crewProfile', 'vessel', 'principal');
        $days = $placement->sign_on_date
            ? $placement->sign_on_date->diffInDays($placement->sign_off_date) : null;

        // Auto-add this voyage to the crew's Sea Service.
        $placement->crewProfile->seaServices()->create([
            'company_name'    => optional($placement->principal)->name,
            'vessel_name'     => optional($placement->vessel)->vessel_name,
            'vessel_type'     => optional($placement->vessel)->vessel_type,
            'grt'             => optional($placement->vessel)->grt,
            'dwt'             => optional($placement->vessel)->dwt,
            'imo_no'          => optional($placement->vessel)->imo,
            'rank'            => $placement->rank,
            'sign_on'         => $placement->sign_on_date,
            'sign_off'        => $placement->sign_off_date,
            'duration_days'   => $days,
            'reason_sign_off' => $reason,
            'source'          => 'placement',
        ]);

        // Availability after sign-off: honour the choice; an Available crew returning
        // on a future date automatically becomes Resting until then.
        $availability = $d['availability'] ?? 'available';
        $availFrom = $d['available_from'] ?? null;
        if ($availability === 'available' && $availFrom && Carbon::parse($availFrom)->startOfDay()->isFuture()) {
            $availability = 'resting';
        }

        $logReason = $reason
            ? $reason.($note ? ' — '.$note : '')
            : ('Sign-off from voyage'.(optional($placement->vessel)->vessel_name ? ' — '.$placement->vessel->vessel_name : ''));

        app(CrewStatusUpdater::class)->apply(
            $placement->crewProfile,
            [
                'availability'   => $availability,
                'available_from' => $availFrom,
                'job_urgency'    => $d['job_urgency'] ?? 'normal',
                'job_deadline'   => $d['job_deadline'] ?? null,
            ],
            'sign_off',
            $logReason,
            $userId,
        );
    }
}
