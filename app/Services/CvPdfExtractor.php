<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

/**
 * Extracts crew data from an uploaded CV PDF (best-effort, label-based) using
 * smalot/pdfparser. Tuned to the GCSM CV format labels; refine the regex map as
 * real CVs come in. Only scalar profile fields are auto-filled to avoid bad rows;
 * sea-service / certificate parsing hooks are left for extension.
 *
 * @return array{profile:array,documents:array,courses:array,sea_services:array}
 */
class CvPdfExtractor
{
    public function extract(string $absolutePath): array
    {
        if (! class_exists(Parser::class)) {
            throw new \RuntimeException('smalot/pdfparser is not installed. Run: composer require smalot/pdfparser');
        }
        $text = (new Parser())->parseFile($absolutePath)->getText();
        $text = preg_replace('/[ \t]+/', ' ', $text);

        $profile = array_filter([
            'name' => $this->grab($text, ['Name of the Seafarer', 'Full Name', 'Name']),
            'name_chinese' => $this->grab($text, ['Name (Chinese)', 'Chinese Name']),
            'father_name' => $this->grab($text, ["Father's Name", 'Father Name', 'Father']),
            'mother_name' => $this->grab($text, ["Mother's Name", 'Mother Name', 'Mother']),
            'date_of_birth' => $this->grabDate($text, ['Date of Birth', 'D.O.B', 'DOB']),
            'place_of_birth' => $this->grab($text, ['Place of Birth']),
            'nationality' => $this->grab($text, ['Nationality']),
            'gender' => $this->grabGender($text),
            'mobile' => $this->grab($text, ['Contact Number', 'Mobile No', 'Mobile', 'Cell', 'Phone', 'Tel']),
            'email' => $this->grab($text, ['Email', 'E-mail']),
            'present_address' => $this->grab($text, ['Present Address', 'Current Address', 'Address']),
            'cdc_no' => $this->grab($text, ['CDC No', 'CDC', 'Seaman Book', "Seaman's Book"]),
            'passport_no' => $this->grab($text, ['Passport No', 'Passport']),
            'coc_no' => $this->grab($text, ['COC No', 'COC', 'Certificate of Competency', 'C.O.C']),
            'sid_no' => $this->grab($text, ['SID No', 'SID']),
            'nid_no' => $this->grab($text, ['National ID', 'NID No', 'NID']),
            'birth_registration_no' => $this->grab($text, ['Birth Registration', 'Birth Reg']),
            'blood_group' => $this->grab($text, ['Blood Group', 'Blood Type']),
            'marital_status' => $this->grab($text, ['Marital Status']),
        ], fn ($v) => $v !== null && $v !== '');

        return [
            'profile' => $profile,
            'documents' => [],
            'courses' => [],
            'sea_services' => [],
        ];
    }

    protected function grab(string $text, array $labels): ?string
    {
        foreach ($labels as $label) {
            if (preg_match('/'.preg_quote($label, '/').'\s*[:\-]?\s*([^\n\r]{1,60})/i', $text, $m)) {
                $val = trim($m[1]);
                // stop at the next label-ish token
                $val = preg_split('/\s{2,}|\s(?=[A-Z][a-z]+\s*[:\-])/', $val)[0] ?? $val;
                if ($val !== '' && ! preg_match('/^[:\-]/', $val)) return $val;
            }
        }
        return null;
    }

    protected function grabDate(string $text, array $labels): ?string
    {
        $raw = $this->grab($text, $labels);
        if (! $raw) return null;
        try { return \Carbon\Carbon::parse($raw)->toDateString(); } catch (\Throwable) { return null; }
    }

    protected function grabGender(string $text): ?string
    {
        $raw = $this->grab($text, ['Gender', 'Sex']);
        if (! $raw) return null;
        if (stripos($raw, 'female') !== false || strtoupper(trim($raw)) === 'F') return 'Female';
        if (stripos($raw, 'male') !== false || strtoupper(trim($raw)) === 'M') return 'Male';
        return null;
    }
}
