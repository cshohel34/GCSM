@extends('layouts.app')
@section('title', $requisition->reference ?: 'REQ-'.$requisition->id)
@section('actions')
    <a href="{{ route('selection.export', ['requisition'=>$requisition->id,'export'=>'pdf']) }}" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition mr-1">⬇ PDF</a>
    <a href="{{ route('selection.export', ['requisition'=>$requisition->id,'export'=>'excel']) }}" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition mr-1">⬇ Excel</a>
    @can('selection.edit')
    <form method="POST" action="{{ route('selection.close', $requisition) }}" class="inline">@csrf
        <button class="inline-flex items-center gap-1 rounded-md border font-semibold text-sm px-3 py-1.5 transition mr-1 {{ $requisition->status==='open' ? 'border-emerald-300 text-emerald-700 hover:bg-emerald-50' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}">{{ $requisition->status==='open' ? '● Open — close it' : '○ Closed — reopen' }}</button>
    </form>
    <a href="{{ route('selection.edit', $requisition) }}" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-sm px-4 py-1.5 hover:bg-[#2E74B5] transition">✎ Edit</a>
    @endcan
@endsection
@section('content')

@php
    $ref = $requisition->reference ?: 'REQ-'.$requisition->id;
    $totalNeed = (int) $requisition->positions->sum('headcount');
    $signedOn  = (int) $requisition->positions->sum(fn ($p) => $p->countAt(['signed_on']));
    $pc = $totalNeed > 0 ? (int) round(min($signedOn, $totalNeed) / $totalNeed * 100) : 0;
    if ($pc >= 100)    { $g0='#34D399'; $g1='#059669'; $txt='text-emerald-600'; $lbl='Fulfilled'; }
    elseif ($pc >= 50) { $g0='#FBBF24'; $g1='#F59E0B'; $txt='text-amber-600';   $lbl='In progress'; }
    else               { $g0='#FB7185'; $g1='#E11D48'; $txt='text-rose-600';    $lbl='Open'; }

    $tabDefs = [
        'overview'  => 'Requirement Overview',
        'positions' => 'Positions & Candidates',
        'staff'     => 'Managing Staff / Partner',
    ];
    $logo = optional($requisition->principal)->logo_path;

    $headDeadline = $requisition->deadline;
    $headDeadlinePassed = $requisition->deadlinePassed();
    $headDaysLeft = $headDeadline ? \Carbon\Carbon::today()->diffInDays($headDeadline, false) : null;
@endphp

{{-- Header (scrolls away; a compact sticky bar stays on top) --}}
<div class="bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-start gap-5">
        @if ($logo)
            <img src="{{ asset('storage/'.$logo) }}" alt="logo" class="w-24 h-24 rounded-xl object-contain ring-1 ring-slate-200 bg-white shrink-0">
        @else
            <div class="w-24 h-24 rounded-xl bg-[#1F3864] text-white flex items-center justify-center text-2xl font-bold shrink-0">{{ strtoupper(mb_substr(optional($requisition->principal)->name ?: 'R',0,2)) }}</div>
        @endif
        <div class="flex-1 min-w-0">
            <div class="text-2xl font-bold text-navy-800 tracking-tight leading-tight">{{ optional($requisition->principal)->name }}</div>
            <div class="text-sm text-slate-500 mt-1">Ref <span class="font-medium text-slate-600">{{ $ref }}</span>
                @if($requisition->requirement_date)<span class="text-slate-300">·</span> {{ $requisition->requirement_date->toDateString() }}@endif</div>
            <div class="flex flex-wrap items-center gap-1.5 mt-3">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 {{ $requisition->status==='open' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-50 text-slate-600 ring-slate-200' }}">{{ strtoupper($requisition->status) }}</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 bg-blue-50 text-blue-700 ring-blue-200">{{ $requisition->positions->count() }} position(s)</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 bg-amber-50 text-amber-800 ring-amber-200">need {{ $totalNeed }} · signed on {{ $signedOn }}</span>
            </div>
            <div class="text-xs text-slate-500 mt-2">
                <span class="text-slate-400">Company contact:</span>
                <span class="font-semibold text-navy-800">{{ optional($requisition->contact)->name ?: '—' }}</span>
                <span class="text-slate-300 mx-1">|</span>
                <span class="text-slate-400">Created by</span>
                <span class="font-semibold text-navy-800">{{ optional($requisition->createdBy)->name ?: 'System' }}</span>
            </div>
        </div>
        {{-- Deadline highlight (replaces the progress ring) --}}
        <div class="ml-auto shrink-0 w-52">
            @if ($headDeadline)
                <div class="rounded-xl overflow-hidden shadow-sm ring-1 {{ $headDeadlinePassed ? 'ring-rose-300' : 'ring-[#C9A227]/50' }}">
                    <div class="px-4 py-1.5 bg-gradient-to-r from-[#1F3864] to-[#12233F] text-gold-300 text-[10px] font-bold uppercase tracking-[.15em] flex items-center gap-1.5">⏰ Deadline</div>
                    <div class="px-4 py-3 bg-white text-center">
                        <div class="text-xl font-extrabold text-navy-800 leading-tight">{{ $headDeadline->format('d M Y') }}</div>
                        <div class="mt-1.5">
                            @if ($headDeadlinePassed)
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-600 ring-1 ring-rose-200">Passed{{ abs($headDaysLeft) > 0 ? ' · '.abs($headDaysLeft).'d ago' : '' }}</span>
                            @elseif ($headDaysLeft === 0)
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 ring-1 ring-amber-200">Due today</span>
                            @else
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">{{ $headDaysLeft }} day{{ $headDaysLeft == 1 ? '' : 's' }} left</span>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-xl shadow-sm ring-1 ring-slate-200 bg-slate-50 px-4 py-3 text-center">
                    <div class="text-[10px] font-bold uppercase tracking-[.15em] text-slate-400">⏰ Deadline</div>
                    <div class="text-sm font-semibold text-slate-500 mt-1.5">Not set</div>
                    @can('selection.edit')<a href="{{ route('selection.edit', $requisition) }}" class="text-[11px] text-[#2E74B5] hover:underline">Set a deadline →</a>@endcan
                </div>
            @endif
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

{{-- Fixed condensed bar: appears smoothly on scroll --}}
<div id="miniBar" class="fixed top-[57px] left-64 right-0 z-30 bg-white border-b border-slate-200 shadow-md px-6 pt-2 pb-1.5">
    <div class="flex items-center gap-x-4 gap-y-1 flex-wrap text-sm">
        @if ($logo)
            <img src="{{ asset('storage/'.$logo) }}" class="w-10 h-10 rounded-lg object-contain ring-1 ring-slate-200 bg-white shrink-0" alt="">
        @else
            <div class="w-10 h-10 rounded-lg bg-[#1F3864] text-white flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(mb_substr(optional($requisition->principal)->name ?: 'R',0,2)) }}</div>
        @endif
        <div class="leading-tight shrink-0">
            <div class="text-sm font-bold text-navy-800">{{ optional($requisition->principal)->name }}</div>
            <div class="text-[11px] text-slate-500">{{ $ref }}@if($requisition->requirement_date) · {{ $requisition->requirement_date->toDateString() }}@endif</div>
        </div>
        <div class="w-px h-8 bg-slate-200 shrink-0 hidden md:block"></div>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[12px]">
            <span><span class="text-slate-400 text-[10px] uppercase">Need</span> <span class="font-medium text-navy-800">{{ $totalNeed }}</span></span>
            <span><span class="text-slate-400 text-[10px] uppercase">Signed on</span> <span class="font-medium text-navy-800">{{ $signedOn }}</span></span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium ring-1 {{ $requisition->status==='open' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-50 text-slate-600 ring-slate-200' }}">{{ strtoupper($requisition->status) }}</span>
        </div>
        <div class="ml-auto flex items-center gap-2 shrink-0">
            @if ($headDeadline)
                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold ring-1 {{ $headDeadlinePassed ? 'bg-rose-50 text-rose-600 ring-rose-200' : ($headDaysLeft === 0 ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-200') }}" title="Deadline {{ $headDeadline->format('d M Y') }}">⏰ {{ $headDeadline->format('d M Y') }}{{ $headDeadlinePassed ? ' · passed' : ($headDaysLeft === 0 ? ' · today' : ' · '.$headDaysLeft.'d left') }}</span>
            @else
                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold ring-1 bg-slate-50 text-slate-500 ring-slate-200" title="No deadline set">⏰ No deadline</span>
            @endif
        </div>
    </div>
    <div class="js-tabs flex gap-1 overflow-x-auto mt-1.5 pt-1.5 border-t border-slate-100">
        @foreach ($tabDefs as $key => $label)
            <button type="button" class="ptab" data-tab="{{ $key }}">{{ $label }}</button>
        @endforeach
    </div>
</div>

{{-- Requirement Overview --}}
<div data-panel="overview" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <h3 class="font-semibold text-navy-800 mb-4">Requirement Overview</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div><div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">Company</div><div class="font-medium text-navy-800">{{ optional($requisition->principal)->name ?: '—' }}</div></div>
        <div><div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">Reference</div><div class="font-medium text-navy-800">{{ $ref }}</div></div>
        <div><div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">Requirement date</div><div class="font-medium text-navy-800">{{ optional($requisition->requirement_date)->toDateString() ?: '—' }}</div></div>
        <div><div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">Status</div><div class="font-medium text-navy-800">{{ ucfirst($requisition->status) }}</div></div>
        <div class="md:col-span-2">
            <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">Company contact</div>
            <div class="font-medium text-navy-800">
                {{ optional($requisition->contact)->name ?: '—' }}
                @if(optional($requisition->contact)->designation)<span class="text-slate-400 font-normal">({{ $requisition->contact->designation }})</span>@endif
            </div>
            @if(optional($requisition->contact)->phone || optional($requisition->contact)->email)
                <div class="text-xs text-slate-500 mt-0.5">
                    @if($requisition->contact->phone){{ $requisition->contact->phone }}@endif
                    @if($requisition->contact->email)<span class="text-slate-300 mx-1">·</span>{{ $requisition->contact->email }}@endif
                </div>
            @endif
        </div>
        <div class="md:col-span-2"><div class="text-[11px] uppercase tracking-wide text-slate-400 mb-0.5">Notes</div><div class="font-medium text-navy-800">{{ $requisition->notes ?: '—' }}</div></div>
    </div>
    <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
        Created by <span class="font-semibold text-navy-800">{{ optional($requisition->createdBy)->name ?: 'System' }}</span>
        @if($requisition->created_at) on {{ $requisition->created_at->format('d M Y, h:i A') }} ({{ $requisition->created_at->diffForHumans() }})@endif
    </div>
</div>

{{-- Positions & Candidates --}}
@php $deadlinePassed = $requisition->deadlinePassed(); @endphp
<div data-panel="positions" class="tab-panel hidden mb-4">
    @if ($deadlinePassed)
        <div class="bg-amber-50 ring-1 ring-amber-200 text-amber-800 rounded-lg p-4 mb-4 text-sm">
            <div class="font-semibold">⏰ Deadline passed{{ optional($requisition->deadline)->format(' d M Y') ? ' — '.$requisition->deadline->format('d M Y') : '' }}</div>
            <div class="mt-1">No new position or crew can be added to this requirement. Candidates already added can still be processed through interview, service charge, sign-on and sign-off.
            @can('selection.edit')<span class="font-medium">A Super Admin can extend the deadline from <a href="{{ route('selection.edit', $requisition) }}" class="underline">Edit</a>.</span>@endcan</div>
        </div>
    @endif
    @can('selection.edit')
    @unless ($deadlinePassed)
    <div class="bg-white rounded-lg shadow p-5 mb-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-navy-800">Positions ({{ $requisition->positions->count() }})</h3>
            <button type="button" onclick="gcsmToggleBox('addPositionForm')" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">+ Add position</button>
        </div>
        <div id="addPositionForm" class="{{ $errors->has('headcount') ? '' : 'hidden' }} border-t pt-4">
            <form method="POST" action="{{ route('selection.positions.store', $requisition) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm items-end">
                @csrf
                <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Rank</label>
                    <select name="rank_id" data-placeholder="Any rank" class="w-full border border-slate-300 rounded-md px-3 py-2">@include('crew.partials.rank_options', ['selected' => ''])</select></div>
                <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Vessel <span class="normal-case text-slate-300">({{ optional($requisition->principal)->name }} only)</span></label>
                    <select name="principal_vessel_id" data-placeholder="Select vessel…" class="w-full border border-slate-300 rounded-md px-3 py-2"><option value="">—</option>
                        @foreach ($requisition->principal->vessels as $v)
                            <option value="{{ $v->id }}">{{ $v->vessel_name }}{{ $v->imo ? ' — IMO '.$v->imo : '' }}{{ $v->vessel_type ? ' · '.$v->vessel_type : '' }}</option>
                        @endforeach
                    </select></div>
                <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Headcount</label><input type="number" name="headcount" value="1" min="1" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
                <div class="flex gap-2"><button class="bg-[#1F3864] text-white font-semibold px-5 py-2 rounded-md hover:bg-[#2E74B5]">Save position</button><button type="button" onclick="gcsmToggleBox('addPositionForm')" class="border border-slate-300 text-slate-700 font-semibold px-5 py-2 rounded-md hover:bg-slate-100">Cancel</button></div>
            </form>
        </div>
    </div>
    @endunless
    @endcan

    {{-- Each position is a sub-tab so only one position's card shows at a time (clean UI) --}}
    @if ($requisition->positions->isNotEmpty())
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 px-2 py-2 mb-4">
        <div id="posTabs" class="flex flex-wrap gap-1">
            @foreach ($requisition->positions as $ptab)
                <button type="button" class="ptab" data-postab="pos{{ $ptab->id }}">
                    {{ optional($ptab->rank)->rank_name ?: 'Any rank' }}@if($ptab->vessel) · {{ $ptab->vessel->vessel_name }}@endif
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[9px] font-semibold {{ ['open'=>'bg-blue-100 text-blue-700','filled'=>'bg-emerald-100 text-emerald-700','unfulfilled'=>'bg-amber-100 text-amber-800'][$ptab->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $ptab->candidates->count() }}/{{ $ptab->headcount }}</span>
                </button>
            @endforeach
        </div>
    </div>
    @endif

    @forelse ($requisition->positions as $pos)
    <div class="pos-panel bg-white rounded-lg shadow mb-4" data-pospanel="pos{{ $pos->id }}">
        <div class="px-4 py-3 border-b flex justify-between items-center">
            <div class="font-semibold text-slate-700">
                {{ optional($pos->rank)->rank_name ?: 'Any rank' }}
                @if($pos->vessel)<span class="text-slate-400 font-normal">· {{ $pos->vessel->vessel_name }}</span>@endif
                <span class="text-slate-400 font-normal">· need {{ $pos->headcount }}</span>
                <span class="ml-2 px-2 py-0.5 rounded text-xs {{ ['open'=>'bg-blue-100 text-blue-700','filled'=>'bg-emerald-100 text-emerald-700','unfulfilled'=>'bg-amber-100 text-amber-800'][$pos->status] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($pos->status) }}</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-xs text-slate-500">
                    Wishlist {{ $pos->countAt(['wishlisted']) }} ·
                    Shortlist {{ $pos->countAt(['shortlisted']) }} ·
                    Forwarded {{ $pos->countAt(['forwarded','interview_selected','interview_passed','interview_failed','final_selected']) }} ·
                    Signed on {{ $pos->countAt(['signed_on']) }}
                </div>
                {{-- A position can only be removed while it has no crew on its candidate list. --}}
                @can('selection.edit')
                @if ($pos->candidates->isEmpty())
                <form method="POST" action="{{ route('selection.positions.destroy', [$requisition,$pos]) }}"
                      data-confirm="Remove the “{{ optional($pos->rank)->rank_name ?: 'Any rank' }}” position?"
                      data-confirm-title="Remove position" data-confirm-ok="Remove position" data-confirm-danger>
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-1 rounded-md border border-red-300 text-red-600 font-semibold text-xs px-3 py-1.5 hover:bg-red-50 transition whitespace-nowrap">🗑 Remove position</button>
                </form>
                @endif
                @endcan
            </div>
        </div>

        <div class="p-4">
            @php $isThisPos = $searchPos === $pos->id; @endphp
            @can('selection.edit')
            @unless ($deadlinePassed)
            <div class="flex items-center justify-between mb-3">
                <div class="text-[11px] uppercase tracking-wide text-slate-400">Add crew to this position</div>
                <button type="button" onclick="gcsmToggleBox('crewSearch{{ $pos->id }}')" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">+ Add crew</button>
            </div>
            <div id="crewSearch{{ $pos->id }}" class="{{ $isThisPos ? '' : 'hidden' }}">
            <form method="GET" action="{{ route('selection.show', $requisition) }}" class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-3 text-sm items-end bg-slate-50 border border-slate-100 rounded-lg p-3">
                <input type="hidden" name="pos" value="{{ $pos->id }}">
                <div>
                    <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1" title="Name / CDC / Passport / Mobile / Crew No">Name / CDC / Passport…</label>
                    <input name="q" value="{{ $isThisPos ? $q : '' }}" placeholder="Type to search crew…" class="w-full border border-slate-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Rank</label>
                    <select name="crew_rank_id" data-placeholder="Any rank" class="w-full border border-slate-300 rounded-md px-3 py-2">@include('crew.partials.rank_options', ['selected' => $isThisPos ? $searchCrewRankId : ''])</select>
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Availability</label>
                    <select name="availability" data-placeholder="Any availability" class="w-full border border-slate-300 rounded-md px-3 py-2">
                        <option value="">Any availability</option>
                        @foreach (['available'=>'Available','not_available'=>'Not available','onboard'=>'Onboard','resting'=>'Resting'] as $val => $label)
                            <option value="{{ $val }}" @selected($isThisPos && $searchAvailability===$val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Vessel type (experience)</label>
                    <select name="vessel_type" data-placeholder="Any vessel type" class="w-full border border-slate-300 rounded-md px-3 py-2">
                        <option value="">Any vessel type</option>
                        @foreach ($vesselTypes as $vt)
                            <option value="{{ $vt->type_name }}" @selected($isThisPos && $searchVesselType===$vt->type_name)>{{ $vt->type_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 flex gap-2">
                    <button class="bg-[#1F3864] text-white font-semibold rounded-md px-4 py-1.5 hover:bg-[#2E74B5]">Search crew</button>
                    @if ($isThisPos)
                        <a href="{{ route('selection.show', $requisition) }}" class="border border-slate-300 text-slate-700 font-semibold rounded-md px-4 py-1.5 hover:bg-slate-100">Clear</a>
                    @endif
                </div>
            </form>
            @if ($isThisPos && $crewMatches->isNotEmpty())
                <div class="mb-4 rounded-xl overflow-hidden shadow-sm ring-1 ring-[#C9A227]/40">
                    <div class="px-4 py-2.5 bg-gradient-to-r from-[#1F3864] to-[#12233F] flex items-center justify-between gap-3">
                        <div class="text-white font-semibold text-sm">🔎 Search results — {{ $crewMatches->count() }} crew found</div>
                        <div class="text-[11px] text-gold-300">Click “+ Wishlist” to add a crew to this position</div>
                    </div>
                    <div class="bg-white divide-y divide-slate-100">
                        @foreach ($crewMatches as $cm)
                            @php $ea = $cm->effective_availability; @endphp
                            <div class="flex items-center gap-3 px-4 py-3 hover:bg-[#FBF5E0]/60 transition">
                                @if ($cm->photo_path)
                                    <img src="{{ asset('storage/'.$cm->photo_path) }}" alt="" class="w-11 h-11 rounded-lg object-cover ring-1 ring-gold-300 shrink-0">
                                @else
                                    <div class="w-11 h-11 rounded-lg bg-[#1F3864] text-white flex items-center justify-center text-sm font-bold shrink-0">{{ strtoupper(mb_substr($cm->name,0,1)) }}</div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('crew.show', $cm) }}" target="_blank" rel="noopener" class="font-semibold text-navy-800 hover:text-[#2E74B5] hover:underline">{{ $cm->name }}</a>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium ring-1 {{ ['available'=>'bg-emerald-50 text-emerald-700 ring-emerald-200','not_available'=>'bg-slate-50 text-slate-500 ring-slate-200','onboard'=>'bg-blue-50 text-blue-700 ring-blue-200','resting'=>'bg-amber-50 text-amber-800 ring-amber-200'][$ea] ?? 'bg-slate-50 text-slate-500 ring-slate-200' }}">{{ ucfirst(str_replace('_',' ',$ea)) }}</span>
                                        @if ($cm->offences->isNotEmpty())<span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-800 ring-1 ring-amber-200">⚠ Offence</span>@endif
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap gap-x-3">
                                        <span class="text-navy-700 font-medium">{{ optional($cm->currentRank)->rank_name ?: '—' }}</span>
                                        <span><span class="text-slate-400">ID</span> {{ $cm->display_id }}</span>
                                        @if($cm->cdc_no)<span><span class="text-slate-400">CDC</span> {{ $cm->cdc_no }}</span>@endif
                                        @if($cm->passport_no)<span><span class="text-slate-400">Passport</span> {{ $cm->passport_no }}</span>@endif
                                        @if($cm->mobile)<span><span class="text-slate-400">Mobile</span> {{ $cm->mobile }}</span>@endif
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('selection.candidates.store', $pos) }}" class="shrink-0">@csrf
                                    <input type="hidden" name="crew_profile_id" value="{{ $cm->id }}">
                                    <button class="inline-flex items-center justify-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-4 py-2 hover:bg-[#2E74B5] transition shadow-sm">+ Wishlist</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif ($isThisPos && ($q !== '' || $searchAvailability !== '' || $searchVesselType !== '' || $searchCrewRankId !== ''))
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm">
                    No matching crew found — they may already be on this position's list. Try changing the filters.
                </div>
            @endif
            </div>
            @endunless
            @endcan

            @php
                $stageStyles = [
                    'wishlisted'         => 'bg-slate-100 text-slate-700 ring-slate-200',
                    'shortlisted'        => 'bg-blue-50 text-blue-700 ring-blue-200',
                    'forwarded'           => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                    'rejected_by_company' => 'bg-rose-50 text-rose-700 ring-rose-200',
                    'interview_selected' => 'bg-amber-50 text-amber-800 ring-amber-200',
                    'interview_passed'   => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                    'interview_failed'   => 'bg-rose-50 text-rose-700 ring-rose-200',
                    'final_selected'     => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                    'signed_on'          => 'bg-emerald-600 text-white ring-emerald-600',
                ];
            @endphp
            <div class="rounded-xl ring-1 ring-slate-200 overflow-hidden">
                <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Candidates ({{ $pos->candidates->count() }})
                </div>
                <div class="bg-white divide-y divide-slate-100">
                @forelse ($pos->candidates as $cand)
                    @php $crew = $cand->crewProfile; @endphp
                    <div class="p-4 hover:bg-slate-50/70 transition">
                      <div class="flex items-start gap-3">
                        @if ($crew->photo_path)
                            <img src="{{ asset('storage/'.$crew->photo_path) }}" alt="" class="w-11 h-11 rounded-lg object-cover ring-1 ring-gold-300 shrink-0">
                        @else
                            <div class="w-11 h-11 rounded-lg bg-[#1F3864] text-white flex items-center justify-center text-sm font-bold shrink-0">{{ strtoupper(mb_substr($crew->name,0,1)) }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('crew.show', $crew) }}" target="_blank" rel="noopener" class="font-semibold text-navy-800 hover:text-[#2E74B5] hover:underline">{{ $crew->name }}</a>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold ring-1 {{ $stageStyles[$cand->stage] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">{{ $cand->stageLabel() }}</span>
                                @if ($crew->offences->isNotEmpty())<span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-800 ring-1 ring-amber-200">⚠ Offence</span>@endif
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap gap-x-3">
                                <span class="text-navy-700 font-medium">{{ optional($crew->currentRank)->rank_name ?: '—' }}</span>
                                <span><span class="text-slate-400">ID</span> {{ $crew->display_id }}</span>
                                @if($crew->cdc_no)<span><span class="text-slate-400">CDC</span> {{ $crew->cdc_no }}</span>@endif
                                @if($crew->mobile)<span><span class="text-slate-400">Mobile</span> {{ $crew->mobile }}</span>@endif
                            </div>
                            <div class="text-xs mt-1.5">
                                @if ($cand->stage==='rejected_by_company')
                                    <span class="text-rose-600 font-medium">✕ Rejected by company</span>
                                    <span class="text-slate-500"> — {{ $cand->fail_reason ?: 'no reason recorded' }}</span>
                                @elseif ($cand->stage==='interview_failed')
                                    <span class="text-rose-600">Reason: {{ $cand->fail_reason ?: '—' }}</span>
                                @elseif ($cand->stage==='signed_on')
                                    <span class="text-emerald-700 font-medium">Placed onboard</span>@if($cand->interview_date)<span class="text-slate-400"> · interview {{ $cand->interview_date->toDateString() }}</span>@endif
                                @elseif (in_array($cand->stage, ['interview_passed','final_selected']))
                                    @if ($cand->service_charge_decided)
                                        @if ($cand->service_charge)
                                            <span class="text-emerald-600">✓ Service charge ৳{{ number_format($cand->service_charge,2) }}</span>
                                        @else
                                            <span class="text-slate-600">No service charge — {{ $cand->no_charge_reason }}</span>
                                        @endif
                                    @else
                                        <span class="text-amber-600">○ Service charge not decided</span>
                                    @endif
                                    @if ($cand->stage==='final_selected' && $cand->confirmed_at)<span class="text-slate-400"> · confirmed {{ $cand->confirmed_at->toDateString() }}</span>@endif
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-1.5 shrink-0">
                            @can('selection.edit')
                                @if ($cand->stage === 'shortlisted')
                                    <form method="POST" action="{{ route('selection.candidates.stage', $cand) }}">@csrf
                                        <input type="hidden" name="stage" value="wishlisted">
                                        <button class="inline-flex items-center justify-center gap-1 rounded-md border border-[#1F3864]/40 bg-white text-[#1F3864] font-semibold text-xs px-3 py-1.5 hover:bg-[#1F3864] hover:text-white hover:border-[#1F3864] transition" title="Move back to the wishlist">↩ Back to wishlist</button>
                                    </form>
                                @endif

                                {{-- Company review of the forwarded CV: select for interview or reject (with reason) --}}
                                @if ($cand->stage === 'forwarded')
                                    <form id="outcomeForm_{{ $cand->id }}" method="POST" action="{{ route('selection.candidates.interview', $cand) }}" class="hidden">@csrf
                                        <input type="hidden" name="result" id="outcomeResult_{{ $cand->id }}">
                                        <input type="hidden" name="fail_reason" id="outcomeReason_{{ $cand->id }}">
                                    </form>
                                    <button type="button" onclick="gcsmSubmitOutcome({{ $cand->id }}, 'interview_selected')"
                                            class="inline-flex items-center justify-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">✓ Selected for interview</button>
                                    <button type="button" onclick="gcsmAskReason({{ $cand->id }}, 'rejected_by_company', 'Rejected by company', @js('Why is the company rejecting '.$crew->name.'?'))"
                                            class="inline-flex items-center justify-center gap-1 rounded-md border border-red-300 text-red-600 font-semibold text-xs px-3 py-1.5 hover:bg-red-50 transition">✕ Rejected by company</button>
                                @endif

                                {{-- Interview result: passed, or failed (with reason). Date optional. --}}
                                @if ($cand->stage === 'interview_selected')
                                    <form id="outcomeForm_{{ $cand->id }}" method="POST" action="{{ route('selection.candidates.interview', $cand) }}" class="hidden">@csrf
                                        <input type="hidden" name="result" id="outcomeResult_{{ $cand->id }}">
                                        <input type="hidden" name="fail_reason" id="outcomeReason_{{ $cand->id }}">
                                    </form>
                                    <button type="button" onclick="gcsmAskDate({ action: @js(route('selection.candidates.interview', $cand)), title: 'Interview passed', label: 'Interview date', dateName: 'interview_date', value: @js(optional($cand->interview_date)->toDateString()), ok: 'Mark passed', extra: { result: 'interview_passed' } })"
                                            class="inline-flex items-center justify-center gap-1 rounded-md bg-emerald-600 text-white font-semibold text-xs px-3 py-1.5 hover:bg-emerald-700 transition">✓ Interview passed</button>
                                    <button type="button" onclick="gcsmAskReason({{ $cand->id }}, 'interview_failed', 'Interview failed', @js('Why did '.$crew->name.' fail the interview?'))"
                                            class="inline-flex items-center justify-center gap-1 rounded-md border border-red-300 text-red-600 font-semibold text-xs px-3 py-1.5 hover:bg-red-50 transition">✕ Interview failed</button>
                                @endif
                            @endcan
                            <a href="{{ route('crew.show', $crew) }}" target="_blank" rel="noopener" title="Open {{ $crew->name }}'s profile in a new tab"
                               class="inline-flex items-center justify-center gap-1 rounded-md border border-[#1F3864]/40 bg-white text-[#1F3864] font-semibold text-xs px-3 py-1.5 hover:bg-[#1F3864] hover:text-white hover:border-[#1F3864] transition">👁 View Profile</a>
                            @can('selection.edit')
                            @switch($cand->stage)
                                @case('wishlisted')
                                    <form method="POST" action="{{ route('selection.candidates.stage', $cand) }}">@csrf<input type="hidden" name="stage" value="shortlisted"><button class="inline-flex items-center justify-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">Shortlist</button></form>
                                    @break
                                @case('shortlisted')
                                    <form method="POST" action="{{ route('selection.candidates.forward', $cand) }}">@csrf<button class="inline-flex items-center justify-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition" title="Emails CV to principal">Forward + email</button></form>
                                    @break
                                @case('rejected_by_company')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-rose-50 text-rose-700 ring-1 ring-rose-200 font-semibold text-xs">✕ Rejected by company</span>
                                    @break
                                @case('interview_passed')
                                @case('final_selected')
                                    @if ($cand->stage === 'interview_passed')
                                        <button type="button" onclick="gcsmAskDate({ action: @js(route('selection.candidates.stage', $cand)), title: 'Confirm selection', label: 'Selection confirmed on', dateName: 'confirmed_at', ok: 'Confirm selection', extra: { stage: 'final_selected' } })"
                                                class="inline-flex items-center justify-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">✓ Confirm selection</button>
                                    @endif
                                    <button type="button" onclick="gcsmAskDate({ action: @js(route('selection.candidates.signon', $cand)), title: 'Sign on', label: 'Sign-on date', dateName: 'sign_on_date', ok: 'Sign on', fields: [{ name: 'expected_joining_date', label: 'Expected date of joining', type: 'date' }, { name: 'salary', label: 'Salary (per month)', type: 'number', step: '0.01', placeholder: 'e.g. 3500' }, { name: 'place_of_joining', label: 'Place of joining', type: 'text', placeholder: 'e.g. Singapore' }] })"
                                            class="inline-flex items-center justify-center gap-1 rounded-md bg-emerald-600 text-white font-semibold text-xs px-3 py-1.5 hover:bg-emerald-700 transition">⚓ Sign on</button>
                                    {{-- The letter needs the sign-on details (date, expected joining, salary, place),
                                         so it stays locked until the "Sign on" form has been completed. --}}
                                    <span title="Complete the Sign on form first" aria-disabled="true"
                                          class="inline-flex items-center justify-center gap-1 rounded-md border border-slate-200 bg-slate-100 text-slate-400 font-semibold text-xs px-3 py-1.5 cursor-not-allowed select-none">🔒 Sign On Letter (PDF)</span>
                                    @break
                                @case('signed_on')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 font-semibold text-xs">✓ Onboard</span>
                                    <a href="{{ route('selection.signon.letter', $cand) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-1 rounded-md border border-[#1F3864]/40 bg-white text-[#1F3864] font-semibold text-xs px-3 py-1.5 hover:bg-[#1F3864] hover:text-white transition">⬇ Sign On Letter (PDF)</a>
                                    @if (optional($cand->placement)->status === 'onboard')
                                        <button type="button" onclick="gcsmSignOff({ action: @js(route('selection.candidates.signoff', $cand)), signOnDate: @js(optional(optional($cand->placement)->sign_on_date)->toDateString()) })"
                                                class="inline-flex items-center justify-center gap-1 rounded-md bg-slate-700 text-white font-semibold text-xs px-3 py-1.5 hover:bg-slate-800 transition">⚓ Sign off</button>
                                    @elseif (optional($cand->placement)->status === 'signed_off')
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-slate-100 text-slate-500 ring-1 ring-slate-200 font-semibold text-xs">🏁 Signed off</span>
                                    @endif
                                    @break
                            @endswitch
                            {{-- Once the CV has been forwarded to the company the candidate is part of the
                                 record and can no longer be removed. --}}
                            @if (in_array($cand->stage, ['wishlisted', 'shortlisted'], true))
                                <form method="POST" action="{{ route('selection.candidates.destroy', $cand) }}" class="inline"
                                      data-confirm="Remove {{ $crew->name }} from this position's candidate list?"
                                      data-confirm-title="Remove candidate" data-confirm-ok="Remove candidate" data-confirm-danger>
                                    @csrf @method('DELETE')<button class="inline-flex items-center justify-center gap-1 rounded-md border border-red-300 text-red-600 font-semibold text-xs px-3 py-1.5 hover:bg-red-50 transition">Remove</button></form>
                            @endif
                            @endcan
                        </div>
                      </div>

                      {{-- Service charge decision (opens a themed popup: Yes → amount → auto-draft journal / No → reason) --}}
                      @if (in_array($cand->stage, ['interview_passed','final_selected']) && auth()->user()->can('selection.edit'))
                        <div class="mt-3 ml-0 md:ml-14 rounded-lg bg-slate-50 ring-1 ring-slate-200 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-[11px] uppercase tracking-wide text-slate-400 font-semibold">Service Charge</div>
                                    <div class="text-sm mt-0.5">
                                        @if ($cand->service_charge_decided)
                                            @if ($cand->service_charge)
                                                <span class="text-emerald-700 font-semibold">Yes — ৳{{ number_format($cand->service_charge, 2) }}</span>
                                                @if ($cand->service_charge_txn_id)<span class="text-slate-400 text-xs"> · drafted to accounting journal</span>@endif
                                            @else
                                                <span class="text-navy-800 font-semibold">No service charge</span> <span class="text-slate-500 text-xs">— {{ $cand->no_charge_reason }}</span>
                                            @endif
                                        @else
                                            <span class="text-amber-600">Not decided yet — choose Yes (amount) or No (reason).</span>
                                        @endif
                                    </div>
                                </div>
                                <button type="button" onclick="gcsmAskCharge({ action: @js(route('selection.candidates.charge', $cand)), amount: @js((string) ($cand->service_charge ?: '')), reason: @js((string) ($cand->no_charge_reason ?: '')) })"
                                        class="shrink-0 inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">{{ $cand->service_charge_decided ? 'Change' : 'Set service charge' }}</button>
                            </div>
                        </div>
                      @endif

                      {{-- Document checklist — available at every stage, auto-mapped from the crew profile --}}
                      @php
                          $chk = $cand->checklistItems;
                          $chkTotal = $chk->count();
                          $chkDone  = $chk->where('is_received', true)->count();
                          $chkPct   = $chkTotal ? (int) round($chkDone / $chkTotal * 100) : 0;
                          $chkBar   = $chkPct >= 100 ? 'bg-emerald-500' : ($chkPct >= 50 ? 'bg-[#C9A227]' : 'bg-rose-400');
                      @endphp
                      <div class="mt-3 ml-0 md:ml-14 rounded-lg bg-slate-50 ring-1 ring-slate-200 overflow-hidden">
                          <button type="button" onclick="gcsmToggleBox('checklist_{{ $cand->id }}')" class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-slate-100 transition">
                              <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 shrink-0">📋 Document Checklist</span>
                              <div class="flex-1 h-2 rounded-full bg-slate-200 overflow-hidden">
                                  <div class="h-full {{ $chkBar }} transition-all" style="width: {{ $chkPct }}%"></div>
                              </div>
                              <span class="text-xs font-bold {{ $chkPct>=100 ? 'text-emerald-600' : ($chkPct>=50 ? 'text-amber-600' : 'text-rose-600') }} shrink-0">{{ $chkPct }}%</span>
                              <span class="text-[11px] text-slate-400 shrink-0">{{ $chkDone }}/{{ $chkTotal }} received</span>
                              <span class="text-slate-300 shrink-0">▾</span>
                          </button>

                          <div id="checklist_{{ $cand->id }}" class="hidden border-t border-slate-200 p-3">
                              <div class="flex items-center justify-end gap-2 mb-2">
                                  <a href="{{ route('selection.checklist.pdf', $cand) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition" title="Download this crew's checklist on the GCSM letterhead">⬇ Download checklist</a>
                                  @can('selection.edit')
                                  <form method="POST" action="{{ route('selection.checklist.remap', $cand) }}">@csrf
                                      <button class="inline-flex items-center gap-1 rounded-md border border-[#1F3864]/40 bg-white text-[#1F3864] font-semibold text-xs px-3 py-1.5 hover:bg-[#1F3864] hover:text-white transition" title="Re-scan the crew profile and tick documents that are on file">↻ Re-map from profile</button>
                                  </form>
                                  @endcan
                              </div>

                              <div class="rounded-lg ring-1 ring-slate-200 bg-white overflow-hidden">
                                  <div class="hidden md:flex items-center gap-3 px-3 py-2 bg-slate-50 border-b border-slate-200 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                      <div class="w-7 shrink-0 text-center">SL</div>
                                      <div class="w-52 shrink-0">Document</div>
                                      <div class="w-24 shrink-0">Status</div>
                                      <div class="w-40 shrink-0">File</div>
                                      <div class="flex-1 min-w-0">Remarks</div>
                                      <div class="w-6 shrink-0"></div>
                                  </div>
                                  @forelse ($chk as $it)
                                      @php $autoLocked = \App\Services\CandidateChecklist::isAutoCode($it->code); @endphp
                                      <div class="flex flex-wrap md:flex-nowrap items-start gap-3 px-3 py-2.5 {{ $loop->first ? '' : 'border-t border-slate-100' }}">
                                          {{-- Serial --}}
                                          <div class="w-7 shrink-0 text-xs text-slate-400 pt-1.5 text-center">{{ $loop->iteration }}</div>

                                          {{-- Document name --}}
                                          <div class="w-52 shrink-0">
                                              <div class="text-sm font-medium text-navy-800">
                                                  {{ $it->item }}
                                                  @unless ($it->code)<span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-semibold bg-slate-100 text-slate-500 align-middle">Custom</span>@endunless
                                              </div>
                                              @if ($it->auto_source)<div class="text-[11px] text-emerald-600">✓ {{ $it->auto_source }}</div>@endif
                                              @if (! $it->is_received)<div class="text-[11px] text-amber-600">To collect — add it in the crew profile</div>@endif
                                          </div>

                                          {{-- Status --}}
                                          <div class="w-24 shrink-0">
                                              @if ($autoLocked)
                                                  <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-1 rounded-md ring-1 {{ $it->is_received ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-rose-50 text-rose-600 ring-rose-200' }}" title="Mapped automatically from the crew profile — locked">🔒 {{ $it->is_received ? 'Yes' : 'No' }}</span>
                                              @elseif (auth()->user()->can('selection.edit'))
                                                  <form method="POST" action="{{ route('selection.checklist.status', $it) }}">@csrf
                                                      <select name="is_received" onchange="this.form.submit()" class="no-enhance text-xs font-semibold rounded-md border px-2 py-1 {{ $it->is_received ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200' }}">
                                                          <option value="1" @selected($it->is_received)>Yes</option>
                                                          <option value="0" @selected(! $it->is_received)>No</option>
                                                      </select>
                                                  </form>
                                              @else
                                                  <span class="inline-flex text-[11px] font-semibold px-2 py-1 rounded-md ring-1 {{ $it->is_received ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-rose-50 text-rose-600 ring-rose-200' }}">{{ $it->is_received ? 'Yes' : 'No' }}</span>
                                              @endif
                                          </div>

                                          {{-- File: view / download (only when a scan is on file) --}}
                                          <div class="w-40 shrink-0 pt-0.5">
                                              @php
                                                  $fileUrl = ($it->code === 'cv' && $it->is_received)
                                                      ? route('crew.cv.pdf', $crew)
                                                      : ($it->evidence_path ? asset('storage/'.$it->evidence_path) : null);
                                              @endphp
                                              @if ($fileUrl)
                                                  <div class="flex items-center gap-1.5">
                                                      <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="text-[11px] font-semibold rounded-md border border-[#1F3864]/40 bg-white text-[#1F3864] px-2.5 py-1 hover:bg-[#1F3864] hover:text-white transition">View</a>
                                                      <a href="{{ $fileUrl }}" download class="text-[11px] font-semibold rounded-md bg-[#1F3864] text-white px-2.5 py-1 hover:bg-[#2E74B5] transition">Download</a>
                                                  </div>
                                              @else
                                                  <span class="text-slate-300 text-xs">—</span>
                                              @endif
                                          </div>

                                          {{-- Remarks --}}
                                          <div class="flex-1 min-w-0">
                                              @can('selection.edit')
                                                  <form method="POST" action="{{ route('selection.checklist.remark', $it) }}" class="flex items-center gap-1">@csrf
                                                      <input name="notes" value="{{ $it->notes }}" placeholder="Add a remark…" class="w-full border border-slate-200 rounded px-2 py-1 text-[11px]">
                                                      <button class="text-[11px] font-semibold text-[#1F3864] hover:underline shrink-0">Save</button>
                                                  </form>
                                                  @if ($it->remark_by)<div class="text-[10px] text-slate-400 mt-0.5">edited by {{ optional($it->remarkBy)->name ?: 'System' }} · {{ optional($it->remark_at)->diffForHumans() }}</div>@endif
                                              @else
                                                  <div class="text-[11px] text-slate-600">{{ $it->notes ?: '—' }}</div>
                                                  @if ($it->remark_by)<div class="text-[10px] text-slate-400">by {{ optional($it->remarkBy)->name }}</div>@endif
                                              @endcan
                                          </div>

                                          {{-- Remove (custom only) --}}
                                          <div class="w-6 shrink-0 pt-1.5 text-right">
                                              @can('selection.edit')
                                                  @unless ($it->code)
                                                      <form method="POST" action="{{ route('selection.checklist.destroy', $it) }}"
                                                            data-confirm="Remove “{{ $it->item }}” from the checklist?" data-confirm-title="Remove item" data-confirm-ok="Remove" data-confirm-danger>
                                                          @csrf @method('DELETE')<button class="text-red-400 text-xs hover:text-red-600" title="Remove custom item">✕</button></form>
                                                  @endunless
                                              @endcan
                                          </div>
                                      </div>
                                  @empty
                                      <div class="px-3 py-3 text-xs text-slate-400">No checklist items.</div>
                                  @endforelse
                              </div>

                              @can('selection.edit')
                              <form method="POST" action="{{ route('selection.checklist.store', $cand) }}" class="flex gap-2 mt-3">@csrf
                                  <input name="item" required placeholder="Add a custom checklist item (e.g. specific short course)" class="flex-1 border border-slate-300 rounded-md px-3 py-1.5 text-xs">
                                  <button class="inline-flex items-center justify-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-4 py-1.5 hover:bg-[#2E74B5] transition">+ Add item</button>
                              </form>
                              @endcan
                          </div>
                      </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-sm">No candidates yet — search crew above to build the wishlist.</div>
                @endforelse
                </div>
            </div>

            @can('selection.edit')
            @if ($pos->status !== 'unfulfilled')
            <form method="POST" action="{{ route('selection.positions.unfulfilled', [$requisition,$pos]) }}" class="flex gap-1 mt-3 text-xs">@csrf
                <input name="unfulfilled_reason" placeholder="If this position can't be filled, record why…" class="border rounded px-2 py-1 w-80">
                <button class="border rounded px-2 py-1 text-slate-600">Mark unfulfilled</button>
            </form>
            @else
                <div class="text-xs text-amber-700 mt-3">Unfulfilled: {{ $pos->unfulfilled_reason }}</div>
            @endif
            @endcan
        </div>
    </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center text-slate-400">No positions yet. Click <span class="font-semibold">+ Add position</span> to add one.</div>
    @endforelse
</div>

{{-- Managing Staff / Partner --}}
<div data-panel="staff" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-navy-800">Managing Staff / Partner ({{ $requisition->assignedStaff->count() }})</h3>
        @can('selection.edit')
            <button type="button" onclick="gcsmToggleBox('reqStaffForm')" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">+ Add staff / partner</button>
        @endcan
    </div>

    @forelse ($requisition->assignedStaff as $u)
        @php $isPartner = ($u->user_type ?? null) === 'partner'; @endphp
        <div class="border rounded-xl p-3 mb-2 flex items-center gap-3 text-sm {{ $isPartner ? 'border-amber-200 bg-amber-50/40' : 'border-blue-200 bg-blue-50/40' }}">
            <span class="w-9 h-9 rounded-full text-white flex items-center justify-center text-xs font-semibold shrink-0 {{ $isPartner ? 'bg-[#C9A227]' : 'bg-[#1F3864]' }}">{{ strtoupper(mb_substr($u->name,0,1)) }}</span>
            <div class="flex-1 min-w-0">
                <div class="font-semibold text-navy-800">{{ $u->name }} <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-semibold {{ $isPartner ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-700' }}">{{ $isPartner ? 'Partner' : 'Staff' }}</span></div>
                @if($u->pivot->note)<div class="text-[11px] text-slate-400">{{ $u->pivot->note }}</div>@endif
            </div>
            @can('selection.edit')
                <form method="POST" action="{{ route('selection.staff.destroy', [$requisition, $u]) }}"
                      data-confirm="Remove {{ $u->name }} from the staff / partners managing this requirement?"
                      data-confirm-title="Remove staff / partner" data-confirm-ok="Remove" data-confirm-danger>@csrf @method('DELETE')
                    <button class="text-red-500 text-xs font-semibold hover:underline">Remove</button>
                </form>
            @endcan
        </div>
    @empty
        <div class="text-slate-400 text-sm mb-2">No staff or partner assigned yet.</div>
    @endforelse

    @can('selection.edit')
    <div id="reqStaffForm" class="{{ $errors->has('user_id') ? '' : 'hidden' }} border-t pt-4 mt-2">
        <form method="POST" action="{{ route('selection.staff.store', $requisition) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            @csrf
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Staff / Partner *</label>
                <select name="user_id" required data-placeholder="Select staff / partner…" class="w-full border border-slate-300 rounded-md px-3 py-2">
                    <option value="">Select staff / partner…</option>
                    @foreach ($staff as $u)<option value="{{ $u->id }}">{{ $u->name }}{{ ($u->user_type ?? null)==='partner' ? ' (Partner)' : ' (Staff)' }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Note / responsibility</label>
                <input name="note" class="w-full border border-slate-300 rounded-md px-3 py-2">
            </div>
            <div class="flex items-end gap-2">
                <button class="bg-[#1F3864] text-white font-semibold px-5 py-2 rounded-md hover:bg-[#2E74B5]">Assign</button>
                <button type="button" onclick="gcsmToggleBox('reqStaffForm')" class="border border-slate-300 text-slate-700 font-semibold px-5 py-2 rounded-md hover:bg-slate-100">Cancel</button>
            </div>
        </form>
    </div>
    @endcan
</div>

{{-- Reason dialog for outcomes that must be explained (rejection / interview failure) --}}
<div id="gcsmReasonModal" class="hidden fixed inset-0 z-[130] items-center justify-center bg-[#12233F]/60 backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden ring-1 ring-slate-200">
        <div class="px-5 py-4 bg-gradient-to-r from-[#1F3864] to-[#12233F] flex items-center gap-3">
            <span class="w-9 h-9 rounded-full bg-red-500/20 text-red-200 flex items-center justify-center text-lg">✕</span>
            <div>
                <div id="gcsmReasonTitle" class="text-white font-bold leading-tight">Reason</div>
                <div class="text-[11px] text-gold-300">A written reason is required</div>
            </div>
        </div>
        <div class="p-5">
            <p id="gcsmReasonPrompt" class="text-sm text-slate-600 mb-3"></p>
            <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Reason *</label>
            <textarea id="gcsmReasonText" rows="3" maxlength="500" placeholder="Write the reason…"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864]"></textarea>
            <div id="gcsmReasonError" class="hidden text-xs text-red-600 mt-1">Please write the reason.</div>
            <div class="flex items-center justify-end gap-2 pt-4">
                <button type="button" id="gcsmReasonCancel" class="rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-4 py-1.5 hover:bg-slate-100">Cancel</button>
                <button type="button" id="gcsmReasonSave" class="rounded-md bg-red-600 text-white font-semibold text-sm px-4 py-1.5 hover:bg-red-700">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
function gcsmSubmitOutcome(id, result) {
    var form = document.getElementById('outcomeForm_' + id);
    var res  = document.getElementById('outcomeResult_' + id);
    if (!form || !res) return;
    res.value = result;
    if (typeof form.requestSubmit === 'function') { form.requestSubmit(); } else { form.submit(); }
}
var gcsmReasonTarget = null;
function gcsmAskReason(id, result, title, prompt) {
    var m = document.getElementById('gcsmReasonModal');
    if (!m) return;
    gcsmReasonTarget = { id: id, result: result };
    document.getElementById('gcsmReasonTitle').textContent = title;
    document.getElementById('gcsmReasonPrompt').textContent = prompt || '';
    var t = document.getElementById('gcsmReasonText');
    t.value = '';
    document.getElementById('gcsmReasonError').classList.add('hidden');
    m.classList.remove('hidden'); m.classList.add('flex');
    setTimeout(function () { try { t.focus(); } catch (e) {} }, 40);
}
(function () {
    var m = document.getElementById('gcsmReasonModal');
    if (!m) return;
    function close() { m.classList.add('hidden'); m.classList.remove('flex'); gcsmReasonTarget = null; }
    document.getElementById('gcsmReasonCancel').addEventListener('click', close);
    m.addEventListener('click', function (e) { if (e.target === m) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !m.classList.contains('hidden')) close(); });
    document.getElementById('gcsmReasonSave').addEventListener('click', function () {
        if (!gcsmReasonTarget) return close();
        var text = document.getElementById('gcsmReasonText').value.trim();
        if (!text) { document.getElementById('gcsmReasonError').classList.remove('hidden'); return; }
        var reason = document.getElementById('outcomeReason_' + gcsmReasonTarget.id);
        if (reason) reason.value = text;
        var t = gcsmReasonTarget;
        close();
        gcsmSubmitOutcome(t.id, t.result);
    });
})();

function gcsmToggleBox(id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('hidden');
    if (!el.classList.contains('hidden')) {
        var f = el.querySelector('select, input, textarea');
        if (f) { try { f.focus(); } catch (e) {} }
    }
}
(function () {
    var KEY = 'gcsmRequirementTab:{{ $requisition->id }}';
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.js-tabs .ptab'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('.tab-panel[data-panel]'));
    if (!tabs.length) return;

    function hasPanel(name) { return name && document.querySelector('.tab-panel[data-panel="' + name + '"]'); }
    function show(name) {
        tabs.forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-tab') === name); });
        panels.forEach(function (p) { p.classList.toggle('hidden', p.getAttribute('data-panel') !== name); });
        try { sessionStorage.setItem(KEY, name); } catch (e) {}
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { show(t.getAttribute('data-tab')); }); });

    // Priority: crew-search / validation activity → last tab used → Overview.
    var forced = @json($searchPos ? 'positions' : ($errors->has('user_id') ? 'staff' : ($errors->has('headcount') ? 'positions' : null)));
    if (hasPanel(forced)) {
        show(forced);
    } else {
        var stored = null;
        try { stored = sessionStorage.getItem(KEY); } catch (e) {}
        show(hasPanel(stored) ? stored : 'overview');
    }

    // Reveal the condensed bar on scroll.
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

    // ---- Position sub-tabs: show one position's card at a time ----
    (function () {
        var PKEY = 'gcsmReqPosTab:{{ $requisition->id }}';
        var tabs = Array.prototype.slice.call(document.querySelectorAll('#posTabs .ptab'));
        var panels = Array.prototype.slice.call(document.querySelectorAll('.pos-panel[data-pospanel]'));
        if (!tabs.length) return;

        function hasPos(name) { return name && document.querySelector('.pos-panel[data-pospanel="' + name + '"]'); }
        function showPos(name) {
            panels.forEach(function (p) { p.classList.toggle('hidden', p.getAttribute('data-pospanel') !== name); });
            tabs.forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-postab') === name); });
            try { sessionStorage.setItem(PKEY, name); } catch (e) {}
        }
        tabs.forEach(function (t) { t.addEventListener('click', function () { showPos(t.getAttribute('data-postab')); }); });

        // Prefer the position that a crew search targeted; else the last one used; else the first.
        var searchPanel = document.querySelector('.pos-panel [id^="crewSearch"]:not(.hidden)');
        var forcedPos = searchPanel ? searchPanel.closest('.pos-panel').getAttribute('data-pospanel') : null;
        var stored = null; try { stored = sessionStorage.getItem(PKEY); } catch (e) {}
        showPos(hasPos(forcedPos) ? forcedPos : (hasPos(stored) ? stored : tabs[0].getAttribute('data-postab')));
    })();
})();
</script>
@endsection
