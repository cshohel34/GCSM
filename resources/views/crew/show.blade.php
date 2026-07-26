@extends('layouts.app')
@section('title', 'Edit — '.$crew->name)
@section('actions')
    <a href="{{ route('crew.show', $crew) }}" title="Back to the read-only profile view" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition mr-1">← View profile</a>
    <a href="{{ route('crew.cv.pdf', $crew) }}" title="Download CV as PDF (GCSM format)" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition mr-1">⬇ CV PDF</a>
    <a href="{{ route('crew.cv.excel', $crew) }}" title="Download CV as Excel" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition mr-1">⬇ CV Excel</a>
    @can('crew.edit')<a href="{{ route('crew.edit', $crew) }}" title="Edit basic identity fields (name, CDC, passport…)" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-sm px-4 py-1.5 hover:bg-[#2E74B5] transition">✎ Edit identity</a>@endcan
@endsection
@section('content')

@php $eng = ['','Excellent','Very Good','Good','Fair','Poor'];
     $ea = $crew->effective_availability;
     $availColors = ['available'=>'bg-emerald-50 text-emerald-700 ring-emerald-200','not_available'=>'bg-slate-100 text-slate-500 ring-slate-200','onboard'=>'bg-blue-50 text-blue-700 ring-blue-200','resting'=>'bg-amber-50 text-amber-800 ring-amber-200'];
     $pc = (int) $completeness['percent'];
     if ($pc >= 100)    { $g0='#34D399'; $g1='#059669'; $txt='text-emerald-600'; $lbl='Complete'; }
     elseif ($pc >= 50) { $g0='#FBBF24'; $g1='#F59E0B'; $txt='text-amber-600';   $lbl='In progress'; }
     else               { $g0='#FB7185'; $g1='#E11D48'; $txt='text-rose-600';    $lbl='Incomplete'; }
     $bmi = null; $bmiCat = ''; $bmiColor = 'text-navy-800';
     if ($crew->height_cm && $crew->weight_kg && (float)$crew->height_cm > 0) {
         $hm = (float)$crew->height_cm / 100; $bmi = round((float)$crew->weight_kg / ($hm*$hm), 1);
         if ($bmi < 18.5) { $bmiCat='Underweight'; $bmiColor='text-amber-600'; }
         elseif ($bmi < 25) { $bmiCat='Normal'; $bmiColor='text-emerald-600'; }
         elseif ($bmi < 30) { $bmiCat='Overweight'; $bmiColor='text-amber-600'; }
         else { $bmiCat='Obese'; $bmiColor='text-rose-600'; }
     }
@endphp

@if ($crew->offences->isNotEmpty())
    <div class="mb-4 rounded bg-amber-100 border border-amber-300 text-amber-800 px-4 py-2 text-sm">⚠ This crew has {{ $crew->offences->count() }} offence record(s). Review before selection.</div>
@endif

{{-- Header --}}
<div class="bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-start gap-5">
        @if ($crew->photo_path)
            <img src="{{ asset('storage/'.$crew->photo_path) }}" class="w-24 h-28 rounded-xl object-cover ring-1 ring-gold-300 shadow-sm shrink-0" alt="photo">
        @else
            <div class="w-24 h-28 rounded-xl bg-slate-100 ring-1 ring-slate-200 flex items-center justify-center text-slate-300 text-3xl shrink-0">👤</div>
        @endif
        <div class="flex-1 min-w-0">
            <div class="text-2xl font-bold text-navy-800 tracking-tight leading-tight">{{ $crew->name }} @if($crew->name_chinese)<span class="text-slate-400 text-lg font-medium">/ {{ $crew->name_chinese }}</span>@endif</div>
            <div class="text-sm text-slate-500 mt-1">{{ optional($crew->currentRank)->rank_name }} <span class="text-slate-300">·</span> Crew ID <span class="font-medium text-slate-600">{{ $crew->display_id }}</span> <span class="ml-1 text-xs text-amber-600 font-semibold">· Editing</span></div>
            <div class="flex flex-wrap items-center gap-1.5 mt-3">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 {{ $crew->source === 'oma' ? 'bg-blue-50 text-blue-700 ring-blue-200' : 'bg-slate-50 text-slate-600 ring-slate-200' }}">{{ strtoupper($crew->source) }}</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 {{ $completeness['complete'] ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-amber-50 text-amber-800 ring-amber-200' }}">{{ $completeness['complete'] ? 'COMPLETE' : 'DRAFT' }}</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 {{ $availColors[$ea] ?? 'bg-slate-50 text-slate-600 ring-slate-200' }}">{{ ucfirst(str_replace('_',' ',$ea)) }}</span>
                @if ($crew->availability === 'resting' && $crew->available_from)<span class="text-[11px] text-amber-700">avail {{ $crew->available_from->toDateString() }}@if(!is_null($crew->resting_days_left)) ({{ max($crew->resting_days_left,0) }}d)@endif</span>@endif
                @include('crew.partials.urgency', ['level' => $crew->job_urgency, 'deadline' => $crew->job_deadline])
            </div>
        </div>
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
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="url(#gcsmRing)" stroke-width="4" stroke-linecap="round" stroke-dasharray="{{ $pc }} {{ 100 - $pc }}" style="transition:stroke-dasharray .8s var(--ease,ease); filter:drop-shadow(0 2px 4px rgba(16,33,60,.18))"></circle>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center leading-none">
                    <span class="text-2xl font-extrabold text-navy-800">{{ $pc }}</span>
                    <span class="text-[9px] font-bold {{ $txt }} tracking-wider">PERCENT</span>
                </div>
            </div>
            <div class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide {{ $pc>=100 ? 'bg-emerald-50 text-emerald-600' : ($pc>=50 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}">{{ $lbl }}</div>
        </div>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-y-4 gap-x-6 mt-5 pt-5 border-t border-slate-100 text-sm">
        @php $fact = fn ($l,$v) => '<div class="min-w-0"><div class="text-[10px] uppercase tracking-wider text-slate-400">'.e($l).'</div><div class="text-sm font-semibold text-navy-800 truncate">'.e($v !== null && $v !== '' ? $v : '—').'</div></div>'; @endphp
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

{{-- Fixed condensed crew bar --}}
<div id="miniBar" class="fixed top-[57px] left-64 right-0 z-30 bg-white border-b border-slate-200 shadow-md px-6 pt-2 pb-1.5">
    <div class="flex items-center gap-x-4 gap-y-1 flex-wrap text-sm">
        @if ($crew->photo_path)
            <img src="{{ asset('storage/'.$crew->photo_path) }}" class="w-10 h-10 rounded-lg object-cover ring-1 ring-gold-300 shrink-0" alt="">
        @else
            <div class="w-10 h-10 rounded-lg bg-slate-100 ring-1 ring-slate-200 flex items-center justify-center text-slate-300 shrink-0">👤</div>
        @endif
        <div class="leading-tight shrink-0">
            <div class="text-sm font-bold text-navy-800">{{ $crew->name }} <span class="text-[10px] text-amber-600 font-semibold">· Editing</span></div>
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
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-[11px] font-bold {{ $pc>=100 ? 'text-emerald-600' : ($pc>=50 ? 'text-amber-600' : 'text-rose-600') }}" title="Profile {{ $pc }}% complete" style="background-image:conic-gradient({{ $g1 }} {{ $pc }}%, #eef2f7 0)">
                <span class="w-7 h-7 rounded-full bg-white flex items-center justify-center">{{ $pc }}</span>
            </div>
        </div>
    </div>
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

{{-- ================= PANELS ================= --}}

{{-- Personal Details --}}
<div data-panel="personal" class="tab-panel hidden">
    @unless ($completeness['complete'])
        <div class="bg-amber-50 border border-amber-300 rounded-lg p-3 mb-4 text-xs">
            <b class="text-amber-900">Still to complete ({{ count($completeness['missing']) }}):</b>
            <span class="flex flex-wrap gap-1.5 mt-1">
                @foreach ($completeness['missing'] as $m)<span class="px-2 py-0.5 rounded-full bg-white border border-amber-300 text-amber-900">{{ $m }}</span>@endforeach
            </span>
        </div>
    @endunless

    <div class="bg-white rounded-lg shadow p-5 mb-4">
        <div class="gcsm-head"><h3 class="font-semibold text-sm md:text-base">Personal &amp; CV details</h3></div>
        <form method="POST" action="{{ route('crew.details.update', $crew) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <fieldset class="border border-slate-200 rounded-lg p-4">
                <legend class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Personal &amp; physical</legend>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-3 text-sm">
                    <div><label class="block text-xs text-slate-500 mb-1">Photo</label><input type="file" name="photo" accept="image/*" class="text-xs w-full"></div>
                    <div class="relative"><label class="block text-xs text-slate-500 mb-1">Place of birth <span class="text-slate-400">(search district)</span></label>
                        <input name="place_of_birth" data-combo="districts" autocomplete="off" value="{{ $crew->place_of_birth }}" placeholder="Type to search…" class="w-full border rounded px-2 py-1.5"></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Nationality</label><input name="nationality" value="{{ $crew->nationality ?: 'Bangladeshi' }}" class="w-full border rounded px-2 py-1.5"></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Religion</label>
                        <select name="religion" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
                            @foreach(['Islam','Hinduism','Christianity','Buddhism','Other'] as $rel)<option @selected($crew->religion===$rel)>{{ $rel }}</option>@endforeach</select></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Gender</label>
                        <select name="gender" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
                            @foreach(['Male','Female'] as $g)<option @selected($crew->gender===$g)>{{ $g }}</option>@endforeach</select></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Marital status</label>
                        <select name="marital_status" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
                            @foreach(['Single','Married','Widowed','Separated','Divorced','Not specified'] as $m)<option @selected($crew->marital_status===$m)>{{ $m }}</option>@endforeach</select></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Blood group</label>
                        <select name="blood_group" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
                            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)<option @selected($crew->blood_group===$bg)>{{ $bg }}</option>@endforeach</select></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Shoe size (EU)</label>
                        <select name="shoe_size" id="shoeSelect" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
                            @for($s=36;$s<=48;$s++)<option value="{{ $s }}" @selected((string)$crew->shoe_size===(string)$s)>EU {{ $s }}</option>@endfor</select>
                        <div id="shoeGuide" class="text-[11px] text-slate-400 mt-1"></div></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Height</label>
                        <select name="height_cm" id="heightSelect" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
                            @for($h=140;$h<=210;$h++)<option value="{{ $h }}" @selected((string)$crew->height_cm===(string)$h)>{{ $h }} cm</option>@endfor</select>
                        <input type="number" id="heightInch" step="0.1" placeholder="or type inch → cm" class="w-full border rounded px-2 py-1 text-xs mt-1"></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Weight</label>
                        <select name="weight_kg" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
                            @for($w=40;$w<=160;$w++)<option value="{{ $w }}" @selected((string)$crew->weight_kg===(string)$w)>{{ $w }} kg</option>@endfor</select></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Emergency contact</label><input name="emergency_contact" value="{{ $crew->emergency_contact }}" class="w-full border rounded px-2 py-1.5"></div>
                </div>
            </fieldset>

            <fieldset class="border border-slate-200 rounded-lg p-4">
                <legend class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Addresses</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div><label class="block text-xs text-slate-500 mb-1">Present address</label><input name="present_address" value="{{ $crew->present_address }}" class="w-full border rounded px-2 py-1.5"></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Permanent address</label><input name="permanent_address" value="{{ $crew->permanent_address }}" class="w-full border rounded px-2 py-1.5"></div>
                </div>
            </fieldset>

            <fieldset class="border border-slate-200 rounded-lg p-4">
                <legend class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Next of kin</legend>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-3 text-sm">
                    <div><label class="block text-xs text-slate-500 mb-1">Name</label><input name="next_of_kin_name" value="{{ $crew->next_of_kin_name }}" class="w-full border rounded px-2 py-1.5"></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Relation</label><input name="next_of_kin_relation" value="{{ $crew->next_of_kin_relation }}" class="w-full border rounded px-2 py-1.5"></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Contact number</label><input name="next_of_kin_contact" value="{{ $crew->next_of_kin_contact }}" class="w-full border rounded px-2 py-1.5"></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Address</label><input name="next_of_kin_address" value="{{ $crew->next_of_kin_address }}" class="w-full border rounded px-2 py-1.5"></div>
                </div>
            </fieldset>

            <fieldset class="border border-slate-200 rounded-lg p-4">
                <legend class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">English level (Excellent → Poor)</legend>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-3 text-sm">
                    @foreach (['english_listening'=>'Listening','english_speaking'=>'Spoken','english_reading'=>'Reading','english_writing'=>'Writing'] as $f=>$lbl2)
                    <div><label class="block text-xs text-slate-500 mb-1">{{ $lbl2 }}</label>
                        <select name="{{ $f }}" class="w-full border rounded px-2 py-1.5">
                            @foreach($eng as $lv)<option value="{{ $lv }}" @selected($crew->$f===$lv)>{{ $lv ?: '—' }}</option>@endforeach
                        </select></div>
                    @endforeach
                </div>
            </fieldset>

            {{-- Job status & urgency is edited only from the Placement History tab (every change is logged). --}}

            <button class="bg-[#1F3864] text-white rounded px-5 py-2 text-sm hover:bg-[#2E74B5]">Save personal details</button>
        </form>
    </div>
</div>

{{-- Placement History --}}
<div data-panel="voyages" class="tab-panel hidden">
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-semibold text-slate-600 mr-1">Placement status</span>
            <span class="px-2 py-0.5 rounded text-xs {{ $availColors[$ea] ?? 'bg-slate-100 text-slate-500' }}">{{ ucfirst(str_replace('_',' ',$ea)) }}</span>
            @include('crew.partials.urgency', ['level' => $crew->job_urgency, 'deadline' => $crew->job_deadline])
            <div class="ml-auto flex items-center gap-2">
                @if ($crew->availability === 'onboard' && $activePlacement)
                    <span title="Placement status is locked while the crew is onboard — sign off first to change it"
                          class="inline-flex items-center gap-1 rounded-md bg-slate-100 text-slate-400 font-semibold text-xs px-3 py-1.5 ring-1 ring-slate-200 cursor-not-allowed select-none">🔒 Placement status locked</span>
                    <button type="button" onclick="gcsmSignOff({ action: @js(route('placements.signoff', $activePlacement)), signOnDate: @js(optional($activePlacement->sign_on_date)->toDateString()) })"
                            class="rounded-md bg-slate-700 text-white font-semibold text-xs px-3 py-1.5 hover:bg-slate-800" title="Complete this voyage and update placement status">⚓ Sign-Off</button>
                @else
                    <button type="button" onclick="document.getElementById('statusEdit').classList.toggle('hidden')"
                            class="rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5">✎ Edit availability &amp; urgency</button>
                    <a href="{{ route('selection.index') }}" class="rounded-md border border-slate-300 text-slate-700 font-semibold text-xs px-3 py-1.5 hover:bg-slate-100" title="Sign-On is recorded in the Crew Selection module">Go to Crew Selection →</a>
                @endif
            </div>
        </div>

        {{-- Inline editor: availability + urgency + reason (logged system-wide) --}}
        <form id="statusEdit" method="POST" action="{{ route('crew.status.update', $crew) }}"
              class="hidden mt-3 border-t pt-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">@csrf
            <div>
                <label class="block text-xs text-slate-500 mb-1">Availability</label>
                <select name="availability" class="w-full border rounded px-2 py-1.5">
                    @foreach(['available'=>'Available','not_available'=>'Not available','onboard'=>'Onboard','resting'=>'Resting'] as $k=>$l)
                        <option value="{{ $k }}" @selected($crew->availability===$k)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Job urgency</label>
                <select name="job_urgency" class="w-full border rounded px-2 py-1.5">
                    @foreach(['normal'=>'Normal','high'=>'High','urgent'=>'Urgent'] as $k=>$l)
                        <option value="{{ $k }}" @selected($crew->job_urgency===$k)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Placement deadline</label>
                <input type="date" name="job_deadline" value="{{ optional($crew->job_deadline)->toDateString() }}" class="w-full border rounded px-2 py-1.5">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Available from <span class="text-slate-400">(future = Resting)</span></label>
                <input type="date" name="available_from" value="{{ optional($crew->available_from)->toDateString() }}" class="w-full border rounded px-2 py-1.5">
            </div>
            <div class="col-span-2 md:col-span-4">
                <label class="block text-xs text-slate-500 mb-1">Reason for change <span class="text-red-500">*</span></label>
                <input type="text" name="reason" required maxlength="500" placeholder="Why is this change being made? (e.g. crew requested leave, urgent replacement needed)"
                       class="w-full border rounded px-2 py-1.5">
            </div>
            <div class="col-span-2 md:col-span-4 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('statusEdit').classList.add('hidden')"
                        class="text-xs px-3 py-1.5 rounded border border-slate-300 text-slate-600 hover:bg-slate-100">Cancel</button>
                <button class="bg-[#1F3864] text-white rounded px-4 py-1.5 text-xs font-semibold">Save &amp; log change</button>
            </div>
        </form>

        <p class="text-xs text-slate-400 mt-2">Changes here sync across the whole system (crew list, Crew Selection, dashboard) and are recorded below with who, when and why. Sign-On is recorded in the <b>Crew Selection</b> module; Sign-Off from the active voyage below.</p>
    </div>

    @include('crew.partials.voyages', ['editing' => true])

    <div class="mt-4">@include('crew.partials.status_log')</div>
</div>

{{-- Reminders --}}
<div data-panel="reminders" class="tab-panel hidden">
    <div class="bg-white rounded-lg shadow p-4 mb-4">
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
</div>

{{-- Maritime Education --}}
<div data-panel="maritime" class="tab-panel hidden">
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="gcsm-head justify-between">
            <h3 class="font-semibold text-sm md:text-base">Maritime Education Details</h3>
            <button type="button" class="js-toggle bg-[#1F3864] hover:bg-[#2E4A7A] text-white text-xs px-3 py-1.5 rounded-md" data-target="#add-maritime">+ Add record</button>
        </div>
        <table class="w-full text-sm mb-2">
            <thead class="text-slate-400 text-left"><tr><th class="py-1">Institute</th><th>Department</th><th>Year of Graduation</th><th></th></tr></thead>
            <tbody id="rows-maritime">
            @forelse ($crew->maritimeEducations as $row)@include('crew.partials.maritime_row')
            @empty <tr class="empty-row"><td colspan="4" class="py-3 text-slate-400">No maritime education added yet.</td></tr> @endforelse
            </tbody>
        </table>
        <form id="add-maritime" class="js-add hidden bg-slate-50 border rounded-lg p-3 mt-2" method="POST" action="{{ route('crew.maritime.store', $crew) }}" data-rows="#rows-maritime">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                <div><label class="block text-xs text-slate-500 mb-1">Name of Maritime Institute *</label>
                    <select name="institute" data-placeholder="Search institute…" class="w-full border rounded px-2 py-1.5">
                        <option value="">—</option>
                        @foreach (($marineAcademies ?? []) as $cat => $items)
                            <optgroup label="{{ $cat }}">@foreach ($items as $a)<option value="{{ $a->name }}">{{ $a->name }}</option>@endforeach</optgroup>
                        @endforeach
                    </select></div>
                <div><label class="block text-xs text-slate-500 mb-1">Department</label>
                    <select name="department" data-placeholder="Search department…" class="w-full border rounded px-2 py-1.5">
                        <option value="">—</option>
                        @foreach (($marineDepartments ?? []) as $cat => $items)
                            <optgroup label="{{ $cat }}">@foreach ($items as $d)<option value="{{ $d->name }}">{{ $d->name }}</option>@endforeach</optgroup>
                        @endforeach
                    </select></div>
                <div><label class="block text-xs text-slate-500 mb-1">Year of Graduation</label><input name="year_of_graduation" class="w-full border rounded px-2 py-1.5"></div>
            </div>
            <div class="flex items-center gap-3 mt-3"><button class="bg-[#1F3864] text-white rounded px-4 py-1.5 text-sm">Save record</button><span class="js-msg text-xs"></span></div>
        </form>
    </div>
</div>

{{-- Educational Qualification --}}
<div data-panel="education" class="tab-panel hidden">
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="gcsm-head justify-between">
            <h3 class="font-semibold text-sm md:text-base">Educational Qualification</h3>
            <button type="button" class="js-toggle bg-[#1F3864] hover:bg-[#2E4A7A] text-white text-xs px-3 py-1.5 rounded-md" data-target="#add-academic">+ Add record</button>
        </div>
        <table class="w-full text-sm mb-2">
            <thead class="text-slate-400 text-left"><tr><th class="py-1">Description</th><th>Board</th><th>Group</th><th>Passing Year</th><th>GPA</th><th></th></tr></thead>
            <tbody id="rows-academic">
            @forelse ($crew->academics as $row)@include('crew.partials.academic_row')
            @empty <tr class="empty-row"><td colspan="6" class="py-3 text-slate-400">No educational qualification added yet.</td></tr> @endforelse
            </tbody>
        </table>
        <form id="add-academic" class="js-add hidden bg-slate-50 border rounded-lg p-3 mt-2" method="POST" action="{{ route('crew.academics.store', $crew) }}" data-rows="#rows-academic">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                <div><label class="block text-xs text-slate-500 mb-1">Description *</label>
                    <select name="description" class="w-full border rounded px-2 py-1.5">
                        <option value="S.S.C">S.S.C</option><option value="H.S.C">H.S.C</option>
                        <option value="Diploma">Diploma</option><option value="B.Sc">B.Sc</option><option value="Others">Others</option>
                    </select></div>
                <div><label class="block text-xs text-slate-500 mb-1">Board</label><input name="board" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Group</label><input name="group" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Passing Year</label><input name="passing_year" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">GPA</label><input name="gpa" class="w-full border rounded px-2 py-1.5"></div>
            </div>
            <div class="flex items-center gap-3 mt-3"><button class="bg-[#1F3864] text-white rounded px-4 py-1.5 text-sm">Save record</button><span class="js-msg text-xs"></span></div>
        </form>
    </div>
</div>

{{-- Travel Documents --}}
<div data-panel="documents" class="tab-panel hidden">
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="gcsm-head justify-between">
            <h3 class="font-semibold text-sm md:text-base">Professional &amp; Travel Documents <span class="text-slate-400 text-xs font-normal">(issue &amp; expiry mandatory)</span></h3>
            <button type="button" class="js-toggle bg-[#1F3864] hover:bg-[#2E4A7A] text-white text-xs px-3 py-1.5 rounded-md" data-target="#add-document">+ Add record</button>
        </div>
        <table class="w-full text-sm mb-2">
            <thead class="text-slate-400 text-left"><tr><th class="py-1">Type</th><th>Number</th><th>Issue</th><th>Expiry</th><th>Status</th><th>File</th><th></th></tr></thead>
            <tbody id="rows-document">
            @forelse ($crew->documents as $row)@include('crew.partials.document_row')
            @empty <tr class="empty-row"><td colspan="7" class="py-3 text-slate-400">No documents added yet.</td></tr> @endforelse
            </tbody>
        </table>
        <form id="add-document" class="js-add hidden bg-slate-50 border rounded-lg p-3 mt-2" method="POST" action="{{ route('crew.documents.store', $crew) }}" enctype="multipart/form-data" data-rows="#rows-document">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                <div class="relative"><label class="block text-xs text-slate-500 mb-1">Document type * <span class="text-slate-400">(type to search)</span></label>
                    <input name="doc_type" data-combo="docTypes" autocomplete="off" required placeholder="Search or type…" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Number</label><input name="number" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Issuing Authority</label><input name="issuing_authority" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Date of Issue</label><input type="date" name="issue_date" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Date of Expiry</label><input type="date" name="expiry_date" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Scan (PDF/JPG)</label><input type="file" name="scan" class="text-xs w-full"></div>
            </div>
            <div class="flex items-center gap-3 mt-3"><button class="bg-[#1F3864] text-white rounded px-4 py-1.5 text-sm">Save record</button><span class="js-msg text-xs"></span></div>
        </form>
    </div>
</div>

{{-- Course Certificates --}}
<div data-panel="certs" class="tab-panel hidden">
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="gcsm-head justify-between">
            <h3 class="font-semibold text-sm md:text-base">Course Certificates</h3>
            <button type="button" class="js-toggle bg-[#1F3864] hover:bg-[#2E4A7A] text-white text-xs px-3 py-1.5 rounded-md" data-target="#add-course">+ Add record</button>
        </div>
        <table class="w-full text-sm mb-2">
            <thead class="text-slate-400 text-left"><tr><th class="py-1">Category</th><th>Course Code</th><th>Cert No</th><th>Issue</th><th>Expiry</th><th>Issuer</th><th>Authority</th><th>Src</th><th>File</th><th></th></tr></thead>
            <tbody id="rows-course">
            @forelse ($crew->courses as $row)@include('crew.partials.course_row')
            @empty <tr class="empty-row"><td colspan="10" class="py-3 text-slate-400">No certificates added yet.</td></tr> @endforelse
            </tbody>
        </table>
        <form id="add-course" class="js-add hidden bg-slate-50 border rounded-lg p-3 mt-2" method="POST" action="{{ route('crew.courses.store', $crew) }}" enctype="multipart/form-data" data-rows="#rows-course">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div class="md:col-span-2 relative"><label class="block text-xs text-slate-500 mb-1">Category / Certificate name * <span class="text-slate-400">(search by course name / course code)</span></label>
                    <input name="course_name" data-combo="courses" autocomplete="off" required placeholder="Search by course name / course code" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Certificate Number</label><input name="certificate_no" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Date of Issue</label><input type="date" name="issue_date" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Date of Expiry</label><input type="date" name="expiry_date" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Issuer</label><input name="issuer" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Issuing Authority</label><input name="issuing_authority" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Scan (PDF/JPG)</label><input type="file" name="scan" class="text-xs w-full"></div>
            </div>
            <div class="flex items-center gap-3 mt-3"><button class="bg-[#1F3864] text-white rounded px-4 py-1.5 text-sm">Save record</button><span class="js-msg text-xs"></span></div>
        </form>
    </div>
</div>

{{-- Sea Service --}}
<div data-panel="sea" class="tab-panel hidden">
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="gcsm-head justify-between">
            <h3 class="font-semibold text-sm md:text-base">Sea Service / Experience</h3>
            <button type="button" class="js-toggle bg-[#1F3864] hover:bg-[#2E4A7A] text-white text-xs px-3 py-1.5 rounded-md" data-target="#add-sea">+ Add experience</button>
        </div>
        <table class="w-full text-sm mb-2">
            <thead class="text-slate-400 text-left"><tr><th class="py-1">Vessel</th><th>IMO</th><th>Type</th><th>Rank</th><th>Company</th><th>On</th><th>Off</th><th>Days</th><th></th></tr></thead>
            <tbody id="rows-sea">
            @forelse ($crew->seaServices as $row)@include('crew.partials.sea_row')
            @empty <tr class="empty-row"><td colspan="9" class="py-3 text-slate-400">No sea service added yet.</td></tr> @endforelse
            </tbody>
        </table>
        <form id="add-sea" class="js-add hidden bg-slate-50 border rounded-lg p-3 mt-2" method="POST" action="{{ route('crew.sea.store', $crew) }}" data-rows="#rows-sea">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div><label class="block text-xs text-slate-500 mb-1">Name of Company</label><input name="company_name" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Name of Vessel</label><input name="vessel_name" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Ship IMO</label><input name="imo_no" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">VSL. Type</label>
                    <select name="vessel_type" data-placeholder="Search vessel type…" class="w-full border rounded px-2 py-1.5">
                        <option value="">—</option>
                        @foreach (($vesselTypes ?? []) as $vt)<option value="{{ $vt->type_name }}">{{ $vt->type_name }}</option>@endforeach
                    </select></div>
                <div><label class="block text-xs text-slate-500 mb-1">GRT</label><input name="grt" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">DWT</label><input name="dwt" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Engine Type</label><input name="engine_type" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">BHP</label><input name="bhp" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Flag</label><input name="flag" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Trading Area</label><input name="trading_area" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Rank</label>
                    <select name="rank" data-placeholder="Search rank…" class="w-full border rounded px-2 py-1.5">
                        <option value="">—</option>
                        @php $seaLastDept = null; @endphp
                        @foreach ($ranks as $r)
                            @if ($r->department !== $seaLastDept)
                                @if ($seaLastDept !== null)</optgroup>@endif
                                <optgroup label="{{ $r->department ?: 'Other' }}">
                                @php $seaLastDept = $r->department; @endphp
                            @endif
                            <option value="{{ $r->rank_name }}">{{ $r->rank_name }}</option>
                        @endforeach
                        @if ($seaLastDept !== null)</optgroup>@endif
                    </select>
                </div>
                <div><label class="block text-xs text-slate-500 mb-1">Owner</label><input name="owner" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Date of Sign-On</label><input type="date" name="sign_on" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Date of Sign-Off</label><input type="date" name="sign_off" class="w-full border rounded px-2 py-1.5"></div>
                <div class="md:col-span-4"><label class="block text-xs text-slate-500 mb-1">Reason of Sign-Off</label><input name="reason_sign_off" class="w-full border rounded px-2 py-1.5"></div>
            </div>
            <div class="flex items-center gap-3 mt-3"><button class="bg-[#1F3864] text-white rounded px-4 py-1.5 text-sm">Save experience</button><span class="js-msg text-xs"></span></div>
        </form>
    </div>
</div>

{{-- Bank --}}
<div data-panel="bank" class="tab-panel hidden">
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="gcsm-head"><h3 class="font-semibold text-sm md:text-base">Bank Account Details</h3></div>

        {{-- Existing accounts --}}
        <div class="space-y-3 mb-4">
        @forelse ($crew->bankAccounts as $b)
            @include('crew.partials.bank_card', ['b' => $b, 'editable' => true])
        @empty <div class="text-slate-400 text-sm">None.</div> @endforelse
        </div>

        @can('crew.edit')
        <form method="POST" action="{{ route('crew.bank.store', $crew) }}" enctype="multipart/form-data" class="border-t pt-3">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div><label class="block text-xs text-slate-500 mb-1">Bank Name *</label><input name="bank_name" required class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Account Name *</label><input name="account_name" required class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Account Number *</label><input name="account_number" required class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Branch *</label><input name="branch" required class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Routing Number</label><input name="routing_number" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Swift Code</label><input name="swift_code" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Mobile Number</label><input name="mobile_number" class="w-full border rounded px-2 py-1.5"></div>
                <div><label class="block text-xs text-slate-500 mb-1">Cheque Book Page Scan</label><input type="file" name="cheque" accept="image/*,application/pdf" class="w-full text-xs"></div>
                <div class="md:col-span-2"><label class="block text-xs text-slate-500 mb-1">Account owner</label>
                    <select name="is_own_account" id="ownAcct" class="w-full border rounded px-2 py-1.5">
                        <option value="1">Own account (the crew)</option>
                        <option value="0">Third-party account (someone else)</option>
                    </select></div>
            </div>

            {{-- Owner details (only when it is NOT the crew's own account) --}}
            <div id="ownerFields" class="hidden mt-3 border-t pt-3">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Account owner (third-party)</div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    <div><label class="block text-xs text-slate-500 mb-1">Relationship with crew *</label><input name="owner_relationship" placeholder="e.g. Father, Wife, Brother" class="w-full border rounded px-2 py-1.5"></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Owner NID Number *</label><input name="owner_nid" class="w-full border rounded px-2 py-1.5"></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Owner NID Card (upload) *</label><input type="file" name="owner_nid_scan" accept="image/*,application/pdf" class="w-full text-xs"></div>
                    <div><label class="block text-xs text-slate-500 mb-1">Owner Passport-size Photo</label><input type="file" name="owner_photo" accept="image/*" class="w-full text-xs"></div>
                </div>
            </div>

            <div class="mt-3"><button class="bg-[#1F3864] text-white rounded px-5 py-1.5 text-sm">Add account</button></div>
        </form>
        <script>
        (function () {
            var s = document.getElementById('ownAcct'), f = document.getElementById('ownerFields');
            if (s && f) { var t = function () { f.classList.toggle('hidden', String(s.value) === '1'); }; s.addEventListener('change', t); t(); }
        })();
        </script>
        @endcan
    </div>
</div>

{{-- Offences --}}
<div data-panel="offence" class="tab-panel hidden">
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="gcsm-head"><h3 class="font-semibold text-sm md:text-base">Offence records</h3></div>
        @forelse ($crew->offences as $o)
            <div class="text-sm border-t py-2">{{ optional($o->offence_date)->toDateString() }} — {{ $o->description }}
                <form method="POST" action="{{ route('crew.offences.destroy', [$crew,$o]) }}" class="inline" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button class="text-red-500 text-xs ml-1">×</button></form></div>
        @empty <div class="text-slate-400 text-sm">None.</div> @endforelse
        <form method="POST" action="{{ route('crew.offences.store', $crew) }}" class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-2 text-sm items-end border-t pt-3">
            @csrf
            <input type="date" name="offence_date" class="border rounded px-2 py-1.5">
            <input name="description" placeholder="Description *" required class="border rounded px-2 py-1.5 md:col-span-1">
            <button class="bg-[#1F3864] text-white rounded px-3 py-1.5">Record offence</button>
        </form>
    </div>
</div>

{{-- Notes --}}
<div data-panel="notes" class="tab-panel hidden">
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="gcsm-head"><h3 class="font-semibold text-sm md:text-base">Notes</h3></div>
        @forelse ($crew->notes as $n)
            <div class="text-sm border-t py-2">{{ $n->note }}<br><span class="text-slate-400 text-xs">{{ optional($n->author)->name }} · {{ $n->created_at->diffForHumans() }}
                <form method="POST" action="{{ route('crew.notes.destroy', [$crew,$n]) }}" class="inline" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button class="text-red-500 ml-1">×</button></form></span></div>
        @empty <div class="text-slate-400 text-sm">None.</div> @endforelse
        <form method="POST" action="{{ route('crew.notes.store', $crew) }}" class="mt-3 flex gap-2 text-sm items-end border-t pt-3">
            @csrf
            <textarea name="note" placeholder="Add a note *" required class="flex-1 border rounded px-2 py-1.5"></textarea>
            <button class="bg-[#1F3864] text-white rounded px-3 py-1.5">Save note</button>
        </form>
    </div>
</div>

{{-- Edit Log --}}
<div data-panel="editlog" class="tab-panel hidden">
    @include('crew.partials.edit_log')
</div>

<script>
(function () {
    // ---- Tabs ----
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.js-tabs .ptab'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('.tab-panel'));
    function show(name) {
        tabs.forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-tab') === name); });
        panels.forEach(function (p) { p.classList.toggle('hidden', p.getAttribute('data-panel') !== name); });
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { show(t.getAttribute('data-tab')); }); });
    if (tabs.length) show('personal');

    // ---- Fixed condensed bar on scroll ----
    var mini = document.getElementById('miniBar'), flow = document.getElementById('flowTabs');
    if (mini) {
        var ticking = false;
        function upd() {
            var y = window.pageYOffset || document.documentElement.scrollTop;
            var on = y > 150;
            mini.classList.toggle('show', on);
            if (flow) { flow.style.opacity = on ? '0' : '1'; flow.style.pointerEvents = on ? 'none' : 'auto'; }
            ticking = false;
        }
        window.addEventListener('scroll', function () { if (!ticking) { window.requestAnimationFrame(upd); ticking = true; } }, { passive: true });
        upd();
    }

    // ---- Urgency deadline toggle ----
    var u = document.getElementById('urgencySel'), w = document.getElementById('deadlineWrap');
    function td() { if (u && w) w.style.display = (u.value === 'normal') ? 'none' : ''; }
    if (u) { u.addEventListener('change', td); td(); }

    // ---- Add / delete records without page reload ----
    document.addEventListener('click', function (e) {
        var t = e.target.closest('.js-toggle');
        if (!t) return;
        var el = document.querySelector(t.getAttribute('data-target'));
        if (!el) return;
        el.classList.toggle('hidden');
        if (!el.classList.contains('hidden')) { var f = el.querySelector('input:not([type=hidden]), select, textarea'); if (f) f.focus(); }
    });
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.classList.contains('js-add')) {
            e.preventDefault();
            var msg = form.querySelector('.js-msg'), btn = form.querySelector('button'), fd = new FormData(form);
            if (btn) btn.disabled = true;
            if (msg) { msg.className = 'js-msg text-xs text-slate-400'; msg.textContent = 'Saving…'; }
            fetch(form.action, { method: 'POST', body: fd, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                .then(function (res) {
                    if (btn) btn.disabled = false;
                    if (!res.ok || !res.d.ok) { if (msg) { msg.className = 'js-msg text-xs text-red-500'; msg.textContent = (res.d && res.d.message) || 'Could not save — check the fields.'; } return; }
                    var tbody = document.querySelector(form.getAttribute('data-rows'));
                    if (tbody) { var empty = tbody.querySelector('.empty-row'); if (empty) empty.remove(); tbody.insertAdjacentHTML('beforeend', res.d.html); }
                    form.querySelectorAll('input:not([type=hidden]), select, textarea').forEach(function (i) { i.value = ''; i.dispatchEvent(new Event('change', { bubbles: true })); });
                    if (msg) { msg.className = 'js-msg text-xs text-emerald-600'; msg.textContent = 'Added ✓ — add another or close.'; }
                })
                .catch(function () { if (btn) btn.disabled = false; if (msg) { msg.className = 'js-msg text-xs text-red-500'; msg.textContent = 'Network error — try again.'; } });
            return;
        }
        if (form.classList.contains('js-del')) {
            e.preventDefault();
            if (!confirm('Remove this record?')) return;
            var fd2 = new FormData(form);
            fetch(form.action, { method: 'POST', body: fd2, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (d) { if (d.ok) { var tr = form.closest('tr'); if (tr) tr.remove(); } })
                .catch(function () {});
        }
    });

    // ---- Searchable dropdowns + shoe/height helpers ----
    var LISTS = {
        districts: ['Bagerhat','Bandarban','Barguna','Barishal','Bhola','Bogura','Brahmanbaria','Chandpur','Chattogram','Chuadanga',"Cox's Bazar",'Cumilla','Dhaka','Dinajpur','Faridpur','Feni','Gaibandha','Gazipur','Gopalganj','Habiganj','Jamalpur','Jashore','Jhalokati','Jhenaidah','Joypurhat','Khagrachhari','Khulna','Kishoreganj','Kurigram','Kushtia','Lakshmipur','Lalmonirhat','Madaripur','Magura','Manikganj','Meherpur','Moulvibazar','Munshiganj','Mymensingh','Naogaon','Narail','Narayanganj','Narsingdi','Natore','Chapainawabganj','Netrokona','Nilphamari','Noakhali','Pabna','Panchagarh','Patuakhali','Pirojpur','Rajbari','Rajshahi','Rangamati','Rangpur','Satkhira','Shariatpur','Sherpur','Sirajganj','Sunamganj','Sylhet','Tangail','Thakurgaon'],
        docTypes: ['Passport','Seaman Book (CDC)','Seafarers Identity Document (SID)','National ID (NID)','Birth Registration Certificate','Certificate of Competency (COC)','GMDSS','INDOS','Medical Fitness Certificate (Port Health)','Yellow Fever Certificate','Vaccination Certificate','US Visa','Schengen Visa','Other Visa','Flag State Endorsement','GOC','Watchkeeping Certificate','Police Clearance Certificate','Driving License','TIN Certificate'],
        courses: @json($courses->map(fn ($c) => ['n' => $c->course_name, 'c' => $c->code])->values())
    };
    function _cText(o){ return (o && typeof o === 'object') ? (o.n + (o.c ? '   ·   ' + o.c : '')) : o; }
    function _cVal(o){ return (o && typeof o === 'object') ? o.n : o; }
    function _cHay(o){ return String((o && typeof o === 'object') ? (o.n + ' ' + (o.c || '')) : o).toLowerCase(); }
    document.querySelectorAll('input[data-combo]').forEach(function (input) {
        var opts = LISTS[input.getAttribute('data-combo')] || [];
        var box = document.createElement('div');
        box.className = 'hidden absolute z-30 left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-md shadow-lg max-h-56 overflow-auto text-sm';
        input.parentNode.appendChild(box);
        function render() {
            var q = input.value.toLowerCase();
            var matches = opts.filter(function (o) { return _cHay(o).indexOf(q) > -1; }).slice(0, 60);
            box.innerHTML = '';
            if (!matches.length) { box.classList.add('hidden'); return; }
            matches.forEach(function (o) { var d = document.createElement('div'); d.className = 'combo-item'; d.textContent = _cText(o); d.addEventListener('mousedown', function (ev) { ev.preventDefault(); input.value = _cVal(o); box.classList.add('hidden'); }); box.appendChild(d); });
            box.classList.remove('hidden');
        }
        input.addEventListener('focus', render); input.addEventListener('input', render);
        input.addEventListener('blur', function () { setTimeout(function () { box.classList.add('hidden'); }, 150); });
    });
    var SHOE = {36:['3.5','4'],37:['4','5'],38:['5','6'],39:['6','7'],40:['6.5','7.5'],41:['7.5','8.5'],42:['8','9'],43:['9','10'],44:['9.5','10.5'],45:['10.5','11.5'],46:['11','12'],47:['12','13'],48:['13','14']};
    var shoeSel = document.getElementById('shoeSelect'), shoeGuide = document.getElementById('shoeGuide');
    function showShoe() { if (!shoeSel || !shoeGuide) return; var v = shoeSel.value, g = SHOE[v]; shoeGuide.textContent = g ? ('BD/EU ' + v + '  ·  UK ' + g[0] + '  ·  US ' + g[1]) : ''; }
    if (shoeSel) { shoeSel.addEventListener('change', showShoe); showShoe(); }
    var hSel = document.getElementById('heightSelect'), hInch = document.getElementById('heightInch');
    if (hInch && hSel) {
        hInch.addEventListener('input', function () {
            var inch = parseFloat(hInch.value); if (!inch) return;
            var cm = Math.round(inch * 2.54);
            var exists = Array.prototype.some.call(hSel.options, function (o) { return o.value == cm; });
            if (!exists) { hSel.add(new Option(cm + ' cm', cm)); }
            hSel.value = cm;
            hSel.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }
})();
</script>
@endsection
