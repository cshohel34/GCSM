@extends('layouts.app')
@section('title', 'Document history')
@section('actions')<a href="{{ route('document.index') }}" class="border px-3 py-1.5 rounded text-sm">Back</a>@endsection
@section('content')
<div class="bg-white rounded-lg shadow p-4 mb-4">
    <div class="font-semibold text-[#1F3864]">{{ $document->doc_type }} — {{ optional($document->crewProfile)->name }}</div>
    <div class="text-sm text-slate-500">Number {{ $document->number }} · Issue {{ optional($document->issue_date)->toDateString() }} · Expiry {{ optional($document->expiry_date)->toDateString() }}</div>
</div>
<div class="bg-white rounded-lg shadow p-4">
    <h3 class="font-semibold text-slate-700 mb-2">Change history (validity edits — DM-03)</h3>
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left"><tr><th class="py-1">When</th><th>By</th><th>Event</th><th>Changes</th></tr></thead>
        <tbody>
        @forelse ($audits as $a)
            <tr class="border-t align-top">
                <td class="py-1.5">{{ $a->created_at->toDayDateTimeString() }}</td>
                <td>{{ optional($a->user)->name ?? 'system' }}</td>
                <td>{{ ucfirst($a->event) }}</td>
                <td class="text-xs text-slate-500">
                    @foreach (($a->getModified() ?? []) as $field => $vals)
                        <div><b>{{ $field }}</b>: {{ data_get($vals,'old') }} → {{ data_get($vals,'new') }}</div>
                    @endforeach
                </td>
            </tr>
        @empty <tr><td colspan="4" class="py-3 text-slate-400">No history recorded yet.</td></tr> @endforelse
        </tbody>
    </table>
</div>
@endsection
