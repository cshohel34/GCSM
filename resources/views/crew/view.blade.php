@extends('layouts.app')
@section('title', $crew->name)
@section('actions')
    <a href="{{ route('crew.cv.pdf', $crew) }}" title="Download CV as PDF (GCSM format)" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition mr-1">⬇ CV PDF</a>
    <a href="{{ route('crew.cv.excel', $crew) }}" title="Download CV as Excel" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition mr-1">⬇ CV Excel</a>
    <a href="{{ route('crew.salary', $crew) }}" title="View salary history" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition mr-1">Salary history</a>
    @can('crew.edit')<a href="{{ route('crew.editprofile', $crew) }}" title="Edit this profile" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-sm px-4 py-1.5 hover:bg-[#2E74B5] transition">✎ Edit Profile</a>@endcan
    @role('Super Admin')<button type="button" onclick="document.getElementById('deleteCrewModal').classList.remove('hidden');document.getElementById('deleteCrewModal').classList.add('flex');" title="Delete this crew profile (Super Admin only)" class="inline-flex items-center gap-1 rounded-md border border-red-300 text-red-600 font-semibold text-sm px-3 py-1.5 hover:bg-red-50 transition ml-1">🗑 Delete</button>@endrole
@endsection
@section('content')

<style>
@keyframes gcsmBlink { 0%,100% { background-color:#fef9c3; } 50% { background-color:#fde047; } }
.gcsm-incomplete { animation: gcsmBlink 1.1s ease-in-out infinite; }
</style>

@php
    $dash = fn ($v) => ($v !== null && $v !== '') ? $v : '—';
    $eng = fn ($v) => $v ?: '—';
@endphp

@if ($crew->offences->isNotEmpty())
    <div class="mb-4 rounded bg-amber-100 border border-amber-300 text-amber-800 px-4 py-2 text-sm">⚠ This crew has {{ $crew->offences->count() }} offence record(s). Review before selection.</div>
@endif

{{-- Crew header (scrolls away; a compact sticky bar stays on top) --}}
<div class="bg-white rounded-lg shadow p-5 mb-4">
        @php $ea = $crew->effective_availability;
             $availColors = ['available'=>'bg-emerald-50 text-emerald-700 ring-emerald-200','not_available'=>'bg-slate-100 text-slate-500 ring-slate-200','onboard'=>'bg-blue-50 text-blue-700 ring-blue-200','resting'=>'bg-amber-50 text-amber-800 ring-amber-200'];
        @endphp
        <div class="flex items-start gap-5">
            @if ($crew->photo_path)
                <img id="heroPhoto" src="{{ asset('storage/'.$crew->photo_path) }}" class="w-24 h-28 rounded-xl object-cover ring-1 ring-gold-300 shadow-sm shrink-0" alt="photo">
            @else
                <div id="heroPhoto" class="w-24 h-28 rounded-xl bg-slate-100 ring-1 ring-slate-200 flex items-center justify-center text-slate-300 text-3xl shrink-0">👤</div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="text-2xl font-bold text-navy-800 tracking-tight leading-tight">{{ $crew->name }} @if($crew->name_chinese)<span class="text-slate-400 text-lg font-medium">/ {{ $crew->name_chinese }}</span>@endif</div>
                <div class="text-sm text-slate-500 mt-1">{{ optional($crew->currentRank)->rank_name }} <span class="text-slate-300">·</span> Crew ID <span class="font-medium text-slate-600">{{ $crew->display_id }}</span></div>
                <div class="flex flex-wrap items-center gap-1.5 mt-3">
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 {{ $crew->source === 'oma' ? 'bg-blue-50 text-blue-700 ring-blue-200' : 'bg-slate-50 text-slate-600 ring-slate-200' }}">{{ strtoupper($crew->source) }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 {{ $completeness['complete'] ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-amber-50 text-amber-800 ring-amber-200' }}">{{ $completeness['complete'] ? 'COMPLETE' : 'DRAFT' }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 {{ $availColors[$ea] ?? 'bg-slate-50 text-slate-600 ring-slate-200' }}">{{ ucfirst(str_replace('_',' ',$ea)) }}</span>
                    @if ($crew->availability === 'resting' && $crew->available_from)<span class="text-[11px] text-amber-700">avail {{ $crew->available_from->toDateString() }}@if(!is_null($crew->resting_days_left)) ({{ max($crew->resting_days_left,0) }}d)@endif</span>@endif
                    @include('crew.partials.urgency', ['level' => $crew->job_urgency, 'deadline' => $crew->job_deadline])
                </div>
            </div>
            {{-- Circular completeness (gradient, colour-banded) --}}
            @php
                $pc = (int) $completeness['percent'];
                if ($pc >= 100)      { $g0='#34D399'; $g1='#059669'; $txt='text-emerald-600'; $lbl='Complete'; }
                elseif ($pc >= 50)   { $g0='#FBBF24'; $g1='#F59E0B'; $txt='text-amber-600';   $lbl='In progress'; }
                else                 { $g0='#FB7185'; $g1='#E11D48'; $txt='text-rose-600';    $lbl='Incomplete'; }
                $bmi = null; $bmiCat = ''; $bmiColor = 'text-navy-800';
                if ($crew->height_cm && $crew->weight_kg && (float)$crew->height_cm > 0) {
                    $hm = (float)$crew->height_cm / 100; $bmi = round((float)$crew->weight_kg / ($hm*$hm), 1);
                    if ($bmi < 18.5) { $bmiCat='Underweight'; $bmiColor='text-amber-600'; }
                    elseif ($bmi < 25) { $bmiCat='Normal'; $bmiColor='text-emerald-600'; }
                    elseif ($bmi < 30) { $bmiCat='Overweight'; $bmiColor='text-amber-600'; }
                    else { $bmiCat='Obese'; $bmiColor='text-rose-600'; }
                }
            @endphp
            <div class="ml-auto shrink-0 flex flex-col items-center gap-2" title="Profile {{ $pc }}% complete">
                <div class="relative w-[92px] h-[92px]">
                    <svg viewBox="0 0 36 36" class="w-[92px] h-[92px] -rotate-90">
                        <defs>
                            <linearGradient id="gcsmRing" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="{{ $g0 }}"></stop>
                                <stop offset="100%" stop-color="{{ $g1 }}"></stop>
                            </linearGradient>
                        </defs>
                        <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#eef2f7" stroke-width="3"></circle>
                        <circle cx="18" cy="18" r="15.9155" fill="none" stroke="url(#gcsmRing)" stroke-width="4"
                                stroke-linecap="round" stroke-dasharray="{{ $pc }} {{ 100 - $pc }}"
                                style="transition:stroke-dasharray .8s var(--ease,ease); filter:drop-shadow(0 2px 4px rgba(16,33,60,.18))"></circle>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center leading-none">
                        <span class="text-2xl font-extrabold text-navy-800">{{ $pc }}</span>
                        <span class="text-[9px] font-bold {{ $txt }} tracking-wider">PERCENT</span>
                    </div>
                </div>
                <div class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide {{ $pc>=100 ? 'bg-emerald-50 text-emerald-600' : ($pc>=50 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}">{{ $lbl }}</div>
            </div>
        </div>

        @php $fact = fn ($l,$v) => '<div class="min-w-0"><div class="text-[10px] uppercase tracking-wider text-slate-400">'.e($l).'</div><div class="text-sm font-semibold text-navy-800 flex items-center gap-1"><span class="truncate">'.e($v !== null && $v !== '' ? $v : '—').'</span>'.(($v !== null && $v !== '') ? '<button type="button" class="js-copy text-slate-300 hover:text-[#1F3864] shrink-0" data-copy="'.e($v).'" title="Copy">&#10697;</button>' : '').'</div></div>'; @endphp
        <div id="heroFacts" class="grid grid-cols-2 md:grid-cols-4 gap-y-4 gap-x-6 mt-5 pt-5 border-t border-slate-100">
            {!! $fact('CDC No', $crew->cdc_no) !!}
            {!! $fact('SID No', $crew->sid_no) !!}
            {!! $fact('NID', $crew->nid_no) !!}
            {!! $fact('Passport', $crew->passport_no) !!}
            {!! $fact('COC', $crew->coc_no) !!}
            {!! $fact('Birth Reg', $crew->birth_registration_no) !!}
            {!! $fact('Father', $crew->father_name) !!}
            {!! $fact('Mother', $crew->mother_name) !!}
            {!! $fact('Mobile', $crew->mobile) !!}
            {!! $fact('Email', $crew->email) !!}
            {!! $fact('Date of birth', optional($crew->date_of_birth)->toDateString()) !!}
            <div class="min-w-0"><div class="text-[10px] uppercase tracking-wider text-slate-400">BMI</div><div class="text-sm font-semibold {{ $bmiColor }}">{{ $bmi ? $bmi.' · '.$bmiCat : '—' }}</div></div>
        </div>
</div>

{{-- Tabs (in-flow; fades out as the fixed bar takes over) --}}
<div id="flowTabs" class="mb-4 transition-opacity duration-300">
    <div class="js-tabs flex gap-1 overflow-x-auto bg-white rounded-xl shadow p-2">
        <button type="button" class="ptab" data-tab="personal">Personal Details</button>
        <button type="button" class="ptab" data-tab="maritime">Maritime Education</button>
        <button type="button" class="ptab" data-tab="education">General Education</button>
        <button type="button" class="ptab" data-tab="documents">Professional &amp; Travel Documents</button>
        <button type="button" class="ptab" data-tab="certs">Certificates</button>
        <button type="button" class="ptab" data-tab="sea">Sea Service</button>
        <button type="button" class="ptab" data-tab="bank">Bank Account Details</button>
        <button type="button" class="ptab" data-tab="voyages">Placement History</button>
        <button type="button" class="ptab" data-tab="offence">Offences</button>
        <button type="button" class="ptab" data-tab="notes">Notes</button>
        <button type="button" class="ptab" data-tab="reminders">Reminders</button>
        <button type="button" class="ptab" data-tab="editlog">Edit Logs</button>
    </div>
</div>

{{-- Fixed condensed crew bar: appears smoothly on scroll (summary + tabs) --}}
<div id="miniBar" class="fixed top-[57px] left-64 right-0 z-30 bg-white border-b border-slate-200 shadow-md px-6 pt-2 pb-1.5">
    {{-- Summary row --}}
    <div class="flex items-center gap-x-4 gap-y-1 flex-wrap text-sm">
        @if ($crew->photo_path)
            <img src="{{ asset('storage/'.$crew->photo_path) }}" class="w-10 h-10 rounded-lg object-cover ring-1 ring-gold-300 shrink-0" alt="">
        @else
            <div class="w-10 h-10 rounded-lg bg-slate-100 ring-1 ring-slate-200 flex items-center justify-center text-slate-300 shrink-0">👤</div>
        @endif
        <div class="leading-tight shrink-0">
            <div class="text-sm font-bold text-navy-800">{{ $crew->name }}</div>
            <div class="text-[11px] text-slate-500">{{ optional($crew->currentRank)->rank_name }} · {{ $crew->display_id }}</div>
        </div>
        <div class="w-px h-8 bg-slate-200 shrink-0 hidden md:block"></div>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[12px]">
            <span><span class="text-slate-400 text-[10px] uppercase">CDC</span> <span class="font-medium text-navy-800">{{ $crew->cdc_no ?: '—' }}</span></span>
            <span><span class="text-slate-400 text-[10px] uppercase">Phone</span> <span class="font-medium text-navy-800">{{ $crew->mobile ?: '—' }}</span></span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 {{ $availColors[$ea] ?? 'bg-slate-50 text-slate-600 ring-slate-200' }}">{{ ucfirst(str_replace('_',' ',$ea)) }}</span>
            @include('crew.partials.urgency', ['level' => $crew->job_urgency, 'deadline' => $crew->job_deadline])
        </div>
        <div class="ml-auto flex items-center gap-2 shrink-0">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-[11px] font-bold {{ $pc>=100 ? 'bg-emerald-50 text-emerald-600' : ($pc>=50 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}" title="Profile {{ $pc }}% complete" style="background-image:conic-gradient({{ $g1 }} {{ $pc }}%, #eef2f7 0)">
                <span class="w-7 h-7 rounded-full bg-white flex items-center justify-center">{{ $pc }}</span>
            </div>
        </div>
    </div>
    {{-- Tabs row --}}
    <div class="js-tabs flex gap-1 overflow-x-auto mt-1.5 pt-1.5 border-t border-slate-100">
        <button type="button" class="ptab" data-tab="personal">Personal Details</button>
        <button type="button" class="ptab" data-tab="maritime">Maritime Education</button>
        <button type="button" class="ptab" data-tab="education">General Education</button>
        <button type="button" class="ptab" data-tab="documents">Professional &amp; Travel Documents</button>
        <button type="button" class="ptab" data-tab="certs">Certificates</button>
        <button type="button" class="ptab" data-tab="sea">Sea Service</button>
        <button type="button" class="ptab" data-tab="bank">Bank Account Details</button>
        <button type="button" class="ptab" data-tab="voyages">Placement History</button>
        <button type="button" class="ptab" data-tab="offence">Offences</button>
        <button type="button" class="ptab" data-tab="notes">Notes</button>
        <button type="button" class="ptab" data-tab="reminders">Reminders</button>
        <button type="button" class="ptab" data-tab="editlog">Edit Logs</button>
    </div>
</div>

{{-- Placement History panel --}}
<div class="tab-panel hidden" data-panel="voyages">
    @include('crew.partials.voyages', ['editing' => false])
    <div class="mt-4">@include('crew.partials.status_log')</div>
</div>



@php
    $field = function ($label, $value) use ($dash) {
        $has = $value !== null && $value !== '';
        $copy = $has ? '<button type="button" class="js-copy text-slate-300 hover:text-[#1F3864] shrink-0" data-copy="'.e($value).'" title="Copy">&#10697;</button>' : '';
        return '<div><div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">'.e($label).'</div><div class="text-sm font-medium text-navy-800 flex items-start gap-1"><span class="min-w-0 break-words">'.e($dash($value)).'</span>'.$copy.'</div></div>';
    };
@endphp

{{-- Personal & CV details --}}
<div id="sec-personal" data-panel="personal" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <div class="gcsm-head"><h3 class="font-semibold text-sm md:text-base">Personal &amp; CV details</h3></div>

    <div class="space-y-4">
        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
            <div class="text-[11px] font-semibold text-navy-700 uppercase tracking-wider mb-3">Personal &amp; physical</div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-4">
                {!! $field('Place of birth', $crew->place_of_birth) !!}
                {!! $field('Nationality', $crew->nationality) !!}
                {!! $field('Religion', $crew->religion) !!}
                {!! $field('Gender', $crew->gender) !!}
                {!! $field('Marital status', $crew->marital_status) !!}
                {!! $field('Blood group', $crew->blood_group) !!}
                {!! $field('Shoe size (EU)', $crew->shoe_size) !!}
                {!! $field('Height (cm)', $crew->height_cm) !!}
                {!! $field('Weight (kg)', $crew->weight_kg) !!}
                {!! $field('Emergency contact', $crew->emergency_contact) !!}
            </div>
        </div>

        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
            <div class="text-[11px] font-semibold text-navy-700 uppercase tracking-wider mb-3">Addresses</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                {!! $field('Present address', $crew->present_address) !!}
                {!! $field('Permanent address', $crew->permanent_address) !!}
            </div>
        </div>

        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
            <div class="text-[11px] font-semibold text-navy-700 uppercase tracking-wider mb-3">Next of kin</div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-4">
                {!! $field('Name', $crew->next_of_kin_name) !!}
                {!! $field('Relation', $crew->next_of_kin_relation) !!}
                {!! $field('Contact number', $crew->next_of_kin_contact) !!}
                {!! $field('Address', $crew->next_of_kin_address) !!}
            </div>
        </div>

        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
            <div class="text-[11px] font-semibold text-navy-700 uppercase tracking-wider mb-3">English level</div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-4">
                {!! $field('Listening', $crew->english_listening) !!}
                {!! $field('Spoken', $crew->english_speaking) !!}
                {!! $field('Reading', $crew->english_reading) !!}
                {!! $field('Writing', $crew->english_writing) !!}
            </div>
        </div>
    </div>
</div>

{{-- Renewal reminders / documents summary --}}
<div id="sec-reminders" data-panel="reminders" class="tab-panel hidden bg-white rounded-lg shadow p-4 mb-4">
    <div class="gcsm-head justify-between">
        <h3 class="font-semibold text-sm md:text-base">Reminders &amp; Documents</h3>
        <a href="{{ route('notifications.index', ['crew' => $crew->id]) }}" class="text-xs font-semibold rounded-md bg-[#1F3864] text-white px-3 py-1.5 hover:bg-[#2E4A7A]">See all &rarr;</a>
    </div>
    <div class="flex flex-wrap items-center gap-x-10 gap-y-3 text-sm">
        <div>
            <div class="text-slate-400 text-xs">Renewal reminders sent</div>
            <div class="text-2xl font-bold text-navy-700">{{ $reminderCount }}</div>
        </div>
        <div>
            <div class="text-slate-400 text-xs mb-1">Documents &amp; Certificates</div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">{{ $reminderStats['valid'] }} valid</span>
                <span class="px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-700">{{ $reminderStats['expiring'] }} expiring</span>
                <span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-700">{{ $reminderStats['expired'] }} expired</span>
            </div>
        </div>
    </div>
</div>

@php
    $sectionCard = function ($title) { return '<div class="gcsm-head"><h3 class="font-semibold text-sm md:text-base">'.$title.'</h3></div>'; };
@endphp

{{-- Maritime Education --}}
<div id="sec-maritime" data-panel="maritime" class="tab-panel hidden bg-white rounded-lg shadow p-4 mb-4">
    {!! $sectionCard('Maritime Education Details') !!}
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left"><tr><th class="py-1">Institute</th><th>Department</th><th>Year of Graduation</th><th></th></tr></thead>
        <tbody>
        @forelse ($crew->maritimeEducations as $row)
            @include('crew.partials.maritime_row', ['editing' => false])
        @empty <tr><td colspan="4" class="py-3 text-slate-400">None recorded.</td></tr> @endforelse
        </tbody>
    </table>
</div>

{{-- Educational Qualification --}}
<div id="sec-education" data-panel="education" class="tab-panel hidden bg-white rounded-lg shadow p-4 mb-4">
    {!! $sectionCard('Educational Qualification') !!}
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left"><tr><th class="py-1">Description</th><th>Board</th><th>Group</th><th>Passing Year</th><th>GPA</th><th></th></tr></thead>
        <tbody>
        @forelse ($crew->academics as $row)
            @include('crew.partials.academic_row', ['editing' => false])
        @empty <tr><td colspan="6" class="py-3 text-slate-400">None recorded.</td></tr> @endforelse
        </tbody>
    </table>
</div>

{{-- Qualification & Travel Documents --}}
<div id="sec-documents" data-panel="documents" class="tab-panel hidden bg-white rounded-lg shadow p-4 mb-4">
    {!! $sectionCard('Professional &amp; Travel Documents') !!}
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left"><tr><th class="py-1">Type</th><th>Number</th><th>Issue</th><th>Expiry</th><th>Status</th><th>File</th><th></th></tr></thead>
        <tbody>
        @forelse ($crew->documents as $row)
            @include('crew.partials.document_row', ['editing' => false])
        @empty <tr><td colspan="6" class="py-3 text-slate-400">None recorded.</td></tr> @endforelse
        </tbody>
    </table>
</div>

{{-- Course Certificates --}}
<div id="sec-certs" data-panel="certs" class="tab-panel hidden bg-white rounded-lg shadow p-4 mb-4">
    {!! $sectionCard('Course Certificates') !!}
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left"><tr><th class="py-1">Category</th><th>Course Code</th><th>Cert No</th><th>Issue</th><th>Expiry</th><th>Issuer</th><th>Authority</th><th>Src</th><th>File</th><th></th></tr></thead>
        <tbody>
        @forelse ($crew->courses as $row)
            @include('crew.partials.course_row', ['editing' => false])
        @empty <tr><td colspan="9" class="py-3 text-slate-400">None recorded.</td></tr> @endforelse
        </tbody>
    </table>
</div>

{{-- Sea Service --}}
<div id="sec-sea" data-panel="sea" class="tab-panel hidden bg-white rounded-lg shadow p-4 mb-4">
    {!! $sectionCard('Sea Service / Experience') !!}
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left"><tr><th class="py-1">Vessel</th><th>IMO</th><th>Type</th><th>Rank</th><th>Company</th><th>On</th><th>Off</th><th>Days</th><th></th></tr></thead>
        <tbody>
        @forelse ($crew->seaServices as $row)
            @include('crew.partials.sea_row', ['editing' => false])
        @empty <tr><td colspan="9" class="py-3 text-slate-400">None recorded.</td></tr> @endforelse
        </tbody>
    </table>
</div>

{{-- Bank --}}
<div id="sec-bank" data-panel="bank" class="tab-panel hidden bg-white rounded-lg shadow p-4 mb-4">
    <div class="gcsm-head"><h3 class="font-semibold text-sm md:text-base">Bank Account Details</h3></div>
    <div class="space-y-3">
    @forelse ($crew->bankAccounts as $b)
        @include('crew.partials.bank_card', ['b' => $b, 'editable' => false])
    @empty <div class="text-slate-400 text-sm">None.</div> @endforelse
    </div>
</div>

{{-- Offences --}}
<div id="sec-offence" data-panel="offence" class="tab-panel hidden bg-white rounded-lg shadow p-4 mb-4">
    <div class="gcsm-head"><h3 class="font-semibold text-sm md:text-base">Offence records</h3></div>
    @forelse ($crew->offences as $o)
        <div class="text-sm border-t py-2">{{ optional($o->offence_date)->toDateString() }} — {{ $o->description }}</div>
    @empty <div class="text-slate-400 text-sm">None.</div> @endforelse
</div>

{{-- Notes --}}
<div id="sec-notes" data-panel="notes" class="tab-panel hidden bg-white rounded-lg shadow p-4 mb-4">
    <div class="gcsm-head"><h3 class="font-semibold text-sm md:text-base">Notes</h3></div>
    @forelse ($crew->notes as $n)
        <div class="text-sm border-t py-2">{{ $n->note }}<br><span class="text-slate-400 text-xs">{{ optional($n->author)->name }} · {{ $n->created_at->diffForHumans() }}</span></div>
    @empty <div class="text-slate-400 text-sm">None.</div> @endforelse
</div>

{{-- Edit Log --}}
<div id="sec-editlog" data-panel="editlog" class="tab-panel hidden">
    @include('crew.partials.edit_log')
</div>

<script>
(function () {
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.js-tabs .ptab'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('.tab-panel'));
    if (!tabs.length) return;

    function show(name) {
        tabs.forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-tab') === name); });
        panels.forEach(function (p) { p.classList.toggle('hidden', p.getAttribute('data-panel') !== name); });
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { show(t.getAttribute('data-tab')); }); });
    show('personal');   // first tab open by default

    // Reveal the condensed crew bar on scroll (transform+opacity only → smooth, no reflow).
    // The in-flow tabs fade out at the same time so tabs never appear twice.
    var mini = document.getElementById('miniBar');
    var flow = document.getElementById('flowTabs');
    if (mini) {
        var ticking = false;
        function upd() {
            var y = window.pageYOffset || document.documentElement.scrollTop;
            var on = y > 150;
            mini.classList.toggle('show', on);
            if (flow) {
                flow.style.opacity = on ? '0' : '1';
                flow.style.pointerEvents = on ? 'none' : 'auto';
            }
            ticking = false;
        }
        window.addEventListener('scroll', function () {
            if (!ticking) { window.requestAnimationFrame(upd); ticking = true; }
        }, { passive: true });
        upd();
    }
})();
</script>

@role('Super Admin')
{{-- Delete confirmation modal (Super Admin only) — themed to the GCSM navy/gold template --}}
<div id="deleteCrewModal" class="hidden fixed inset-0 z-[100] items-center justify-center bg-[#12233F]/60 backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden ring-1 ring-slate-200">
        <div class="px-5 py-4 bg-gradient-to-r from-[#1F3864] to-[#12233F] flex items-center gap-3">
            <span class="w-9 h-9 rounded-full bg-red-500/20 text-red-200 flex items-center justify-center text-lg">🗑</span>
            <div>
                <div class="text-white font-bold leading-tight">Delete crew profile</div>
                <div class="text-[11px] text-gold-300">Super Admin authorisation required</div>
            </div>
        </div>
        <form method="POST" action="{{ route('crew.destroy', $crew) }}" class="p-5 space-y-4">
            @csrf
            @method('DELETE')
            <p class="text-sm text-slate-600">
                You are about to delete <span class="font-semibold text-navy-800">{{ $crew->name }}</span>
                (<span class="font-mono text-xs">{{ $crew->display_id }}</span>).
                The profile will be moved to the <span class="font-semibold">Recycle Bin</span> and can be restored later — it is not permanently erased.
            </p>
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Enter your account password to confirm</label>
                <input type="password" name="password" required autocomplete="off"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864] @error('password') border-red-400 ring-1 ring-red-300 @enderror"
                       placeholder="••••••••">
                @error('password')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="button" onclick="document.getElementById('deleteCrewModal').classList.add('hidden');document.getElementById('deleteCrewModal').classList.remove('flex');"
                        class="rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-4 py-1.5 hover:bg-slate-100">Cancel</button>
                <button type="submit"
                        class="rounded-md bg-red-600 text-white font-semibold text-sm px-4 py-1.5 hover:bg-red-700">Delete profile</button>
            </div>
        </form>
    </div>
</div>
@if ($errors->has('password'))
<script>
    (function () {
        var m = document.getElementById('deleteCrewModal');
        if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
    })();
</script>
@endif
@endrole
@endsection
