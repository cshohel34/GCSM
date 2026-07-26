<?php

namespace App\Exports;

use App\Models\CrewProfile;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

/** CV export in the exact GCSM format (Personal, Maritime Education, Educational,
 *  English Level, Certificates & Travelling Documents, Sea Service). */
class CrewCvExport implements FromArray, WithTitle
{
    public function __construct(protected CrewProfile $crew) {}

    public function title(): string { return 'CV'; }

    public function array(): array
    {
        $c = $this->crew;
        $rank = optional($c->rankApplied)->rank_name ?: optional($c->currentRank)->rank_name;
        $rows = [
            ['GOLDEN CAREER SHIP MANAGEMENT'],
            [],
            ['PERSONAL DETAILS'],
            ['Name', $c->name, 'Date of Birth', optional($c->date_of_birth)->toDateString(), 'Rank Applied for', $rank],
            ['Contact Number', $c->mobile, 'Place of Birth', $c->place_of_birth, 'Marital Status', $c->marital_status],
            ['Address', $c->present_address ?: $c->permanent_address, 'Shoe Size', $c->shoe_size, 'Blood Group', $c->blood_group],
            ["Father's Name", $c->father_name, 'Height (CM)', $c->height_cm, 'Weight (KG)', $c->weight_kg],
            ["Mother's Name", $c->mother_name, 'Emergency Contact', $c->emergency_contact, 'SID No', $c->sid_no],
            ['CDC No', $c->cdc_no, 'Passport No', $c->passport_no, 'COC No', $c->coc_no],
            [],
            ['MARITIME EDUCATION DETAILS'],
            ['Name of Maritime Institute', '', 'Department', '', 'Year of Graduation', ''],
            [],
            ['EDUCATIONAL QUALIFICATION'],
            ['Description', 'Board', 'Group', 'Passing Year', 'GPA'],
            ['S.S.C', '', '', '', ''],
            ['H.S.C', '', '', '', ''],
            ['Others (If any)', '', '', '', ''],
            [],
            ['ENGLISH LEVEL', 'Listening', $c->english_listening, 'Spoken', $c->english_speaking, 'Reading', $c->english_reading, 'Writing', $c->english_writing],
            [],
            ['CERTIFICATES & TRAVELLING DOCUMENTS'],
            ['Category', 'Capacity', 'Certificate Number', 'Date of Issue', 'Date of Expiry', 'Issuer', 'Issuing Authority'],
        ];
        foreach ($c->documents as $d) {
            $rows[] = [$d->doc_type, $d->grade, $d->number, optional($d->issue_date)->toDateString(), optional($d->expiry_date)->toDateString(), $d->place_of_issue, $d->issuing_authority];
        }
        foreach ($c->courses as $cr) {
            $rows[] = [$cr->course_name, '', $cr->certificate_no, optional($cr->issue_date)->toDateString(), optional($cr->expiry_date)->toDateString(), $cr->issuing_authority, $cr->dos_registration_no];
        }
        $rows[] = [];
        $rows[] = ['SEA SERVICE DETAILS'];
        $rows[] = ['Company', 'Vessel', 'VSL.Type', 'GRT', 'Engine', 'BHP', 'Flag', 'Trading Area', 'Rank', 'Sign-On', 'Sign-Off', 'Days', 'Reason'];
        foreach ($c->seaServices as $s) {
            $rows[] = [$s->company_name, $s->vessel_name, $s->vessel_type, $s->grt, $s->engine_type, $s->bhp, $s->flag, $s->trading_area, $s->rank, optional($s->sign_on)->toDateString(), optional($s->sign_off)->toDateString(), $s->duration_days, $s->reason_sign_off];
        }
        return $rows;
    }
}
