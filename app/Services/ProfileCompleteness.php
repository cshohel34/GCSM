<?php

namespace App\Services;

use App\Models\CrewProfile;

/**
 * Measures how complete a crew profile is against the GCSM CV template.
 * Returns a percentage, the list of what is still missing, and a done flag so
 * the profile page can show a blinking yellow warning (incomplete) or green (done).
 */
class ProfileCompleteness
{
    /** Personal / identity fields that must be present (CV "Personal Details"). */
    protected array $profileFields = [
        'name' => 'Name',
        'date_of_birth' => 'Date of Birth',
        'place_of_birth' => 'Place of Birth',
        'nationality' => 'Nationality',
        'gender' => 'Gender',
        'marital_status' => 'Marital Status',
        'blood_group' => 'Blood Group',
        'height_cm' => 'Height (CM)',
        'weight_kg' => 'Weight (KG)',
        'shoe_size' => 'Shoe Size',
        'mobile' => 'Contact Number',
        'present_address' => 'Address',
        'photo_path' => 'Photo',
        'rank_applied_id' => 'Rank Applied for',
        'next_of_kin_name' => 'Next of Kin',
        'next_of_kin_contact' => 'Next of Kin Contact',
        'next_of_kin_address' => 'Next of Kin Address',
        'cdc_no' => 'Seaman Book (CDC)',
        'passport_no' => 'Passport No',
        // English level (4)
        'english_listening' => 'English — Listening',
        'english_speaking' => 'English — Spoken',
        'english_reading' => 'English — Reading',
        'english_writing' => 'English — Writing',
    ];

    /** Sections that need at least one row. */
    protected array $sections = [
        'maritimeEducations' => 'Maritime Education',
        'academics' => 'Educational Qualification',
        'courses' => 'Certificates & Documents',
        'seaServices' => 'Sea Service',
    ];

    public function for(CrewProfile $crew): array
    {
        $missing = [];
        $total = 0;
        $done = 0;

        foreach ($this->profileFields as $key => $label) {
            $total++;
            $val = $crew->getAttribute($key);
            if ($val === null || $val === '') {
                $missing[] = $label;
            } else {
                $done++;
            }
        }

        foreach ($this->sections as $rel => $label) {
            $total++;
            if ($crew->{$rel}()->count() > 0) {
                $done++;
            } else {
                $missing[] = 'At least one '.$label;
            }
        }

        $percent = $total ? (int) round($done / $total * 100) : 100;

        return [
            'percent' => $percent,
            'complete' => count($missing) === 0,
            'missing' => $missing,
            'done' => $done,
            'total' => $total,
        ];
    }
}
