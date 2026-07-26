<?php

namespace Database\Seeders;

use App\Models\CrewProfile;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo data: at least 10 marine profiles per active rank, fully populated so
 * every profile reads as "Complete" (100%). Courses, certificates and sea
 * service are chosen to match each rank. A realistic mix of availability is
 * used, and at least 20 marines are given 2–3 expired certificates.
 *
 * Safe to re-run: it first removes any previously-seeded demo crew (identified
 * by the @demo.gcsm.local email marker) before creating a fresh batch.
 */
class DemoMarinersSeeder extends Seeder
{
    protected string $photo = 'crew/placeholder.png';

    protected array $first = [
        'Mohammad','Abdul','Md','Shohel','Rakib','Kamrul','Jahangir','Nasir','Rafiqul','Shahin',
        'Tanvir','Imran','Sohel','Faruk','Hasan','Mizanur','Aminul','Saiful','Jamal','Ashraful',
        'Rubel','Sabbir','Anisur','Delwar','Mahmud','Rezaul','Shamim','Nazmul','Arif','Kabir',
        'Habibur','Golam','Monirul','Ripon','Sujon','Belal','Firoz','Masud','Rasel','Bappi',
    ];
    protected array $last = [
        'Islam','Hossain','Rahman','Ahmed','Khan','Uddin','Chowdhury','Sarkar','Molla','Mia',
        'Hoque','Alam','Kabir','Sheikh','Talukder','Bhuiyan','Miah','Akter','Karim','Mondol',
    ];

    protected array $companies = [
        'Anglo-Eastern Ship Management','Bernhard Schulte Shipmanagement','Wallem Ship Management',
        'Fleet Management Ltd','Synergy Marine Group','V.Ships','Thome Ship Management',
        'MSC Crewing Services','Maersk Fleet Management','Columbia Shipmanagement','OSM Maritime',
    ];
    protected array $vesselTypes = [
        'Bulk Carrier','Container Ship','Oil Tanker','Chemical Tanker','LPG Carrier',
        'General Cargo','Car Carrier (PCTC)','Crude Oil Tanker','Product Tanker',
    ];
    protected array $vesselNames = [
        'MV Ocean Pride','MV Pacific Star','MT Gulf Falcon','MV Sea Harmony','MT Blue Horizon',
        'MV Northern Light','MV Global Trader','MT Eastern Dawn','MV Cape Victory','MT Silver Wave',
        'MV Atlantic Breeze','MV Meghna Princess','MT Bengal Pioneer','MV Southern Cross','MT Desert Rose',
    ];
    protected array $flags = ['Panama','Liberia','Marshall Islands','Singapore','Hong Kong','Bahamas','Malta'];
    protected array $tradingAreas = ['Worldwide','Far East','Middle East Gulf','Europe / Med','US Gulf','South East Asia'];
    protected array $bloodGroups = ['A+','B+','O+','AB+','A-','B-','O-'];
    protected array $engLevels = ['Good','Fair','Very Good','Excellent'];
    protected array $ports = ['Chattogram','Dhaka','Khulna','Barishal','Noakhali','Cumilla','Sylhet','Bhola','Feni','Narayanganj'];

    public function run(): void
    {
        // ---- clean any previous demo batch (idempotent) ----
        CrewProfile::withTrashed()->where('email', 'like', '%@demo.gcsm.local')->get()
            ->each(fn ($c) => $c->forceDelete());

        $adminId = User::orderBy('id')->value('id');
        $ranks   = Rank::where('active', true)->orderBy('sort_order')->get();
        if ($ranks->isEmpty()) {
            $this->command?->warn('No active ranks found — run RankSeeder first.');
            return;
        }

        $expiredMarinersTarget = 24;   // ≥ 20 required
        $expiredMarinersDone   = 0;
        $seq = 0;

        foreach ($ranks as $rankIndex => $rank) {
            $tier = $this->tier($rank->rank_name);            // 0=cadet/junior,1=rating,2=jr officer,3=sr officer
            for ($i = 1; $i <= 10; $i++) {
                $seq++;
                // one expired-cert marine per rank, until target reached
                $makeExpired = ($i === 1 && $expiredMarinersDone < $expiredMarinersTarget);

                $crew = $this->makeProfile($rank, $tier, $seq, $adminId);
                $this->addEducation($crew, $rank);
                $this->addCourses($crew, $rank, false);
                $this->addDocuments($crew, $rank, $tier, $makeExpired);
                $this->addSeaService($crew, $rank, $tier);
                $this->addBank($crew);

                if ($makeExpired) $expiredMarinersDone++;
            }
        }

        $this->command?->info("Seeded {$seq} demo marine profiles across {$ranks->count()} ranks; {$expiredMarinersDone} with expired certificates.");
    }

