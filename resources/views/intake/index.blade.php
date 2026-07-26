@extends('layouts.app')
@section('title', 'CV Intake & Approvals')
@section('content')

<div class="card mb-4">
    <div class="px-4 py-3 border-b font-semibold text-slate-700">Website CV submissions (goldencareerbd.com/career) — pending review</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Name</th><th class="px-4 py-2">Rank</th><th class="px-4 py-2">Phone</th><th class="px-4 py-2">Source</th><th class="px-4 py-2">Duplicate check</th><th class="px-4 py-2"></th></tr></thead>
        <tbody>
        @forelse ($submissions as $s)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $s->name }}</td><td class="px-4 py-2">{{ $s->rank_text }}</td><td class="px-4 py-2">{{ $s->mobile }}</td>
                <td class="px-4 py-2 capitalize">{{ $s->source }}</td>
                <td class="px-4 py-2">@if($s->dupes->isNotEmpty())<span class="badge g-red" style="background:#fee2e2;color:#b91c1c;padding:2px 8px;border-radius:999px;font-size:11px">⚠ {{ $s->dupes->count() }} match</span>@else<span style="background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:999px;font-size:11px">Clear</span>@endif</td>
                <td class="px-4 py-2 text-right"><a href="{{ route('intake.review', $s) }}" class="text-[#2E74B5] hover:underline">Review</a></td>
            </tr>
        @empty <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">No pending CV submissions.</td></tr> @endforelse
        </tbody>
    </table>
    @can('crew.create')
    <form method="POST" action="{{ route('intake.store') }}" enctype="multipart/form-data" class="grid grid-cols-2 md:grid-cols-6 gap-2 p-4 border-t text-sm items-end">
        @csrf
        <input name="name" placeholder="Name *" required class="border rounded px-2 py-1">
        <input name="rank_text" placeholder="Rank" class="border rounded px-2 py-1">
        <input name="mobile" placeholder="Mobile" class="border rounded px-2 py-1">
        <input name="cdc_no" placeholder="CDC" class="border rounded px-2 py-1">
        <input name="passport_no" placeholder="Passport" class="border rounded px-2 py-1">
        <button class="bg-[#1F3864] text-white rounded px-3 py-1">Record walk-in CV</button>
    </form>
    @endcan
</div>

<div class="card">
    <div class="px-4 py-3 border-b font-semibold text-slate-700">Pending edits — awaiting Super Admin / Manager approval</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Record</th><th class="px-4 py-2">Requested by</th><th class="px-4 py-2">Changes</th><th class="px-4 py-2">When</th><th class="px-4 py-2"></th></tr></thead>
        <tbody>
        @forelse ($changes as $c)
            <tr class="border-t align-top">
                <td class="px-4 py-2">{{ $c->label }}</td>
                <td class="px-4 py-2">{{ optional($c->requestedBy)->name }}</td>
                <td class="px-4 py-2 text-xs text-slate-600">
                    @foreach ($c->changes as $field => $pair)<div><b>{{ $field }}</b>: {{ data_get($pair,'old') }} → {{ data_get($pair,'new') }}</div>@endforeach
                    @if($c->reason)<div class="text-slate-400">Reason: {{ $c->reason }}</div>@endif
                </td>
                <td class="px-4 py-2">{{ $c->created_at->diffForHumans() }}</td>
                <td class="px-4 py-2 text-right">
                    <form method="POST" action="{{ route('intake.changes.approve', $c) }}" class="inline">@csrf<button class="bg-emerald-600 text-white rounded px-3 py-1 text-xs">Approve</button></form>
                    <form method="POST" action="{{ route('intake.changes.reject', $c) }}" class="inline">@csrf<button class="border rounded px-3 py-1 text-xs">Reject</button></form>
                </td>
            </tr>
        @empty <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No pending edits.</td></tr> @endforelse
        </tbody>
    </table>
</div>
@endsection
