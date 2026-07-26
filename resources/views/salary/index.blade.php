@extends('layouts.app')
@section('title', 'Salary Management')
@section('actions')
    @can('salary.create')<a href="{{ route('salary.create') }}" class="bg-[#1F3864] text-white px-3 py-1.5 rounded text-sm hover:bg-[#2E74B5]">+ Generate Sheet</a>@endcan
@endsection
@section('content')
<form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
    <select name="principal_id" class="border rounded px-2 py-1.5">
        <option value="">Any company</option>
        @foreach ($principals as $p)<option value="{{ $p->id }}" @selected(($filters['principal_id'] ?? '')==$p->id)>{{ $p->name }}</option>@endforeach
    </select>
    <input name="month" value="{{ $filters['month'] ?? '' }}" placeholder="Month e.g. FEB-26" class="border rounded px-2 py-1.5">
    <select name="status" class="border rounded px-2 py-1.5">
        <option value="">Any status</option>
        @foreach (['draft'=>'Draft','reconciled'=>'Reconciled','locked'=>'Locked'] as $k=>$v)
            <option value="{{ $k }}" @selected(($filters['status'] ?? '')===$k)>{{ $v }}</option>
        @endforeach
    </select>
    <div class="flex gap-2"><button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Search</button><a href="{{ route('salary.index') }}" class="px-4 py-1.5 rounded border">Reset</a></div>
</form>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Month</th><th class="px-4 py-2">Company</th><th class="px-4 py-2">Vessel</th><th class="px-4 py-2">Crew</th><th class="px-4 py-2">USD Rate</th><th class="px-4 py-2">Status</th></tr></thead>
        <tbody>
        @forelse ($sheets as $sh)
            <tr class="border-t hover:bg-slate-50">
                <td class="px-4 py-2"><a href="{{ route('salary.show', $sh) }}" class="text-[#2E74B5] font-medium hover:underline">{{ $sh->month }}</a></td>
                <td class="px-4 py-2">{{ optional($sh->principal)->name }}</td>
                <td class="px-4 py-2">{{ optional($sh->vessel)->vessel_name ?: 'All' }}</td>
                <td class="px-4 py-2">{{ $sh->lines_count }}</td>
                <td class="px-4 py-2">{{ $sh->usd_rate }}</td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs {{ ['draft'=>'bg-slate-100 text-slate-600','reconciled'=>'bg-amber-100 text-amber-700','locked'=>'bg-emerald-100 text-emerald-700'][$sh->status] }}">{{ ucfirst($sh->status) }}</span></td>
            </tr>
        @empty <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No salary sheets.</td></tr> @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $sheets->links() }}</div>
@endsection
