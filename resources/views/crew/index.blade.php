@extends('layouts.app')
@section('title', 'Crew Management')
@section('actions')
    <a href="{{ route('crew.export', array_merge(request()->query(), ['export'=>'pdf'])) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a>
    <a href="{{ route('crew.export', array_merge(request()->query(), ['export'=>'excel'])) }}" class="border px-3 py-1.5 rounded text-sm mr-1">Excel</a>
    @role('Super Admin')<a href="{{ route('crew.trash') }}" title="View & restore deleted crew profiles" class="border border-slate-300 text-slate-700 px-3 py-1.5 rounded text-sm hover:bg-slate-100 mr-1">🗑 Recycle Bin</a>@endrole
    @can('crew.create')<a href="{{ route('crew.create') }}" class="bg-[#1F3864] text-white px-3 py-1.5 rounded text-sm hover:bg-[#2E74B5]">+ New Crew</a>@endcan
@endsection
@section('content')
<form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
    <input name="name" value="{{ $filters['name'] ?? '' }}" placeholder="Name" class="border rounded px-2 py-1.5">
    <input name="cdc_no" value="{{ $filters['cdc_no'] ?? '' }}" placeholder="CDC No" class="border rounded px-2 py-1.5">
    <input name="passport_no" value="{{ $filters['passport_no'] ?? '' }}" placeholder="Passport No" class="border rounded px-2 py-1.5">
    <input name="coc_no" value="{{ $filters['coc_no'] ?? '' }}" placeholder="COC No" class="border rounded px-2 py-1.5">
    <input name="mobile" value="{{ $filters['mobile'] ?? '' }}" placeholder="Mobile" class="border rounded px-2 py-1.5">
    <input name="admission_id" value="{{ $filters['admission_id'] ?? '' }}" placeholder="Admission ID" class="border rounded px-2 py-1.5">
    <select name="rank_id" data-placeholder="Any rank" class="border rounded px-2 py-1.5">@include('crew.partials.rank_options', ['selected' => $filters['rank_id'] ?? ''])</select>
    <select name="availability" class="border rounded px-2 py-1.5">
        <option value="">Any availability</option>
        <option value="available" @selected(($filters['availability'] ?? '') === 'available')>Available</option>
        <option value="resting" @selected(($filters['availability'] ?? '') === 'resting')>Resting</option>
        <option value="onboard" @selected(($filters['availability'] ?? '') === 'onboard')>Onboard</option>
        <option value="not_available" @selected(($filters['availability'] ?? '') === 'not_available')>Not available</option>
    </select>
    <input name="company_name" value="{{ $filters['company_name'] ?? '' }}" placeholder="Company (worked)" class="border rounded px-2 py-1.5">
    <select name="vessel_type" data-placeholder="Any vessel type" class="border rounded px-2 py-1.5">
        <option value="">Any vessel type</option>
        @foreach ($vesselTypes as $vt)<option value="{{ $vt->type_name }}" @selected(($filters['vessel_type'] ?? '') === $vt->type_name)>{{ $vt->type_name }}</option>@endforeach
    </select>
    <div class="col-span-2 md:col-span-4 flex gap-2">
        <button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Search</button>
        <a href="{{ route('crew.index') }}" class="px-4 py-1.5 rounded border">Reset</a>
    </div>
</form>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr><th class="px-4 py-2">Crew ID</th><th class="px-4 py-2">Name</th><th class="px-4 py-2">Rank</th><th class="px-4 py-2">Mobile</th><th class="px-4 py-2">Availability</th><th class="px-4 py-2">Urgency</th><th class="px-4 py-2">Flags</th><th class="px-4 py-2 text-right">Action</th></tr>
        </thead>
        <tbody>
        @forelse ($crew as $c)
            <tr class="border-t hover:bg-slate-50">
                <td class="px-4 py-2 font-mono text-xs">{{ $c->display_id }}</td>
                <td class="px-4 py-2"><a href="{{ route('crew.show', $c) }}" class="text-[#2E74B5] font-medium hover:underline">{{ $c->name }}</a>
                    @if($c->is_draft)<span class="ml-1 px-1.5 py-0.5 rounded text-[10px] bg-amber-100 text-amber-800">DRAFT</span>@endif</td>
                <td class="px-4 py-2">{{ optional($c->currentRank)->rank_name }}</td>
                <td class="px-4 py-2">{{ $c->mobile }}</td>
                <td class="px-4 py-2">
                    @php $ea = $c->effective_availability; @endphp
                    <span class="px-2 py-0.5 rounded text-xs {{ ['available'=>'bg-emerald-100 text-emerald-700','not_available'=>'bg-slate-100 text-slate-500','onboard'=>'bg-blue-100 text-blue-700','resting'=>'bg-amber-100 text-amber-800'][$ea] ?? 'bg-slate-100 text-slate-500' }}">{{ ucfirst(str_replace('_',' ',$ea)) }}</span>
                    @if ($c->availability === 'resting' && !is_null($c->resting_days_left))<div class="text-[11px] text-amber-700 mt-0.5">avail in {{ max($c->resting_days_left,0) }}d</div>@endif
                </td>
                <td class="px-4 py-2">@include('crew.partials.urgency', ['level' => $c->job_urgency, 'deadline' => $c->job_deadline])</td>
                <td class="px-4 py-2 space-x-1">
                    @if (in_array($c->id, $urgentByRank)) <span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-700">Urgent job</span> @endif
                    @if ($c->offences_count) <span class="px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-700" title="Has offence record">⚠ Offence</span> @endif
                </td>
                <td class="px-4 py-2 text-right whitespace-nowrap">
                    <a href="{{ route('crew.show', $c) }}" title="View full profile"
                       class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-xs px-3 py-1.5 hover:bg-slate-100 transition mr-1">👁 View</a>
                    @can('crew.edit')<a href="{{ route('crew.editprofile', $c) }}" title="Edit this crew's profile"
                       class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">✎ Edit Profile</a>@endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">No crew found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $crew->links() }}</div>
@endsection