    /* ---------------------------------------------------------------- */

    protected function tier(string $name): int
    {
        $sr = ['Master','Chief Officer','Chief Engineer','2nd Engineer','Second Engineer','2nd Officer'];
        $offWords = ['Officer','Engineer','ETO','Electro','Master','Purser','Safety'];
        if (in_array($name, ['Master','Chief Officer','Chief Engineer','2nd Engineer'])) return 3;
        if (str_contains($name, 'Cadet') || str_contains($name, 'Trainee') || str_starts_with($name, 'Fresh')) return 0;
        foreach ($offWords as $w) if (str_contains($name, $w)) return 2;
        return 1; // ratings & catering
    }

    protected function isOfficer(int $tier): bool { return $tier >= 2; }

    protected function makeProfile(Rank $rank, int $tier, int $seq, ?int $adminId): CrewProfile
    {
        $first = $this->first[($seq * 7) % count($this->first)];
        $last  = $this->last[($seq * 3) % count($this->last)];
        $name  = "$first $last";

        // age band by tier
        [$minAge, $maxAge] = [[20, 26], [24, 42], [28, 46], [40, 58]][$tier];
        $age  = random_int($minAge, $maxAge);
        $dob  = Carbon::now()->subYears($age)->subDays(random_int(0, 364));

        $height = random_int(160, 183);
        $weight = random_int(58, 88);

        // availability mix
        $r = random_int(1, 100);
        $availability = $r <= 55 ? 'available' : ($r <= 75 ? 'onboard' : ($r <= 90 ? 'not_available' : 'resting'));
        $urgency = 'normal'; $deadline = null; $availableFrom = null;
        if ($availability === 'available') {
            $u = random_int(1, 100);
            $urgency = $u <= 70 ? 'normal' : ($u <= 90 ? 'high' : 'urgent');
            if ($urgency !== 'normal') $deadline = Carbon::now()->addDays(random_int(5, 40));
        }
        if ($availability === 'resting') $availableFrom = Carbon::now()->addDays(random_int(10, 90));

        $slug = strtolower($first.'.'.$last).$seq;

        return CrewProfile::create([
            'admission_id'      => null,
            'gc_id'             => CrewProfile::generateGcId(),
            'source'            => 'manual',
            'created_by'        => $adminId,
            'name'              => $name,
            'father_name'       => $this->first[($seq * 5) % count($this->first)].' '.$last,
            'mother_name'       => 'Mst. '.$this->last[($seq * 11) % count($this->last)].' Begum',
            'photo_path'        => $this->photo,
            'rank_applied_id'   => $rank->id,
            'current_rank_id'   => $rank->id,
            'date_of_birth'     => $dob->toDateString(),
            'place_of_birth'    => $this->ports[$seq % count($this->ports)],
            'nationality'       => 'Bangladeshi',
            'religion'          => 'Islam',
            'gender'            => 'Male',
            'marital_status'    => $tier >= 2 ? 'Married' : (random_int(0, 1) ? 'Married' : 'Single'),
            'height_cm'         => (string) $height,
            'weight_kg'         => (string) $weight,
            'blood_group'       => $this->bloodGroups[$seq % count($this->bloodGroups)],
            'shoe_size'         => (string) random_int(39, 45),
            'coverall_size'     => ['S','M','L','XL','XXL'][random_int(0, 4)],
            'hair_colour'       => 'Black',
            'eye_colour'        => 'Black',
            'identification_mark' => 'A black mole on '.['left','right'][random_int(0,1)].' forearm',
            'mobile'            => '01'.[7,8,9,6][random_int(0,3)].str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'email'             => $slug.'@demo.gcsm.local',
            'present_address'   => 'Vill: '.$this->last[$seq % count($this->last)].', PO: '.$this->ports[$seq % count($this->ports)].', Bangladesh',
            'permanent_address' => 'Vill: '.$this->last[$seq % count($this->last)].', Dist: '.$this->ports[($seq+1) % count($this->ports)].', Bangladesh',
            'emergency_contact' => '01'.[7,8,9][random_int(0,2)].str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'next_of_kin_name'  => 'Mst. '.$this->last[($seq*13) % count($this->last)].' Akter',
            'next_of_kin_relation' => $tier >= 2 ? 'Wife' : 'Father',
            'next_of_kin_contact'  => '01'.[7,8,9][random_int(0,2)].str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'next_of_kin_address'  => 'Same as permanent address',
            'cdc_no'            => 'BD-CDC-'.str_pad((string) (100000 + $seq), 6, '0', STR_PAD_LEFT),
            'passport_no'       => 'BX'.str_pad((string) (2000000 + $seq), 7, '0', STR_PAD_LEFT),
            'coc_no'            => $this->isOfficer($tier) ? 'COC-BD-'.str_pad((string) (50000 + $seq), 5, '0', STR_PAD_LEFT) : null,
            'nid_no'            => (string) random_int(1000000000, 9999999999),
            'indos_no'          => '18'.str_pad((string) $seq, 8, '0', STR_PAD_LEFT),
            'sid_no'            => 'SID-'.str_pad((string) $seq, 7, '0', STR_PAD_LEFT),
            'birth_registration_no' => '2019'.str_pad((string) $seq, 13, '0', STR_PAD_LEFT),
            'english_listening' => $this->engLevels[random_int(0, 3)],
            'english_speaking'  => $this->engLevels[random_int(0, 3)],
            'english_reading'   => $this->engLevels[random_int(0, 3)],
            'english_writing'   => $this->engLevels[random_int(0, 3)],
            'availability'      => $availability,
            'job_urgency'       => $urgency,
            'job_deadline'      => $deadline?->toDateString(),
            'available_from'    => $availableFrom?->toDateString(),
            'is_draft'          => false,
            'blacklist_status'  => 'active',
        ]);
    }

