@extends('layouts.app')
@section('title', 'Crew Selection')
@section('actions')
    @can('selection.create')<a href="{{ route('selection.create') }}" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-sm px-3 py-1.5 hover:bg-[#2E74B5] transition">+ New Requirement</a>@endcan
@endsection
@section('content')
<form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
    <select name="principal_id" data-placeholder="Any company" class="border rounded px-2 py-1.5">
        <option value="">Any company</option>
        @foreach ($principals as $p)<option value="{{ $p->id }}" @selected(($filters['principal_id'] ?? '')==$p->id)>{{ $p->name }}</option>@endforeach
    </select>
    <input name="reference" value="{{ $filters['reference'] ?? '' }}" placeholder="Reference" class="border rounded px-2 py-1.5">
    <select name="status" class="border rounded px-2 py-1.5">
        <option value="">Any status</option>
        <option value="open" @selected(($filters['status'] ?? '')==='open')>Open</option>
        <option value="closed" @selected(($filters['status'] ?? '')==='closed')>Closed</option>
    </select>
    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" data-placeholder="Date from" class="border rounded px-2 py-1.5">
    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" data-placeholder="Date to" class="border rounded px-2 py-1.5">

    <select name="vessel_id" data-placeholder="Any vessel" class="border rounded px-2 py-1.5">
        <option value="">Any vessel</option>
        @foreach ($vessels as $companyName => $group)
            <optgroup label="{{ $companyName }}">
                @foreach ($group as $v)<option value="{{ $v->id }}" @selected(($filters['vessel_id'] ?? '')==$v->id)>{{ $v->vessel_name }}{{ $v->imo ? ' — IMO '.$v->imo : '' }}{{ $v->vessel_type ? ' · '.$v->vessel_type : '' }}</option>@endforeach
            </optgroup>
        @endforeach
    </select>
    <select name="rank_id" data-placeholder="Any rank" class="border rounded px-2 py-1.5">@include('crew.partials.rank_options', ['selected' => $filters['rank_id'] ?? ''])</select>
    <select name="country" data-placeholder="Any country" class="border rounded px-2 py-1.5">
        <option value="">Any country</option>
        @foreach (config('countries') as $cname => $dial)
            <option value="{{ $cname }}" @selected(($filters['country'] ?? '')===$cname)>{{ $cname }}</option>
        @endforeach
    </select>
    <select name="contact_id" data-placeholder="Any contact" class="border rounded px-2 py-1.5">
        <option value="">Any contact</option>
        @foreach ($contacts as $companyName => $group)
            <optgroup label="{{ $companyName }}">
                @foreach ($group as $c)<option value="{{ $c->id }}" @selected(($filters['contact_id'] ?? '')==$c->id)>{{ $c->name }}{{ $c->designation ? ' — '.$c->designation : '' }}</option>@endforeach
            </optgroup>
        @endforeach
    </select>
    <select name="staff_id" data-placeholder="Any staff / partner" class="border rounded px-2 py-1.5">
        <option value="">Any staff / partner</option>
        @foreach ($staff as $u)<option value="{{ $u->id }}" @selected(($filters['staff_id'] ?? '')==$u->id)>{{ $u->name }}{{ ($u->user_type ?? null)==='partner' ? ' (Partner)' : ' (Staff)' }}</option>@endforeach
    </select>

    <div class="col-span-2 md:col-span-5 flex gap-2">
        <button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Search</button>
        <a href="{{ route('selection.index') }}" class="px-4 py-1.5 rounded border">Reset</a>
    </div>
</form>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-4 py-2">Reference</th>
                <th class="px-4 py-2">Company</th>
                <th class="px-4 py-2">Contact</th>
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Positions</th>
                <th class="px-4 py-2">Staff / Partner</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2 text-right">Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($requisitions as $r)
            <tr class="border-t hover:bg-slate-50">
                <td class="px-4 py-2"><a href="{{ route('selection.show', $r) }}" class="text-[#2E74B5] font-medium hover:underline">{{ $r->reference ?: 'REQ-'.$r->id }}</a></td>
                <td class="px-4 py-2">{{ optional($r->principal)->name }}</td>
                <td class="px-4 py-2">{{ optional($r->contact)->name ?: '—' }}</td>
                <td class="px-4 py-2">{{ optional($r->requirement_date)->toDateString() }}</td>
                <td class="px-4 py-2">{{ $r->positions_count }}</td>
                <td class="px-4 py-2">
                    @forelse ($r->assignedStaff as $u)
                        @php $isPartner = ($u->user_type ?? null) === 'partner'; @endphp
                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold mr-1 mb-0.5 {{ $isPartner ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-700' }}">{{ $u->name }}</span>
                    @empty
                        <span class="text-slate-300">—</span>
                    @endforelse
                </td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $r->status==='open' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ ucfirst($r->status) }}</span></td>
                <td class="px-4 py-2 text-right whitespace-nowrap">
                    <a href="{{ route('selection.show', $r) }}" title="View requirement details"
                       class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">👁 View Details</a>
                </td>
            </tr>
        @empty <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">No requirements found.</td></tr> @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $requisitions->links() }}</div>
@endsection
