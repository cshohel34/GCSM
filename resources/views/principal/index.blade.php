@extends('layouts.app')
@section('title', 'Principal Management')
@section('actions')
    <a href="{{ route('principal.directory') }}" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition mr-1">Directory PDF</a>
    @can('principal.create')<a href="{{ route('principal.create') }}" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-sm px-3 py-1.5 hover:bg-[#2E74B5] transition">+ New Company</a>@endcan
@endsection
@section('content')
<form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
    <input name="name" value="{{ $filters['name'] ?? '' }}" placeholder="Company name" class="border rounded px-2 py-1.5">
    <select name="type" class="border rounded px-2 py-1.5">
        <option value="">Any type</option>
        <option value="principal" @selected(($filters['type'] ?? '')==='principal')>Principal</option>
        <option value="management" @selected(($filters['type'] ?? '')==='management')>Management</option>
    </select>
    <input name="country" value="{{ $filters['country'] ?? '' }}" placeholder="Country" class="border rounded px-2 py-1.5">
    <select name="status" class="border rounded px-2 py-1.5">
        <option value="">Any status</option>
        <option value="active" @selected(($filters['status'] ?? '')==='active')>Active</option>
        <option value="inactive" @selected(($filters['status'] ?? '')==='inactive')>Inactive</option>
    </select>
    <select name="staff_id" data-placeholder="Any staff / partner" class="border rounded px-2 py-1.5">
        <option value="">Any staff / partner</option>
        @foreach ($staff as $u)<option value="{{ $u->id }}" @selected(($filters['staff_id'] ?? '')==$u->id)>{{ $u->name }}</option>@endforeach
    </select>
    <div class="col-span-2 md:col-span-5 flex gap-2">
        <button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Search</button>
        <a href="{{ route('principal.index') }}" class="px-4 py-1.5 rounded border">Reset</a>
    </div>
</form>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-4 py-2">Company</th>
                <th class="px-4 py-2">Type</th>
                <th class="px-4 py-2">Country</th>
                <th class="px-4 py-2">Managing Staff / Partner</th>
                <th class="px-4 py-2">Onboard</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Flags</th>
                <th class="px-4 py-2 text-right">Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($principals as $p)
            <tr class="border-t hover:bg-slate-50">
                <td class="px-4 py-2">
                    <div class="flex items-center gap-2.5">
                        @if ($p->logo_path)
                            <img src="{{ asset('storage/'.$p->logo_path) }}" alt="logo" class="w-9 h-9 rounded-lg object-contain ring-1 ring-slate-200 bg-white shrink-0">
                        @else
                            <span class="w-9 h-9 rounded-lg bg-[#1F3864] text-white flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(mb_substr($p->name,0,2)) }}</span>
                        @endif
                        <a href="{{ route('principal.show', $p) }}" class="text-[#2E74B5] font-medium hover:underline">{{ $p->name }}</a>
                    </div>
                </td>
                <td class="px-4 py-2 capitalize">{{ $p->type }}</td>
                <td class="px-4 py-2">{{ $p->country }}</td>
                <td class="px-4 py-2">
                    @if ($p->assignedStaff)
                        @php $isPartner = ($p->assignedStaff->user_type ?? null) === 'partner'; @endphp
                        <span class="inline-flex items-center gap-1.5">
                            <span class="text-navy-800">{{ $p->assignedStaff->name }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $isPartner ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-700' }}">{{ $isPartner ? 'Partner' : 'Staff' }}</span>
                        </span>
                    @else — @endif
                </td>
                <td class="px-4 py-2">{{ $p->onboard_count }}</td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $p->status==='active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ ucfirst($p->status) }}</span></td>
                <td class="px-4 py-2">@if ($p->offences_count)<span class="px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-700" title="Has {{ $p->offences_count }} offence record(s)">⚠ Offence</span>@endif</td>
                <td class="px-4 py-2 text-right whitespace-nowrap">
                    <a href="{{ route('principal.show', $p) }}" title="View profile" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-xs px-3 py-1.5 hover:bg-slate-100 transition mr-1">👁 View Profile</a>
                    @can('principal.edit')<a href="{{ route('principal.editprofile', $p) }}" title="Edit profile" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">✎ Edit Profile</a>@endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">No companies found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $principals->links() }}</div>
@endsection
