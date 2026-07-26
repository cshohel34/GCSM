<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class CrewListExport implements FromArray, WithTitle
{
    public function __construct(protected Collection $crew) {}

    public function title(): string { return 'Crew List'; }

    public function array(): array
    {
        $rows = [
            ['GOLDEN CAREER SHIP MANAGEMENT — Crew List'],
            ['Generated', now()->toDayDateTimeString(), 'Count', $this->crew->count()],
            [],
            ['Admission ID', 'Name', 'Rank', 'Mobile', 'CDC No', 'Passport', 'COC', 'Availability', 'Urgency'],
        ];
        foreach ($this->crew as $c) {
            $rows[] = [
                $c->admission_id, $c->name, optional($c->currentRank)->rank_name, $c->mobile,
                $c->cdc_no, $c->passport_no, $c->coc_no,
                $c->availability === 'available' ? 'Available' : 'Not available', ucfirst($c->job_urgency),
            ];
        }
        return $rows;
    }
}
