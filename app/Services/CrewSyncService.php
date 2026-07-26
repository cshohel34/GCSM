<?php

namespace App\Services;

use App\Models\ApiSyncLog;
use App\Models\CrewProfile;
use App\Models\Rank;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Maps an OMA student payload into a GCSM Crew Profile and its child rows.
 * One-way (OMA -> GCSM): OMA-sourced fields and OMA child rows are refreshed;
 * GCSM-only fields (availability, offences, banking, etc.) are never touched.
 */
class CrewSyncService
{
    /** Sync a batch; returns count synced. */
    public function syncMany(array $students, string $endpoint, ?string $reference = null): int
    {
        $count = 0;
        foreach ($students as $s) {
            try {
                $this->syncOne($s);
                $count++;
            } catch (\Throwable $e) {
                ApiSyncLog::create([
                    'endpoint' => $endpoint,
                    'reference' => Arr::get($s, 'studentID', $reference),
                    'status' => 'error',
                    'records' => 0,
                    'message' => $e->getMessage(),
                ]);
            }
        }
        ApiSyncLog::create([
            'endpoint' => $endpoint,
            'reference' => $reference,
            'status' => 'success',
            'records' => $count,
            'message' => "Synced {$count} student(s).",
        ]);
        return $count;
    }

    public function syncOne(array $s): CrewProfile
    {
        $admissionId = (string) Arr::get($s, 'studentID');
        if ($admissionId === '') {
            throw new \InvalidArgumentException('Missing studentID in OMA payload.');
        }

        return DB::transaction(function () use ($s, $admissionId) {
            $basic = Arr::get($s, 'basicInfo', []);
            $addr = Arr::get($s, 'addressInfo', []);

            $profile = CrewProfile::withTrashed()->firstOrNew(['admission_id' => $admissionId]);
            if ($profile->trashed()) $profile->restore();

            // OMA-sourced fields (one-way refresh). GCSM-only fields untouched.
            $profile->fill([
                'source' => 'oma',
                'name' => Arr::get($s, 'studentName', $profile->name),
                'email' => Arr::get($s, 'email') ?: $profile->email,
                'gender' => Arr::get($s, 'gender'),
                'date_of_birth' => $this->date(Arr::get($s, 'dateOfBirth')),
                'place_of_birth' => Arr::get($s, 'placeOfBirth'),
                'nationality' => Arr::get($s, 'nationality', 'Bangladeshi'),
                'current_rank_id' => $this->rankId(Arr::get($s, 'rank')) ?? $profile->current_rank_id,
                'mobile' => Arr::get($basic, 'phone') ?: $profile->mobile,
                'emergency_contact' => Arr::get($basic, 'emergencyContact') ?: $profile->emergency_contact,
                'height_cm' => Arr::get($basic, 'height'),
                'weight_kg' => Arr::get($basic, 'weight'),
                'cdc_no' => Arr::get($basic, 'cdcNo') ?: $profile->cdc_no,
                'eye_colour' => Arr::get($basic, 'eyesColour'),
                'passport_no' => Arr::get($basic, 'passportNumber') ?: $profile->passport_no,
                'nid_no' => Arr::get($basic, 'nidNumber') ?: $profile->nid_no,
                'identification_mark' => Arr::get($basic, 'identificationMark'),
                'present_address' => Arr::get($addr, 'presentAddress') ?: $profile->present_address,
                'permanent_address' => Arr::get($addr, 'permanentAddress') ?: $profile->permanent_address,
                'oma_synced_at' => now(),
            ]);
            $profile->save();

            $this->syncSeaService($profile, Arr::get($s, 'seaInfo', []));
            $this->syncCourses($profile, Arr::get($s, 'courseRows', []), Arr::get($s, 'certificateRows', []));

            return $profile;
        });
    }

    protected function syncSeaService(CrewProfile $profile, array $rows): void
    {
        // Refresh OMA-sourced sea service only; keep manual/placement rows.
        $profile->seaServices()->where('source', 'oma')->delete();
        foreach ($rows as $r) {
            $profile->seaServices()->create([
                'vessel_name' => Arr::get($r, 'nameOfVessel'),
                'vessel_type' => Arr::get($r, 'typeOfVessel'),
                'rank' => Arr::get($r, 'rank'),
                'grt' => Arr::get($r, 'GRT'),
                'sign_on' => $this->date(Arr::get($r, 'signOn')),
                'sign_off' => $this->date(Arr::get($r, 'signOff')),
                'duration_days' => is_numeric(Arr::get($r, 'duration')) ? (int) Arr::get($r, 'duration') : null,
                'source' => 'oma',
            ]);
        }
    }

    protected function syncCourses(CrewProfile $profile, array $courseRows, array $certRows): void
    {
        $profile->courses()->where('source', 'oma')->delete();

        // Index certificate detail by courseName for issue date / DOS reg / cert no.
        $certByName = [];
        foreach ($certRows as $c) {
            $certByName[strtoupper(trim(Arr::get($c, 'courseName', '')))] = $c;
        }

        foreach ($courseRows as $r) {
            $name = Arr::get($r, 'courseName', '');
            $cert = $certByName[strtoupper(trim($name))] ?? [];
            $profile->courses()->create([
                'course_name' => $name,
                'course_code' => Arr::get($cert, 'courseCode'),
                'completion_date' => null,
                'issue_date' => $this->date(Arr::get($cert, 'issueDate')),
                'expiry_date' => null, // OMA does not return expiry; entered in GCSM if needed
                'issuing_authority' => 'Ocean Maritime Academy',
                'dos_registration_no' => trim((string) Arr::get($cert, 'DOSRegistration')) ?: null,
                'certificate_no' => Arr::get($cert, 'certificateNo'),
                'certificate_full_format' => Arr::get($cert, 'certificateFullFormat'),
                'source' => 'oma',
            ]);
        }
    }

    protected function rankId(?string $name): ?int
    {
        $name = trim((string) $name);
        if ($name === '') return null;
        return Rank::firstOrCreate(['rank_name' => $name])->id;
    }

    protected function date(?string $v): ?string
    {
        $v = trim((string) $v);
        if ($v === '' || $v === '0000-00-00') return null;
        try { return Carbon::parse($v)->toDateString(); } catch (\Throwable) { return null; }
    }
}