    protected function addEducation(CrewProfile $crew, Rank $rank): void
    {
        $officer = $this->isOfficer($this->tier($rank->rank_name));
        $gradYear = (int) Carbon::parse($crew->date_of_birth)->year + random_int(20, 24);
        $crew->maritimeEducations()->create([
            'institute' => $officer
                ? ['Bangladesh Marine Academy, Chattogram','Marine Academy & Ship Building Institute'][random_int(0,1)]
                : ['Bangladesh Marine Fisheries Academy','National Maritime Institute, Chattogram','Marine Technology Institute'][random_int(0,2)],
            'department' => $rank->department === 'Engine' ? 'Marine Engineering'
                : ($rank->department === 'Deck' ? 'Nautical Science' : 'Maritime Studies'),
            'year_of_graduation' => (string) min($gradYear, (int) date('Y')),
            'source' => 'manual',
        ]);

        $sscYear = min($gradYear - 4, (int) date('Y'));
        $crew->academics()->createMany([
            ['description' => 'S.S.C', 'board' => 'Comilla', 'group' => 'Science', 'passing_year' => (string) ($sscYear - 2), 'gpa' => number_format(random_int(350, 500) / 100, 2), 'source' => 'manual'],
            ['description' => 'H.S.C', 'board' => 'Comilla', 'group' => 'Science', 'passing_year' => (string) $sscYear, 'gpa' => number_format(random_int(350, 500) / 100, 2), 'source' => 'manual'],
        ]);
    }

