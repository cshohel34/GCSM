@extends('layouts.app')
@section('title', $principal->name)
@section('actions')
    <a href="{{ route('principal.crewexport', ['principal'=>$principal->id,'export'=>'pdf']) }}" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition mr-1">⬇ Crew PDF</a>
    <a href="{{ route('principal.crewexport', ['principal'=>$principal->id,'export'=>'excel']) }}" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition mr-1">⬇ Crew Excel</a>
    @can('principal.edit')
        <form method="POST" action="{{ route('principal.activate', $principal) }}" class="inline">@csrf
            <button class="inline-flex items-center gap-1 rounded-md border font-semibold text-sm px-3 py-1.5 transition mr-1 {{ $principal->status==='active' ? 'border-emerald-300 text-emerald-700 hover:bg-emerald-50' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}">{{ $principal->status==='active' ? '● Active — click to deactivate' : '○ Activate' }}</button>
        </form>
        <a href="{{ route('principal.editprofile', $principal) }}" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-sm px-4 py-1.5 hover:bg-[#2E74B5] transition">✎ Edit Profile</a>
    @endcan
@endsection
@section('content')

@php
    $dash = fn ($v) => ($v !== null && $v !== '') ? $v : '—';
    $copyBtn = fn ($v) => ($v !== null && $v !== '') ? '<button type="button" class="js-copy text-slate-300 hover:text-[#1F3864] shrink-0" data-copy="'.e($v).'" title="Copy">&#10697;</button>' : '';

    // Company profile completeness (10 equally-weighted items).
    $checks = [
        !empty($principal->logo_path),
        !empty($principal->country),
        !empty($principal->phone),
        !empty($principal->email),
        !empty($principal->website),
        !empty($principal->address),
        !empty($principal->assigned_staff_id),
        $principal->contacts->isNotEmpty(),
        $principal->vessels->isNotEmpty(),
        $principal->documents->isNotEmpty(),
    ];
    $pc = (int) round(collect($checks)->filter()->count() / max(count($checks),1) * 100);
    if ($pc >= 100)    { $g0='#34D399'; $g1='#059669'; $txt='text-emerald-600'; $lbl='Complete'; }
    elseif ($pc >= 50) { $g0='#FBBF24'; $g1='#F59E0B'; $txt='text-amber-600';   $lbl='In progress'; }
    else               { $g0='#FB7185'; $g1='#E11D48'; $txt='text-rose-600';    $lbl='Incomplete'; }

    $currentManagers = $principal->assignments->whereNull('unassigned_at');
    $managerNames = $currentManagers->map(fn ($a) => optional($a->staff)->name)->filter()->implode(', ');

    $tabDefs = [
        'overview' => 'Company Overview', 'contacts' => 'Contacts', 'vessels' => 'Vessels',
        'docs' => 'Document & Contract', 'onboard' => 'Crew On Board', 'past' => 'Past Crew',
        'salary' => 'Salary Sheet', 'staff' => 'Managing Staff / Partner', 'offence' => 'Offences',
        'notes' => 'Notes', 'editlog' => 'Edit Log',
    ];
@endphp

@if ($principal->offences->isNotEmpty())
    <div class="mb-4 rounded bg-amber-100 border border-amber-300 text-amber-800 px-4 py-2 text-sm">⚠ This company has {{ $principal->offences->count() }} offence record(s). Review before selection.</div>
@endif

{{-- Header (scrolls away; a compact sticky bar stays on top) --}}
<div class="bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-start gap-5">
        @if ($principal->logo_path)
            <img src="{{ asset('storage/'.$principal->logo_path) }}" alt="logo" class="w-24 h-24 rounded-xl object-contain ring-1 ring-slate-200 bg-white shrink-0">
        @else
            <div class="w-24 h-24 rounded-xl bg-[#1F3864] text-white flex items-center justify-center text-2xl font-bold shrink-0">{{ strtoupper(mb_substr($principal->name,0,2)) }}</div>
        @endif
        <div class="flex-1 min-w-0">
            <div class="text-2xl font-bold text-navy-800 tracking-tight leading-tight">{{ $principal->name }}</div>
            <div class="text-sm text-slate-500 mt-1 capitalize">{{ $principal->type }} company @if($principal->country)<span class="text-slate-300">·</span> {{ $principal->country }}@endif</div>
            <div class="flex flex-wrap items-center gap-1.5 mt-3">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 {{ $principal->status==='active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-50 text-slate-600 ring-slate-200' }}">{{ strtoupper($principal->status) }}</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 bg-blue-50 text-blue-700 ring-blue-200">{{ $principal->vessels->count() }} vessel(s)</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 bg-amber-50 text-amber-800 ring-amber-200">{{ $onboard->count() }} onboard</span>
            </div>
            <div class="text-xs text-slate-500 mt-2"><span class="text-slate-400">Managing staff / partner:</span> <span class="font-semibold text-navy-800">{{ $managerNames ?: 'Unassigned' }}</span></div>
        </div>
        {{-- Circular completeness (gradient, colour-banded) --}}
        <div class="ml-auto shrink-0 flex flex-col items-center gap-2" title="Company profile {{ $pc }}% complete">
            <div class="relative w-[92px] h-[92px]">
                <svg viewBox="0 0 36 36" class="w-[92px] h-[92px] -rotate-90">
                    <defs>
                        <linearGradient id="gcsmRingCo" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="{{ $g0 }}"></stop>
                            <stop offset="100%" stop-color="{{ $g1 }}"></stop>
                        </linearGradient>
                    </defs>
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#eef2f7" stroke-width="3"></circle>
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="url(#gcsmRingCo)" stroke-width="4" stroke-linecap="round" stroke-dasharray="{{ $pc }} {{ 100 - $pc }}" style="transition:stroke-dasharray .8s var(--ease,ease); filter:drop-shadow(0 2px 4px rgba(16,33,60,.18))"></circle>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center leading-none">
                    <span class="text-2xl font-extrabold text-navy-800">{{ $pc }}</span>
                    <span class="text-[9px] font-bold {{ $txt }} tracking-wider">PERCENT</span>
                </div>
            </div>
            <div class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide {{ $pc>=100 ? 'bg-emerald-50 text-emerald-600' : ($pc>=50 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}">{{ $lbl }}</div>
        </div>
    </div>
</div>

{{-- Tabs (in-flow; fades out as the fixed bar takes over) --}}
<div id="flowTabs" class="mb-4 transition-opacity duration-300">
    <div class="js-tabs flex gap-1 overflow-x-auto bg-white rounded-xl shadow p-2">
        @foreach ($tabDefs as $key => $label)
            <button type="button" class="ptab" data-tab="{{ $key }}">{{ $label }}</button>
        @endforeach
    </div>
</div>

{{-- Fixed condensed company bar: appears smoothly on scroll (summary + tabs) --}}
<div id="miniBar" class="fixed top-[57px] left-64 right-0 z-30 bg-white border-b border-slate-200 shadow-md px-6 pt-2 pb-1.5">
    <div class="flex items-center gap-x-4 gap-y-1 flex-wrap text-sm">
        @if ($principal->logo_path)
            <img src="{{ asset('storage/'.$principal->logo_path) }}" class="w-10 h-10 rounded-lg object-contain ring-1 ring-slate-200 bg-white shrink-0" alt="">
        @else
            <div class="w-10 h-10 rounded-lg bg-[#1F3864] text-white flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(mb_substr($principal->name,0,2)) }}</div>
        @endif
        <div class="leading-tight shrink-0">
            <div class="text-sm font-bold text-navy-800">{{ $principal->name }}</div>
            <div class="text-[11px] text-slate-500 capitalize">{{ $principal->type }} @if($principal->country)· {{ $principal->country }}@endif</div>
        </div>
        <div class="w-px h-8 bg-slate-200 shrink-0 hidden md:block"></div>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[12px]">
            <span><span class="text-slate-400 text-[10px] uppercase">Phone</span> <span class="font-medium text-navy-800">{{ $principal->phone ?: '—' }}</span></span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 {{ $principal->status==='active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-50 text-slate-600 ring-slate-200' }}">{{ strtoupper($principal->status) }}</span>
        </div>
        <div class="ml-auto flex items-center gap-2 shrink-0">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-[11px] font-bold {{ $pc>=100 ? 'bg-emerald-50 text-emerald-600' : ($pc>=50 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}" title="Profile {{ $pc }}% complete" style="background-image:conic-gradient({{ $g1 }} {{ $pc }}%, #eef2f7 0)">
                <span class="w-7 h-7 rounded-full bg-white flex items-center justify-center">{{ $pc }}</span>
            </div>
        </div>
    </div>
    <div class="js-tabs flex gap-1 overflow-x-auto mt-1.5 pt-1.5 border-t border-slate-100">
        @foreach ($tabDefs as $key => $label)
            <button type="button" class="ptab" data-tab="{{ $key }}">{{ $label }}</button>
        @endforeach
    </div>
</div>

{{-- Company Overview --}}
<div data-panel="overview" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <h3 class="font-semibold text-navy-800 mb-4">Company Overview</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div><div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">Phone</div><div class="font-medium text-navy-800 flex items-center gap-1">{{ $dash($principal->phone) }} {!! $copyBtn($principal->phone) !!}</div></div>
        <div><div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">Email</div><div class="font-medium text-navy-800 flex items-center gap-1">{{ $dash($principal->email) }} {!! $copyBtn($principal->email) !!}</div></div>
        <div><div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">Website</div><div class="font-medium text-navy-800">@if($principal->website)<a href="{{ $principal->website }}" target="_blank" rel="noopener" class="text-[#2E74B5] hover:underline">{{ $principal->website }}</a>@else — @endif</div></div>
        <div><div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">Country</div><div class="font-medium text-navy-800">{{ $dash($principal->country) }}</div></div>
        <div class="md:col-span-2"><div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">Address</div><div class="font-medium text-navy-800 flex items-start gap-1">{{ $dash($principal->address) }} {!! $copyBtn($principal->address) !!}</div></div>
        <div class="md:col-span-2"><div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">Notes (short)</div><div class="font-medium text-navy-800">{{ $dash($principal->getRawOriginal('notes')) }}</div></div>
    </div>
    <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
        Created by <span class="font-semibold text-navy-800">{{ optional($principal->createdBy)->name ?: 'System' }}</span>
        @if($principal->created_at) on {{ $principal->created_at->format('d M Y, h:i A') }} ({{ $principal->created_at->diffForHumans() }})@endif
    </div>
</div>

{{-- Contacts --}}
<div data-panel="contacts" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <h3 class="font-semibold text-navy-800 mb-4">Contacts ({{ $principal->contacts->count() }})</h3>
    @forelse ($principal->contacts as $c)
        <div class="border border-slate-100 rounded-xl p-4 mb-3 flex items-start gap-4">
            @if ($c->photo_path)
                <img src="{{ asset('storage/'.$c->photo_path) }}" alt="" class="w-16 h-16 rounded-full object-cover ring-1 ring-slate-200 shrink-0">
            @else
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl shrink-0">{{ strtoupper(mb_substr($c->name,0,1)) }}</div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-navy-800">{{ $c->name }}</span>
                    @if($c->is_primary)<span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full">Primary</span>@endif
                </div>
                <div class="text-xs text-slate-500 mb-2">{{ $c->designation }}</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1 text-sm">
                    @if($c->phone)<div class="flex items-center gap-1"><span class="text-slate-400 text-xs w-24 shrink-0">Phone</span> <span class="text-navy-800">{{ $c->phone }}</span> {!! $copyBtn($c->phone) !!}</div>@endif
                    @if($c->email)<div class="flex items-center gap-1"><span class="text-slate-400 text-xs w-24 shrink-0">Email</span> <span class="text-navy-800 truncate">{{ $c->email }}</span> {!! $copyBtn($c->email) !!}</div>@endif
                    @if($c->whatsapp)<div class="flex items-center gap-1"><span class="text-slate-400 text-xs w-24 shrink-0">WhatsApp</span> <span class="text-navy-800">{{ $c->whatsapp }}</span> {!! $copyBtn($c->whatsapp) !!}</div>@endif
                    @if($c->wechat_id)<div class="flex items-center gap-1"><span class="text-slate-400 text-xs w-24 shrink-0">WeChat</span> <span class="text-navy-800">{{ $c->wechat_id }}</span> {!! $copyBtn($c->wechat_id) !!}</div>@endif
                    @if($c->linkedin)<div class="flex items-center gap-1"><span class="text-slate-400 text-xs w-24 shrink-0">LinkedIn</span> <a href="{{ $c->linkedin }}" target="_blank" rel="noopener" class="text-[#2E74B5] hover:underline truncate">Profile</a></div>@endif
                    @if($c->facebook)<div class="flex items-center gap-1"><span class="text-slate-400 text-xs w-24 shrink-0">Facebook</span> <a href="{{ $c->facebook }}" target="_blank" rel="noopener" class="text-[#2E74B5] hover:underline truncate">Profile</a></div>@endif
                    @if($c->office_address)<div class="md:col-span-2 flex items-start gap-1"><span class="text-slate-400 text-xs w-24 shrink-0">Office</span> <span class="text-navy-800">{{ $c->office_address }}</span></div>@endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-slate-400 text-sm">No contacts added.</div>
    @endforelse
</div>

{{-- Vessels --}}
<div data-panel="vessels" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <h3 class="font-semibold text-navy-800 mb-4">Vessels ({{ $principal->vessels->count() }})</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-slate-400 text-left border-b"><tr>
                <th class="py-2">Name</th><th>IMO</th><th>Type</th><th>GRT</th><th>DWT</th><th>Engine</th><th>BHP</th><th>Flag</th><th>Trading Area</th>
            </tr></thead>
            <tbody>
            @forelse ($principal->vessels as $v)
                <tr class="border-t">
                    <td class="py-2 font-medium text-navy-800">{{ $v->vessel_name }}</td>
                    <td>{{ $v->imo ?: '—' }}</td>
                    <td>{{ $v->vessel_type ?: '—' }}</td>
                    <td>{{ $v->grt ?: '—' }}</td>
                    <td>{{ $v->dwt ?: '—' }}</td>
                    <td>{{ $v->engine_type ?: '—' }}</td>
                    <td>{{ $v->bhp ?: '—' }}</td>
                    <td>{{ $v->flag ?: '—' }}</td>
                    <td>{{ $v->trading_area ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="py-4 text-slate-400">No vessels added.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Documents & Contract --}}
<div data-panel="docs" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <h3 class="font-semibold text-navy-800 mb-4">Document &amp; Contract</h3>
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left border-b"><tr><th class="py-2">Type</th><th>Title</th><th>Signed</th><th class="text-right">File</th></tr></thead>
        <tbody>
        @forelse ($principal->documents as $d)
            <tr class="border-t">
                <td class="py-2"><span class="px-2 py-0.5 rounded text-xs {{ $d->doc_type==='contract' ? 'bg-blue-100 text-blue-700':'bg-slate-100 text-slate-600' }}">{{ ucfirst($d->doc_type) }}</span></td>
                <td>{{ $d->title }}</td>
                <td>{{ optional($d->signed_date)->toDateString() ?: '—' }}</td>
                <td class="text-right">
                    <span class="inline-flex items-center gap-1.5">
                        <a href="{{ asset('storage/'.$d->file_path) }}" target="_blank" rel="noopener" class="text-xs font-semibold rounded-md border border-slate-300 text-slate-700 px-2.5 py-1 hover:bg-slate-100">View</a>
                        <a href="{{ asset('storage/'.$d->file_path) }}" download class="text-xs font-semibold rounded-md bg-[#1F3864] text-white px-2.5 py-1 hover:bg-[#2E4A7A]">Download</a>
                    </span>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="py-4 text-slate-400">No documents uploaded.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Crew On Board --}}
<div data-panel="onboard" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-navy-800">Crew On Board ({{ $onboard->count() }})</h3>
        <div class="flex gap-1.5">
            <a href="{{ route('principal.crewexport', ['principal'=>$principal->id,'scope'=>'onboard','export'=>'pdf']) }}" class="text-xs font-semibold rounded-md border border-slate-300 text-slate-700 px-2.5 py-1 hover:bg-slate-100">⬇ PDF</a>
            <a href="{{ route('principal.crewexport', ['principal'=>$principal->id,'scope'=>'onboard','export'=>'excel']) }}" class="text-xs font-semibold rounded-md bg-[#1F3864] text-white px-2.5 py-1 hover:bg-[#2E4A7A]">⬇ Excel</a>
        </div>
    </div>
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left border-b"><tr><th class="py-2">Crew</th><th>Rank</th><th>Vessel</th><th>Sign-on</th><th>Tenure</th></tr></thead>
        <tbody>
        @forelse ($onboard as $pl)
            <tr class="border-t">
                <td class="py-2"><a href="{{ route('crew.show', $pl->crew_profile_id) }}" class="text-[#2E74B5] hover:underline">{{ optional($pl->crewProfile)->name }}</a></td>
                <td>{{ $pl->rank }}</td>
                <td>{{ optional($pl->vessel)->vessel_name }}</td>
                <td>{{ optional($pl->sign_on_date)->toDateString() }}</td>
                <td>{{ $pl->sign_on_date ? $pl->sign_on_date->diffForHumans(null, true) : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="py-4 text-slate-400">No crew onboard. Placements arrive automatically from the Crew Selection module.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Past Crew --}}
<div data-panel="past" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-navy-800">Past Crew ({{ $past->count() }})</h3>
        <div class="flex gap-1.5">
            <a href="{{ route('principal.crewexport', ['principal'=>$principal->id,'scope'=>'past','export'=>'pdf']) }}" class="text-xs font-semibold rounded-md border border-slate-300 text-slate-700 px-2.5 py-1 hover:bg-slate-100">⬇ PDF</a>
            <a href="{{ route('principal.crewexport', ['principal'=>$principal->id,'scope'=>'past','export'=>'excel']) }}" class="text-xs font-semibold rounded-md bg-[#1F3864] text-white px-2.5 py-1 hover:bg-[#2E4A7A]">⬇ Excel</a>
        </div>
    </div>
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left border-b"><tr><th class="py-2">Crew</th><th>Rank</th><th>Vessel</th><th>Sign-off</th></tr></thead>
        <tbody>
        @forelse ($past as $pl)
            <tr class="border-t">
                <td class="py-2"><a href="{{ route('crew.show', $pl->crew_profile_id) }}" class="text-[#2E74B5] hover:underline">{{ optional($pl->crewProfile)->name }}</a></td>
                <td>{{ $pl->rank }}</td>
                <td>{{ optional($pl->vessel)->vessel_name }}</td>
                <td>{{ optional($pl->sign_off_date)->toDateString() }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="py-4 text-slate-400">No past crew records.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Salary Sheet --}}
<div data-panel="salary" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-navy-800">Salary Sheets ({{ $principal->salarySheets->count() }})</h3>
        <a href="{{ route('salary.index', ['principal_id'=>$principal->id]) }}" class="text-xs font-semibold rounded-md border border-slate-300 text-slate-700 px-2.5 py-1 hover:bg-slate-100">Open in Salary module</a>
    </div>
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left border-b"><tr><th class="py-2">Month</th><th>Vessel</th><th>Reference</th><th>Status</th><th class="text-right">Download</th></tr></thead>
        <tbody>
        @forelse ($principal->salarySheets->sortByDesc('id') as $s)
            <tr class="border-t">
                <td class="py-2 font-medium text-navy-800">{{ $s->month }}</td>
                <td>{{ optional($s->vessel)->vessel_name ?: '—' }}</td>
                <td>{{ $s->reference ?: '—' }}</td>
                <td><span class="px-2 py-0.5 rounded text-xs {{ ['draft'=>'bg-slate-100 text-slate-600','reconciled'=>'bg-amber-100 text-amber-700','locked'=>'bg-emerald-100 text-emerald-700'][$s->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($s->status) }}</span></td>
                <td class="text-right">
                    <span class="inline-flex items-center gap-1.5">
                        <a href="{{ route('salary.pdf', $s) }}" class="text-xs font-semibold rounded-md border border-slate-300 text-slate-700 px-2.5 py-1 hover:bg-slate-100">PDF</a>
                        <a href="{{ route('salary.excel', $s) }}" class="text-xs font-semibold rounded-md bg-[#1F3864] text-white px-2.5 py-1 hover:bg-[#2E4A7A]">Excel</a>
                    </span>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="py-4 text-slate-400">No salary sheets for this company yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Managing Staff --}}
<div data-panel="staff" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <h3 class="font-semibold text-navy-800 mb-3">Managing Staff / Partner ({{ $currentManagers->count() }})</h3>
    @if ($currentManagers->isNotEmpty())
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach ($currentManagers as $a)
                @php $isPartner = (optional($a->staff)->user_type ?? null) === 'partner'; @endphp
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full ring-1 text-sm {{ $isPartner ? 'bg-amber-50 ring-amber-200' : 'bg-blue-50 ring-blue-200' }}">
                    <span class="w-6 h-6 rounded-full text-white flex items-center justify-center text-[10px] font-semibold {{ $isPartner ? 'bg-[#C9A227]' : 'bg-[#1F3864]' }}">{{ strtoupper(mb_substr(optional($a->staff)->name ?: '?',0,1)) }}</span>
                    <span class="font-medium text-navy-800">{{ optional($a->staff)->name }}</span>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold {{ $isPartner ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-700' }}">{{ $isPartner ? 'Partner' : 'Staff' }}</span>
                </span>
            @endforeach
        </div>
    @else
        <div class="text-sm text-slate-400 mb-4">Unassigned.</div>
    @endif
    <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-1">Assignment history</div>
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left border-b"><tr><th class="py-2">Staff</th><th>From</th><th>To</th><th>Reason</th></tr></thead>
        <tbody>
        @forelse ($principal->assignments as $a)
            <tr class="border-t">
                <td class="py-2">{{ optional($a->staff)->name }}</td>
                <td>{{ optional($a->assigned_at)->toDateString() }}</td>
                <td>{{ $a->unassigned_at ? $a->unassigned_at->toDateString() : 'current' }}</td>
                <td class="text-slate-500">{{ $a->reason }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="py-4 text-slate-400">No assignment history.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Offences --}}
<div data-panel="offence" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <h3 class="font-semibold text-navy-800 mb-4">Offence / Incident Records ({{ $principal->offences->count() }})</h3>
    @forelse ($principal->offences as $o)
        <div class="border-t py-3 text-sm">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-semibold text-navy-800">{{ optional($o->offence_date)->toDateString() ?: 'Undated' }}</span>
                @if($o->action_taken)<span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800">{{ $o->action_taken }}</span>@endif
            </div>
            <div class="text-slate-700 mt-1">{{ $o->description }}</div>
            <div class="text-[11px] text-slate-400 mt-1">
                @if($o->source)Source: {{ $o->source }} · @endif recorded by {{ optional($o->recordedBy)->name ?: 'System' }} · {{ $o->created_at->diffForHumans() }}
            </div>
        </div>
    @empty
        <div class="text-slate-400 text-sm">No offence records.</div>
    @endforelse
</div>

{{-- Notes --}}
<div data-panel="notes" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <h3 class="font-semibold text-navy-800 mb-4">Notes ({{ $principal->companyNotes->count() }})</h3>
    @forelse ($principal->companyNotes as $n)
        <div class="border-t py-3 text-sm">
            <div class="text-slate-700 whitespace-pre-line">{{ $n->note }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ optional($n->author)->name ?: 'System' }} · {{ $n->created_at->diffForHumans() }}</div>
        </div>
    @empty
        <div class="text-slate-400 text-sm">No notes.</div>
    @endforelse
</div>

{{-- Edit Log --}}
<div data-panel="editlog" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    @include('principal.partials.edit_log')
</div>

<script>
(function () {
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.js-tabs .ptab'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('.tab-panel[data-panel]'));
    if (!tabs.length) return;

    function show(name) {
        tabs.forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-tab') === name); });
        panels.forEach(function (p) { p.classList.toggle('hidden', p.getAttribute('data-panel') !== name); });
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { show(t.getAttribute('data-tab')); }); });
    show('overview');   // first tab open by default

    // Reveal the condensed company bar on scroll (transform+opacity only → smooth).
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
@endsection
