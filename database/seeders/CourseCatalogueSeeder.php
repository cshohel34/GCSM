<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $courses = [
            ['ADVANCED FIRE FIGHTING', 'AFF', 'Ancillary Courses'],
            ['ADVANCED TRAINING FOR CHEMICAL TANKER CARGO OPERATIONS', 'ACT', 'Ancillary Courses'],
            ['ADVANCED TRAINING FOR LIQUEFIED GAS TANKER CARGO OPERATIONS', 'AGT', 'Ancillary Courses'],
            ['ADVANCED TRAINING FOR OIL TANKER CARGO OPERATIONS', 'AOT', 'Ancillary Courses'],
            ['BANGLADESH MERCHANT SHIPPING ORDINANCE', 'BMSO', 'Ancillary Courses'],
            ['BASIC TRAINING FOR LIQUEFIED GAS TANKER CARGO OPERATIONS', 'BOGT', 'Ancillary Courses'],
            ['BASIC TRAINING FOR OIL AND CHEMICAL TANKER CARGO OPERATIONS', 'BOCT', 'Ancillary Courses'],
            ['Bridge Resource Management & Application Of Leadership And Managerial Skills', 'BRMLSM-ML', 'Ancillary Courses'],
            ['Bridge Resource Management &Application Of Leadership And Team Working Skills', 'BRMLSM-OL', 'Ancillary Courses'],
            ['COMPANY SECURITY OFFICER', 'CSO', 'Ancillary Courses'],
            ['EFFICIENT DECK HAND', 'EDH', 'Ancillary Courses'],
            ['Electronic Chart Display and Information Systems (ECDIS)', 'ECDIS', 'Ancillary Courses'],
            ['ELECTRONIC NAVIGATION SYSTEM', 'ENS', 'Ancillary Courses'],
            ['ELEMENTARY FIRST AID', 'EFA', 'Ancillary Courses'],
            ['Engine Room Simulator', 'ERS', 'Ancillary Courses'],
            ['FIRE PREVENTION AND FIRE FIGHTING', 'FPFF', 'Ancillary Courses'],
            ['GMDSS General Operator\'s Certificate (GOC) (Operation Level)', 'GMDSS', 'Ancillary Courses'],
            ['HIGH VOLTAGE SYSTEM (MANAGEMENT LEVEL)', 'HVS(ML)', 'Ancillary Courses'],
            ['HIGH VOLTAGE SYSTEM (OPERATION LEVEL)', 'HVS(OL)', 'Ancillary Courses'],
            ['MEDICAL CARE', 'MC', 'Ancillary Courses'],
            ['MEDICAL FIRST AID', 'MFA', 'Ancillary Courses'],
            ['PERSONAL SAFETY AND SOCIAL RESPONSIBILITIES', 'PSSR', 'Ancillary Courses'],
            ['PERSONAL SURVIVAL TECHNIQUES', 'PST', 'Ancillary Courses'],
            ['PROFICIENCY IN MARITIME TRAINING AND ASSESSMENT', 'PMTA', 'Ancillary Courses'],
            ['PROFICIENCY IN SURVIVAL CRAFT & RESCUE BOATS OTHER THAN FAST RESCUE BOATS', 'PSC&RB', 'Ancillary Courses'],
            ['Radar Observer\'s Course ARPA (Operation Level)', 'ROC ARPA', 'Ancillary Courses'],
            ['RATING AS ABLE SEAFARER DECK', 'RASD', 'Ancillary Courses'],
            ['RATING AS ABLE SEAFARER ENGINE', 'RASE', 'Ancillary Courses'],
            ['RATING FORMING PART OF A NAVIGATIONAL WATCH', 'NWR', 'Ancillary Courses'],
            ['RATING FORMING PART OF AN ENGINEERING WATCH', 'EWR', 'Ancillary Courses'],
            ['SEAFARER WITH DESIGNATED SECURITY DUTIES', 'DSD', 'Ancillary Courses'],
            ['SECURITY AWARENESS', 'SAT', 'Ancillary Courses'],
            ['Ship Handling Simulator Course', 'SHS', 'Ancillary Courses'],
            ['SHIP SECURITY OFFICER', 'SSO', 'Ancillary Courses'],
            ['TANKER FIRE FIGHTING', 'TFF', 'Ancillary Courses'],
            ['PRE-SEA (DECK) RATING TRAINING CERTIFICATE (03 Months Rating Course for Training ID / Supernumerary / Hyundai CDC Holder)', 'IDTOCDC(DECK)', 'ID to CDC'],
            ['PRE-SEA (ELECTRICIAN) RATING TRAINING CERTIFICATE (03 Months Rating Course for Training ID / Supernumerary / Hyundai CDC Holder)', 'IDTOCDC(ELECTRICIAN)', 'ID to CDC'],
            ['PRE-SEA (ENGINE) RATING TRAINING CERTIFICATE (03 Months Rating Course for Training ID / Supernumerary / Hyundai CDC Holder)', 'IDTOCDC(ENGINE)', 'ID to CDC'],
            ['PRE-SEA (FITTER-CUM-WELDER) RATING TRAINING CERTIFICATE (03 Months Rating Course for Training ID / Supernumerary / Hyundai CDC Holder)', 'IDTOCDC(FITTER)', 'ID to CDC'],
            ['PRE-SEA RATING TRAINING CERTIFICATE IN SHIP\'S CATERING SERVICES (STEWARD) (03 Months Rating Course for Training ID / Supernumerary / Hyundai CDC Holder)', 'IDTOCDC(STEWARD)', 'ID to CDC'],
            ['PRE-SEA RATING TRAINING CERTIFICATE IN SHIPS CATERING SERVICES (COOK) (03 Months Rating Course for Training / Supernumerary / Hyundai CDC Holder)', 'IDTOCDC(COOK)', 'ID to CDC'],
            ['PRE-SEA (DECK) RATING TRAINING CERTIFICATE', 'DR', 'Long Courses'],
            ['PRE-SEA (ELECTRICIAN) RATING TRAINING CERTIFICATE', '', 'Long Courses'],
            ['PRE-SEA (ENGINE) RATING TRAINING CERTIFICATE', 'ER', 'Long Courses'],
            ['PRE-SEA (FITTER-CUM-WELDER) RATING TRAINING CERTIFICATE', 'FCW', 'Long Courses'],
            ['PRE-SEA ENGINEERING TRAINING COURSE (ENGINE CADET)', 'EC', 'Long Courses'],
            ['PRE-SEA JUNIOR MARINE ELECTRO-TECHNICAL OFFICER COURSE', 'JMETO', 'Long Courses'],
            ['PRE-SEA NAUTICAL TRAINING COURSE (DECK CADET)', 'DC', 'Long Courses'],
            ['PRE-SEA RATING TRAINING CERTIFICATE IN SHIP\'S CATERING SERVICES (STEWARD)', 'SR', 'Long Courses'],
            ['PRE-SEA RATING TRAINING CERTIFICATE IN SHIPS CATERING SERVICES (COOK)', 'COOK', 'Long Courses'],
            ['Practical Training Effective For Advance Fire Fighting', '', 'Practical Courses'],
            ['Practical Training Effective For Basic Training In Fire Prevention And Fire Fighting', '', 'Practical Courses'],
            ['Practical Training Effective For Personal Survival Techniques', '', 'Practical Courses'],
            ['Practical Training Effective For Proficiency In Survival Craft & Rescue Boats Other Than Fast Rescue Boats', '', 'Practical Courses'],
            ['Preparatory Course Fishing Vessel Mate (Phase - 1)', 'FVM', 'Preparatory Courses'],
            ['Preparatory Course Fishing Vessel Skipper (Phase - 2)', 'FVS', 'Preparatory Courses'],
            ['Preparatory Course For Chief Engineer Officer And Second Engineer Officer (Combined) On Ships Powered By Main Propulsion Machinery Of 750 Kw Propulsion Power Or More (Reg.Iii/2 & Reg.Iii/3) (Part A)', 'MEOC-2&1 (P-A)', 'Preparatory Courses'],
            ['Preparatory Course For Chief Engineer Officer And Second Engineer Officer (Combined) On Ships Powered By Main Propulsion Machinery Of 750 Kw Propulsion Power Or More (Reg.Iii/2 & Reg.Iii/3) (Part B)', 'MEOC-2&1 (P-B)', 'Preparatory Courses'],
            ['Preparatory Course For Chief Engineer Officer And Second Engineer Officer (Combined) On Ships Powered By Main Propulsion Machinery Of 750 Kw Propulsion Power Or More (Reg.Iii/2 & Reg.Iii/3) (Part B)', 'MEOC-2&1 (P-B)', 'Preparatory Courses'],
            ['Preparatory Course For Electro-Technical Officer', 'METO', 'Preparatory Courses'],
            ['Preparatory Course For Endorsement Examination As Chief Engineer On Ships Of Less Than 3,000 Kw Main Propulsion Power (For Bangladesh Coastal Vessel)', 'MEOC-5&4', 'Preparatory Courses'],
            ['Preparatory Course For Engineer Officer In Charge of A Watch In A Manned Engine-Room Or Designated Duty Engineer Officer In A Periodically Unmanned Engine-Room On A Seagoing Ship Powered By Main Propulsion Machinery Of 750 Kw Propulsion Power Or More (Part B)', 'MEOC-3(P-B)', 'Preparatory Courses'],
            ['Preparatory Course For Engineer Officer In Charge Of A Watch In A Manned Engine-Room Or Designated Duty Engineer Officer In A Periodically Unmanned Engine-Room On A Seagoing Ship Powered By Main Propulsion Machinery Of 750 Kw Propulsion Power Or More (Part A)', 'MEOC-3(P-A)', 'Preparatory Courses'],
            ['Preparatory Course For Master And Chief Mate (Combined) On Ships Of 500 Gross Tonnage Or More', 'DOC-2&1', 'Preparatory Courses'],
            ['Preparatory Course For Navigational Watch Keeping Officer And Master (Combined) On Ships Of Less Than 500 Gross Tonnage On Near Coastal Voyage (For Bangladesh Coastal Vessel))', 'DOC-5&4', 'Preparatory Courses'],
            ['Preparatory Course For Navigational Watch Keeping Officer On Ships Of 500 Gross Tonnage Or More', 'DOC-3', 'Preparatory Courses'],
            ['REFRESHER IN FIRE PREVENTION AND FIRE FIGHTING', 'FPFF(REFRESHER)', 'Refresher Courses'],
            ['REFRESHER IN MEDICAL CARE', 'MC(REFRESHER)', 'Refresher Courses'],
            ['REFRESHER IN MEDICAL FIRST AID', 'MFA(REFRESHER)', 'Refresher Courses'],
            ['REFRESHER IN PERSONAL SURVIVAL TECHNIQUES', 'PST(REFRESHER)', 'Refresher Courses'],
            ['REFRESHER IN PROFICIENCY IN SURVIVAL CRAFT AND RESCUE BOATS OTHER THAN FAST RESCUE BOATS', 'PSC&RB(REFRESHER)', 'Refresher Courses'],
            ['REFRESHER ON ADVANCED FIRE FIGHTING', 'AFF(REFRESHER)', 'Refresher Courses'],
            ['Refresher Training in EFA', 'EFA(REFRESHER)', 'Refresher Courses'],
            ['REFRESHER/UPDATING IN PERSONAL SAFETY AND SOCIAL RESPONSIBILITIES', 'PSSR(REFRESHER)', 'Refresher Courses'],
            ['REFRESHER/UPGRADATION COURSES, ELECTRO-TECHNICAL OFFICER', 'ETO (REFRESHER)', 'Refresher Courses'],
            ['UPGRADATION COURSES, DECK OFFICER (MANAGEMENT LEVEL) - CLASS -2/1', 'UPGDECKML', 'Refresher Courses'],
            ['UPGRADATION COURSES, DECK OFFICER (OPERATIONAL LEVEL) - CLASS-3', 'UPGDECKOL', 'Refresher Courses'],
            ['UPGRADATION COURSES, ENGINEER OFFICER (MANAGEMENT LEVEL) - CLASS - 2/1', 'UPGENGML', 'Refresher Courses'],
            ['UPGRADATION COURSES, ENGINEER OFFICER (OPERATIONAL LEVEL) - CLASS - 3', 'UPGENGOL', 'Refresher Courses'],
            ['Hydrogen Sulphide (H2S) Safety', 'H2S', 'Special'],
            ['PERSONAL SAFETY AND SOCIAL RESPONSIBILITIES - UPGRADATION COURSE', 'PSSR(UPGRADATION)', 'Upgradation'],
            ['Machine Shop Practice', 'PTMSP', 'Workshop'],
            ['Marine & Mechanical Shop Practice', 'PTMMSP', 'Workshop'],
        ];
        foreach ($courses as [$name, $code, $cat]) {
            DB::table('course_catalogue')->updateOrInsert(
                ['course_name' => $name],
                ['code' => $code ?: null, 'category' => $cat, 'active' => true, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