    /** STCW + rank-specific training courses (crew_courses). */
    protected function addCourses(CrewProfile $crew, Rank $rank, bool $makeExpired): void
    {
        $tier = $this->tier($rank->rank_name);
        $dept = $rank->department;

        // Basic STCW (everyone) — code, name, validityYears (0 = no expiry)
        $courses = [
            ['PST',  'Personal Survival Techniques', 5],
            ['FPFF', 'Fire Prevention & Fire Fighting', 5],
            ['EFA',  'Elementary First Aid', 5],
            ['PSSR', 'Personal Safety and Social Responsibilities', 0],
            ['PSC&RB','Proficiency in Survival Craft & Rescue Boats', 5],
            ['SAT',  'Security Awareness Training', 0],
        ];

        if ($tier >= 1) {
            $courses[] = ['AFF', 'Advanced Fire Fighting', 5];
            $courses[] = ['MFA', 'Medical First Aid', 5];
        }
        if ($dept === 'Deck') {
            if ($this->isOfficer($tier)) {
                $courses[] = ['GMDSS', "GMDSS General Operator's Certificate (GOC)", 5];
                $courses[] = ['ECDIS', 'Electronic Chart Display & Information Systems (ECDIS)', 0];
                $courses[] = ['ROC ARPA', "Radar Observer's Course with ARPA", 0];
                $courses[] = ['BRM', 'Bridge Resource Management & Leadership', 0];
                if ($tier >= 3) $courses[] = ['MC', 'Medical Care', 5];
            } else {
                $courses[] = ['EDH', 'Efficient Deck Hand', 0];
                $courses[] = ['RASD', 'Rating as Able Seafarer Deck', 0];
                $courses[] = ['NWR', 'Rating Forming Part of a Navigational Watch', 0];
            }
        } elseif ($dept === 'Engine') {
            if ($this->isOfficer($tier)) {
                $courses[] = ['ERS', 'Engine Room Simulator', 0];
                $courses[] = ['HVS(ML)', 'High Voltage System (Management Level)', 0];
                if (str_contains($rank->rank_name, 'ETO') || str_contains($rank->rank_name, 'Electr')) {
                    $courses[] = ['JMETO', 'Junior Marine Electro-Technical Officer Course', 0];
                }
                if ($tier >= 3) $courses[] = ['MC', 'Medical Care', 5];
            } else {
                $courses[] = ['RASE', 'Rating as Able Seafarer Engine', 0];
                $courses[] = ['EWR', 'Rating Forming Part of an Engineering Watch', 0];
                if (str_contains($rank->rank_name, 'Fitter') || str_contains($rank->rank_name, 'Welder')) {
                    $courses[] = ['FCW', 'Pre-Sea Fitter-cum-Welder Rating Certificate', 0];
                }
            }
        } elseif ($dept === 'Catering') {
            $courses[] = ['COOK', "Ship's Cook / Catering Services Certificate", 0];
            $courses[] = ['MFA', 'Medical First Aid', 5];
        }

        // tanker endorsements for some
        if (in_array($dept, ['Deck','Engine']) && random_int(0, 1)) {
            $courses[] = ['BOCT', 'Basic Training for Oil & Chemical Tanker Cargo Operations', 5];
            if ($this->isOfficer($tier)) $courses[] = ['AOT', 'Advanced Training for Oil Tanker Cargo Operations', 5];
        }

        $expiredLeft = $makeExpired ? random_int(2, 3) : 0;
        $authorities = ['Bangladesh Marine Academy','DoS Bangladesh (Dept. of Shipping)','Marine Academy & Ship Building Institute'];

        foreach ($courses as $idx => [$code, $cname, $validity]) {
            $expireThis = false;
            $issue = Carbon::now()->subMonths(random_int(6, 44));
            if ($validity > 0) {
                if ($expiredLeft > 0 && random_int(0, 1)) {
                    // force expired: issued long enough ago that it lapsed
                    $issue = Carbon::now()->subYears($validity)->subMonths(random_int(2, 20));
                    $expireThis = true;
                    $expiredLeft--;
                }
                $expiry = (clone $issue)->addYears($validity);
            } else {
                $expiry = null;
            }

            $crew->courses()->create([
                'course_name'       => $cname,
                'course_code'       => $code,
                'category'          => 'STCW Course',
                'capacity'          => $rank->rank_name,
                'completion_date'   => $issue->toDateString(),
                'issue_date'        => $issue->toDateString(),
                'expiry_date'       => $expiry?->toDateString(),
                'issuing_authority' => $authorities[$idx % count($authorities)],
                'issuer'            => 'Government of Bangladesh',
                'certificate_no'    => strtoupper($code).'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
                'dos_registration_no' => 'DOS/'.random_int(1000, 9999),
                'source'            => 'manual',
            ]);
        }
    }

