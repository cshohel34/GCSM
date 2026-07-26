@extends('layouts.app')
@section('title', 'Licence history')
@section('actions')<a href="{{ route('license.index') }}" class="border px-3 py-1.5 rounded text-sm">Back</a>@endsection
@section('content')
<div class="bg-white rounded-lg shadow p-4 mb-4">
    <div class="font-semibold text-[#1F3864]">{{ $license->name }}</div>
    <div class="text-sm text-slate-500">No {{ $license->license_no }} · Expiry {{ optional($license->expiry_date)->toDateString() }} · {{ ucfirst($license->status) }}</div>
</div>
<div class="bg-white rounded-lg shadow p-4">
    <h3 class="font-semibold text-slate-700 mb-2">Renewal / change history</h3>
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left"><tr><th class="py-1">When</th><th>By</th><th>Event</th><th>Changes</th></tr></thead>
        <tbody>
        @forelse ($audits as $a)
            <tr class="border-t align-top"><td class="py-1.5">{{ $a->created_at->toDayDateTimeString() }}</td><td>{{ optional($a->user)->name ?? 'system' }}</td><td>{{ ucfirst($a->event) }}</td>
                <td class="text-xs text-slate-500">@foreach (($a->getModified() ?? []) as $f => $v)<div><b>{{ $f }}</b>: {{ data_get($v,'old') }} → {{ data_get($v,'new') }}</div>@endforeach</td></tr>
        @empty <tr><td colspan="4" class="py-3 text-slate-400">No history.</td></tr> @endforelse
        </tbody>
    </table>
</div>
@endsection
