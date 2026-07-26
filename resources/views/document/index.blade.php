@extends('layouts.app')
@section('title', 'Document Management')
@section('actions')<a href="{{ route('document.signon.register') }}" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-sm px-3 py-1.5 hover:bg-[#2E74B5] transition mr-1">📄 Sign On Letter Register</a><a href="{{ route('document.business.index') }}" class="border px-3 py-1.5 rounded text-sm mr-1">Business Docs</a><a href="{{ route('document.export', array_merge(request()->query(), ['export'=>'pdf'])) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a><a href="{{ route('document.export', array_merge(request()->query(), ['export'=>'excel'])) }}" class="border px-3 py-1.5 rounded text-sm">Excel</a>@endsection
@section('content')
<div class="grid grid-cols-3 gap-4 mb-4">
    <div class="bg-emerald-600 text-white rounded-lg p-4"><div class="text-2xl font-bold">{{ $counts['valid'] }}</div><div class="text-sm opacity-90">Valid</div></div>
    <div class="bg-amber-500 text-white rounded-lg p-4"><div class="text-2xl font-bold">{{ $counts['expiring'] }}</div><div class="text-sm opacity-90">Expiring (≤30d)</div></div>
    <div class="bg-red-600 text-white rounded-lg p-4"><div class="text-2xl font-bold">{{ $counts['expired'] }}</div><div class="text-sm opacity-90">Expired</div></div>
</div>
<form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
    <input name="crew" value="{{ $filters['crew'] ?? '' }}" placeholder="Crew name" class="border rounded px-2 py-1.5">
    <input name="doc_type" value="{{ $filters['doc_type'] ?? '' }}" placeholder="Document type" class="border rounded px-2 py-1.5">
    <select name="status" class="border rounded px-2 py-1.5"><option value="">Any status</option>
        @foreach (['valid'=>'Valid','expiring'=>'Expiring','expired'=>'Expired'] as $k=>$v)<option value="{{ $k }}" @selected(($filters['status'] ?? '')===$k)>{{ $v }}</option>@endforeach
    </select>
    <select name="within" class="border rounded px-2 py-1.5"><option value="0">Any expiry window</option>
        @foreach ([30=>'≤ 30 days',90=>'≤ 90 days',180=>'≤ 180 days'] as $k=>$v)<option value="{{ $k }}" @selected((int)($filters['within'] ?? 0)===$k)>{{ $v }}</option>@endforeach
    </select>
    <div class="col-span-2 md:col-span-4 flex gap-2"><button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Filter</button><a href="{{ route('document.index') }}" class="px-4 py-1.5 rounded border">Reset</a></div>
</form>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Crew</th><th class="px-4 py-2">Document</th><th class="px-4 py-2">Number</th><th class="px-4 py-2">Issue</th><th class="px-4 py-2">Expiry</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr></thead>
        <tbody>
        @forelse ($docs as $d)
            <tr class="border-t">
                <td class="px-4 py-2"><a href="{{ route('crew.show', $d->crew_profile_id) }}" class="text-[#2E74B5] hover:underline">{{ optional($d->crewProfile)->name }}</a></td>
                <td class="px-4 py-2">{{ $d->doc_type }}</td><td class="px-4 py-2">{{ $d->number }}</td>
                <td class="px-4 py-2">{{ optional($d->issue_date)->toDateString() }}</td><td class="px-4 py-2">{{ optional($d->expiry_date)->toDateString() }}</td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs {{ ['valid'=>'bg-emerald-100 text-emerald-700','expiring'=>'bg-amber-100 text-amber-700','expired'=>'bg-red-100 text-red-700','na'=>'bg-slate-100 text-slate-500'][$d->status] }}">{{ ucfirst($d->status) }}</span></td>
                <td class="px-4 py-2 text-right"><a href="{{ route('document.history', $d) }}" class="text-[#2E74B5] text-xs">history</a></td>
            </tr>
        @empty <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">No documents match.</td></tr> @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $docs->links() }}</div>
@endsection