    /** Identity & travel documents (crew_documents) — drives the reminder engine. */
    protected function addDocuments(CrewProfile $crew, Rank $rank, int $tier, bool $makeExpired): void
    {
        $docs = [];
        $docs[] = ['Passport', $crew->passport_no, null, 10];
        $docs[] = ['CDC (Seaman Book)', $crew->cdc_no, null, 5];
        $docs[] = ['Medical Fitness Certificate', 'MED-'.random_int(10000, 99999), null, 2];
        $docs[] = ['Yellow Fever Vaccination', 'YF-'.random_int(10000, 99999), null, 10];

        if ($this->isOfficer($tier)) {
            $docs[] = ['Certificate of Competency (COC)', $crew->coc_no, $this->cocGrade($rank), 5];
            if ($rank->department === 'Deck') {
                $docs[] = ['GMDSS Operator Licence', 'GOC-'.random_int(1000, 9999), 'GOC', 5];
            }
        } else {
            $docs[] = ['Certificate of Proficiency (Watchkeeping)', 'COP-'.random_int(1000, 9999), $rank->rank_name, 5];
        }

        // how many to force-expire on this marine
        $expiredLeft = $makeExpired ? random_int(2, 3) : 0;

        foreach ($docs as [$type, $number, $grade, $validity]) {
            $expireThis = false;
            if ($expiredLeft > 0 && in_array($type, ['Passport','Medical Fitness Certificate','CDC (Seaman Book)','Certificate of Competency (COC)'])) {
                $issue  = Carbon::now()->subYears($validity)->subMonths(random_int(1, 18));
                $expiry = (clone $issue)->addYears($validity);
                $expireThis = true;
                $expiredLeft--;
            } else {
                $issue  = Carbon::now()->subMonths(random_int(3, max(4, $validity * 12 - 6)));
                $expiry = (clone $issue)->addYears($validity);
            }
            $status = $expiry->lt(now()->startOfDay()) ? 'expired'
                : ($expiry->lte(now()->addDays(30)) ? 'expiring' : 'valid');

            $crew->documents()->create([
                'doc_type'          => $type,
                'number'            => $number,
                'grade'             => $grade,
                'issue_date'        => $issue->toDateString(),
                'expiry_date'       => $expiry->toDateString(),
                'place_of_issue'    => 'Chattogram, Bangladesh',
                'issuing_authority' => str_contains($type, 'Passport') ? 'Department of Immigration & Passports' : 'Department of Shipping, Bangladesh',
                'status'            => $status,
            ]);
        }
    }

    protected function cocGrade(Rank $rank): string
    {
        return match ($rank->rank_name) {
            'Master'          => 'Master Mariner (Class-1, Deck)',
            'Chief Officer'   => 'Chief Mate (Class-2, Deck)',
            '2nd Officer', '3rd Officer', 'Junior Officer' => 'OOW / Class-3 (Deck)',
            'Chief Engineer'  => 'MEO Class-1 (Motor)',
            '2nd Engineer'    => 'MEO Class-2 (Motor)',
            '3rd Engineer', '4th Engineer', 'Junior Engineer' => 'MEO Class-4 / EOW (Motor)',
            'Electro-Technical Officer (ETO)', 'Electrical Engineer' => 'ETO (Reg. III/6)',
            default           => $rank->rank_name,
        };
    }

