<?php

namespace App\Services;

use App\Models\CrewProfile;
use Illuminate\Support\Collection;

/**
 * Strong duplicate detection so a crew can never have two profiles.
 * Exact match on any of CDC / Passport / SID / Phone / NID / Birth-Reg,
 * OR Name+DOB, OR Name+Father+Mother.
 *
 * @return Collection<int,array{crew:CrewProfile,reason:string}>
 */
class DuplicateCrewChecker
{
    protected array $exactKeys = [
        'cdc_no' => 'CDC No', 'passport_no' => 'Passport No', 'sid_no' => 'SID No',
        'mobile' => 'Phone', 'nid_no' => 'National ID', 'birth_registration_no' => 'Birth Reg No',
    ];

    public function find(array $data, ?int $excludeId = null): Collection
    {
        $norm = fn ($v) => mb_strtolower(trim((string) $v));
        $name = $norm($data['name'] ?? '');
        $dob = $norm($data['date_of_birth'] ?? $data['dob'] ?? '');
        $father = $norm($data['father_name'] ?? '');
        $mother = $norm($data['mother_name'] ?? '');

        return CrewProfile::query()
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->get()
            ->map(function (CrewProfile $c) use ($data, $norm, $name, $dob, $father, $mother) {
                $hits = [];
                foreach ($this->exactKeys as $field => $label) {
                    $a = $norm($data[$field] ?? '');
                    $b = $norm($c->{$field});
                    if ($a !== '' && $a === $b) $hits[] = $label;
                }
                if ($name !== '' && $name === $norm($c->name)) {
                    if ($dob !== '' && $dob === $norm(optional($c->date_of_birth)->toDateString())) $hits[] = 'Name + DOB';
                    if ($father !== '' && $father === $norm($c->father_name) && $mother !== '' && $mother === $norm($c->mother_name)) $hits[] = 'Name + Father + Mother';
                }
                return $hits ? ['crew' => $c, 'reason' => implode(', ', array_unique($hits))] : null;
            })
            ->filter()
            ->values();
    }
}
