<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\ChecklistTemplate;
use App\Models\CrewProfile;

/**
 * GCSM "Crew On Board" document checklist.
 *
 * The template lives in `checklist_templates` and is fully customisable from
 * Settings. Each candidate gets a copy when wishlisted; template changes are
 * synced onto every candidate automatically. Items with a match rule are
 * auto-mapped from the crew profile; the rest are ticked by the office.
 */
class CandidateChecklist
{
    /** In-request cache of the active template rows. */
    protected static ?\Illuminate\Support\Collection $templateCache = null;

    /** The active template items (customisable in Settings), in display order. */
    public static function templates(): \Illuminate\Support\Collection
    {
        if (self::$templateCache === null) {
            self::$templateCache = ChecklistTemplate::where('active', true)
                ->orderBy('sort_order')->orderBy('id')->get();
        }
        return self::$templateCache;
    }

    /** A template code is auto-mapped when it carries a match rule. */
    public static function isAutoCode(?string $code): bool
    {
        if (! $code) return false;
        $t = self::templates()->firstWhere('code', $code);
        return $t && ! empty($t->match_rule);
    }

    /** Create the checklist for a candidate if it has none yet (from the template). */
    public function ensure(Candidate $candidate): void
    {
        if ($candidate->checklistItems()->whereNotNull('code')->exists()) {
            return;
        }
        $this->sync($candidate);
    }

    /**
     * Reconcile a candidate's checklist with the current template and crew profile:
     *  - add template items the candidate is missing,
     *  - remove standard items whose template was deleted (custom items are kept),
     *  - keep the manual status/remarks of items that remain,
     *  - re-evaluate auto-mapped items from the crew profile.
     */
    public function sync(Candidate $candidate): void
    {
        $crew = $candidate->crewProfile()->with(['documents', 'courses'])->first();
        $templates = self::templates();
        $templateCodes = $templates->pluck('code')->all();

        $existing = $candidate->checklistItems()->get()->keyBy('code'); // custom items keyed by null are dropped from this map

        foreach ($templates as $tpl) {
            [$received, $source, $evidence] = $this->evaluate($tpl->match_rule, $crew);
            $isAuto = ! empty($tpl->match_rule);
            $item = $existing->get($tpl->code);

            if (! $item) {
                $candidate->checklistItems()->create([
                    'code'          => $tpl->code,
                    'item'          => $tpl->label,
                    'sort_order'    => $tpl->sort_order,
                    'is_received'   => $received,
                    'auto_source'   => $source,
                    'evidence_path' => $evidence,
                    'required'      => true,
                ]);
                continue;
            }

            // Keep the item; refresh label/order from the template.
            $update = ['item' => $tpl->label, 'sort_order' => $tpl->sort_order];
            if ($isAuto) {
                // Auto items always reflect the profile.
                $update['is_received']   = $received;
                $update['auto_source']   = $source;
                $update['evidence_path'] = $evidence;
            } else {
                // Manual item that used to be auto-mapped — clear any stale evidence.
                $update['auto_source']   = null;
                $update['evidence_path'] = null;
            }
            $item->update($update);
        }

        // Drop standard items whose template no longer exists (keep per-candidate custom items).
        $candidate->checklistItems()
            ->whereNotNull('code')
            ->whereNotIn('code', $templateCodes)
            ->delete();
    }

    /** Re-run the sync (template reconcile + profile auto-map). */
    public function remap(Candidate $candidate): void
    {
        $this->sync($candidate);
    }

    /** Completion percentage (received / total). */
    public function percent(Candidate $candidate): int
    {
        $items = $candidate->checklistItems;
        $total = $items->count();
        if ($total === 0) return 0;
        return (int) round($items->where('is_received', true)->count() / $total * 100);
    }

    /**
     * Decide whether a match rule is satisfied by the crew's data.
     * @return array{0:bool,1:?string,2:?string} [received, source, evidence path (asset-relative)]
     */
    protected function evaluate(?string $rule, ?CrewProfile $crew): array
    {
        if (! $rule || ! $crew) return [false, null, null];

        // Return [doc_type label, scan_path] of the first document whose type matches.
        $docHit = function (array $needles) use ($crew) {
            foreach ($crew->documents as $d) {
                $type = mb_strtolower((string) $d->doc_type);
                foreach ($needles as $n) {
                    if ($type !== '' && str_contains($type, mb_strtolower($n))) {
                        return [$d->doc_type, $d->scan_path];
                    }
                }
            }
            return null;
        };

        switch ($rule) {
            case 'cv':
                // GCSM generates the CV from the profile — evidence handled specially in the view.
                return $crew->name ? [true, 'CV generated from GCSM profile', null] : [false, null, null];

            case 'photo':
                return $crew->photo_path ? [true, 'Profile photo on file', $crew->photo_path] : [false, null, null];

            case 'cdc':
                if ($h = $docHit(['cdc', 'seaman'])) return [true, $h[0].' on file', $h[1]];
                return $crew->cdc_no ? [true, 'CDC No: '.$crew->cdc_no, null] : [false, null, null];

            case 'passport':
                if ($h = $docHit(['passport'])) return [true, $h[0].' on file', $h[1]];
                return $crew->passport_no ? [true, 'Passport No: '.$crew->passport_no, null] : [false, null, null];

            case 'coc':
                if ($h = $docHit(['coc', 'competency'])) return [true, $h[0].' on file', $h[1]];
                return $crew->coc_no ? [true, 'COC No: '.$crew->coc_no, null] : [false, null, null];

            case 'short_course':
                $withScan = $crew->courses->firstWhere('scan_path', '!=', null);
                $count = $crew->courses->count();
                return $count > 0 ? [true, $count.' course certificate(s) on file', optional($withScan)->scan_path] : [false, null, null];

            case 'port_health':
                if ($h = $docHit(['port health', 'medical', 'health'])) return [true, $h[0].' on file', $h[1]];
                return [false, null, null];

            case 'sid':
                if ($h = $docHit(['sid', 'identity document'])) return [true, $h[0].' on file', $h[1]];
                return $crew->sid_no ? [true, 'SID No: '.$crew->sid_no, null] : [false, null, null];

            case 'next_of_kin':
                return $crew->emergency_contact ? [true, 'Emergency contact on file', null] : [false, null, null];

            case 'visa':
                if ($h = $docHit(['visa'])) return [true, $h[0].' on file', $h[1]];
                return [false, null, null];

            case 'yellow_fever':
                if ($h = $docHit(['yellow fever', 'cholera'])) return [true, $h[0].' on file', $h[1]];
                return [false, null, null];
        }

        return [false, null, null];
    }
}
