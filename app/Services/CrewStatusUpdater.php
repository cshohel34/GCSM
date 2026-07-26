<?php

namespace App\Services;

use App\Models\CrewProfile;
use App\Models\CrewStatusLog;
use Carbon\Carbon;

/**
 * Single place that changes a crew profile's availability / job-urgency.
 * It enforces the lifecycle rules, writes the new values to the crew profile
 * (so the change syncs everywhere — crew list, Crew Selection, dashboard), and
 * records an audit-log row (who / when / why) whenever something actually changed.
 */
class CrewStatusUpdater
{
    /**
     * @param array $data may contain: availability, job_urgency, job_deadline, available_from
     * @return bool whether anything changed (and was therefore logged)
     */
    public function apply(CrewProfile $crew, array $data, string $context, ?string $reason, ?int $userId): bool
    {
        $old = [
            'availability'   => $crew->availability,
            'job_urgency'    => $crew->job_urgency,
            'job_deadline'   => optional($crew->job_deadline)->toDateString(),
            'available_from' => optional($crew->available_from)->toDateString(),
        ];

        $new = [
            'availability'   => $data['availability'] ?? $old['availability'],
            'job_urgency'    => $data['job_urgency'] ?? $old['job_urgency'],
            'job_deadline'   => array_key_exists('job_deadline', $data) ? ($data['job_deadline'] ?: null) : $old['job_deadline'],
            'available_from' => array_key_exists('available_from', $data) ? ($data['available_from'] ?: null) : $old['available_from'],
        ];

        // Lifecycle rules: Normal urgency carries no deadline. Honour the chosen
        // availability exactly — only a Resting crew keeps a future "available from"
        // date; any other status clears it (so an Available crew stays Available).
        if (($new['job_urgency'] ?? 'normal') === 'normal') {
            $new['job_deadline'] = null;
        }
        if ($new['availability'] !== 'resting') {
            $new['available_from'] = null;
        }

        $changed = $old != $new;

        $crew->update($new);

        if ($changed) {
            CrewStatusLog::create([
                'crew_profile_id'    => $crew->id,
                'changed_by'         => $userId,
                'context'            => $context,
                'old_availability'   => $old['availability'],
                'new_availability'   => $new['availability'],
                'old_urgency'        => $old['job_urgency'],
                'new_urgency'        => $new['job_urgency'],
                'old_deadline'       => $old['job_deadline'],
                'new_deadline'       => $new['job_deadline'],
                'old_available_from' => $old['available_from'],
                'new_available_from' => $new['available_from'],
                'reason'             => $reason,
            ]);
        }

        return $changed;
    }
}
