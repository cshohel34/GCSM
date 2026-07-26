@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    @foreach ([
        ['Total Crew', $stats['crew_total'], 'bg-[#1F3864]'],
        ['Available', $stats['crew_available'], 'bg-emerald-600'],
        ['Docs Expiring', $stats['docs_expiring'], 'bg-amber-500'],
        ['Docs Expired', $stats['docs_expired'], 'bg-red-600'],
    ] as [$label, $value, $bg])
    <div class="rounded-lg text-white p-4 {{ $bg }}">
        <div class="text-3xl font-bold">{{ $value }}</div>
        <div class="text-sm opacity-90">{{ $label }}</div>
    </div>
    @endforeach
</div>
<div class="bg-white rounded-lg shadow">
    <div class="px-4 py-3 border-b font-semibold text-slate-700">Documents / certificates needing attention</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr><th class="px-4 py-2">Crew</th><th class="px-4 py-2">Document</th><th class="px-4 py-2">Expiry</th><th class="px-4 py-2">Status</th></tr>
        </thead>
        <tbody>
        @forelse ($expiring as $d)
            <tr class="border-t">
                <td class="px-4 py-2"><a href="{{ route('crew.show', $d->crew_profile_id) }}" class="text-[#2E74B5] hover:underline">{{ optional($d->crewProfile)->name }}</a></td>
                <td class="px-4 py-2">{{ $d->doc_type }}</td>
                <td class="px-4 py-2">{{ optional($d->expiry_date)->toDateString() }}</td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $d->status === 'expired' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">{{ ucfirst($d->status) }}</span></td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Nothing expiring soon.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