    /** Sea service voyages appropriate to the rank/seniority. */
    protected function addSeaService(CrewProfile $crew, Rank $rank, int $tier): void
    {
        $voyages = [3, 4, 6, 9][$tier];                       // seniors have more history
        $voyages = max(2, $voyages + random_int(-1, 2));
        $onboard = $crew->availability === 'onboard';

        // walk backwards in time from ~now
        $cursor = Carbon::now()->subMonths(random_int(1, 6));
        // rank progression: junior ranks for older voyages
        for ($v = 0; $v < $voyages; $v++) {
            $durDays = random_int(120, 270);
            $signOff = (clone $cursor);
            $signOn  = (clone $signOff)->subDays($durDays);

            $openNow = ($v === 0 && $onboard);                // current, still onboard
            $servedRank = $v === 0 ? $rank->rank_name : $this->juniorRank($rank, $v);

            $crew->seaServices()->create([
                'company_name'   => $this->companies[($v + $crew->id) % count($this->companies)],
                'vessel_name'    => $this->vesselNames[($v * 2 + $crew->id) % count($this->vesselNames)],
                'vessel_type'    => $this->vesselTypes[($v + $crew->id) % count($this->vesselTypes)],
                'grt'            => (string) (random_int(8, 120) * 1000),
                'engine_type'    => $rank->department === 'Engine' ? ['MAN B&W','Wartsila','Sulzer'][random_int(0,2)] : 'Diesel',
                'bhp'            => (string) (random_int(9000, 25000)),
                'flag'           => $this->flags[($v + $crew->id) % count($this->flags)],
                'trading_area'   => $this->tradingAreas[($v + $crew->id) % count($this->tradingAreas)],
                'rank'           => $servedRank,
                'owner'          => $this->companies[($v + 3 + $crew->id) % count($this->companies)],
                'sign_on'        => $signOn->toDateString(),
                'sign_off'       => $openNow ? null : $signOff->toDateString(),
                'duration_days'  => $openNow ? null : $durDays,
                'reason_sign_off'=> $openNow ? null : ['Contract completed','Owner change','Leave','Promotion'][random_int(0, 3)],
                'source'         => 'manual',
            ]);

            // step further back with a small gap (leave between contracts)
            $cursor = (clone $signOn)->subDays(random_int(30, 120));
        }
    }

    /** A plausible more-junior rank for older voyages (same department). */
    protected function juniorRank(Rank $rank, int $stepsBack): string
    {
        $deck   = ['Deck Cadet','3rd Officer','2nd Officer','Chief Officer','Master'];
        $engine = ['Engine Cadet','4th Engineer','3rd Engineer','2nd Engineer','Chief Engineer'];
        $ratingsDeck = ['Ordinary Seaman (OS)','Able Seaman (AB)','Bosun'];
        $ratingsEng  = ['Wiper','Oiler','Fitter'];

        $ladder = match (true) {
            in_array($rank->rank_name, $deck)   => $deck,
            in_array($rank->rank_name, $engine) => $engine,
            $rank->department === 'Deck'        => $ratingsDeck,
            $rank->department === 'Engine'      => $ratingsEng,
            default                             => [$rank->rank_name],
        };
        $idx = array_search($rank->rank_name, $ladder);
        if ($idx === false) return $rank->rank_name;
        $target = max(0, $idx - $stepsBack);
        return $ladder[$target];
    }

    protected function addBank(CrewProfile $crew): void
    {
        $banks = ['Islami Bank Bangladesh','Dutch-Bangla Bank','BRAC Bank','Sonali Bank','City Bank','Pubali Bank'];
        $crew->bankAccounts()->create([
            'bank_name'      => $banks[$crew->id % count($banks)],
            'branch'         => $this->ports[$crew->id % count($this->ports)].' Branch',
            'account_name'   => $crew->name,
            'account_number' => (string) random_int(1000000000000, 9999999999999),
            'routing_number' => (string) random_int(100000000, 999999999),
            'is_own_account' => true,
            'is_primary'     => true,
        ]);
    }
}
