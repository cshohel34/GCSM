@extends('layouts.app')
@section('title', 'Sign On Letter Register')
@section('actions')
    <a href="{{ route('document.index') }}" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition">← Documents</a>
@endsection
@section('content')

<form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
    <input name="crew" value="{{ $filters['crew'] ?? '' }}" placeholder="Crew name" class="border rounded px-2 py-1.5">
    <input name="cdc_no" value="{{ $filters['cdc_no'] ?? '' }}" placeholder="CDC No" class="border rounded px-2 py-1.5">
    <input name="passport_no" value="{{ $filters['passport_no'] ?? '' }}" placeholder="Passport No" class="border rounded px-2 py-1.5">
    <input name="mobile" value="{{ $filters['mobile'] ?? '' }}" placeholder="Mobile" class="border rounded px-2 py-1.5">
    <input name="vessel" value="{{ $filters['vessel'] ?? '' }}" placeholder="Vessel name" class="border rounded px-2 py-1.5">
    <input name="company" value="{{ $filters['company'] ?? '' }}" placeholder="Company name" class="border rounded px-2 py-1.5">
    <input name="rank" value="{{ $filters['rank'] ?? '' }}" placeholder="Rank" class="border rounded px-2 py-1.5">
    <input name="reference" value="{{ $filters['reference'] ?? '' }}" placeholder="Reference No" class="border rounded px-2 py-1.5">
    <div>
        <label class="block text-[10px] uppercase tracking-wide text-slate-400 mb-0.5">Issued from</label>
        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="border rounded px-2 py-1.5 w-full">
    </div>
    <div>
        <label class="block text-[10px] uppercase tracking-wide text-slate-400 mb-0.5">Issued to</label>
        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="border rounded px-2 py-1.5 w-full">
    </div>
    <div>
        <label class="block text-[10px] uppercase tracking-wide text-slate-400 mb-0.5">Joining date</label>
        <input type="date" name="joining_date" value="{{ $filters['joining_date'] ?? '' }}" class="border rounded px-2 py-1.5 w-full">
    </div>
    <div class="flex items-end gap-2">
        <button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Search</button>
        <a href="{{ route('document.signon.register') }}" class="px-4 py-1.5 rounded border">Reset</a>
    </div>
</form>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-3 py-2">Reference No</th>
                    <th class="px-3 py-2">Issued</th>
                    <th class="px-3 py-2">Crew</th>
                    <th class="px-3 py-2">CDC</th>
                    <th class="px-3 py-2">Passport</th>
                    <th class="px-3 py-2">Mobile</th>
                    <th class="px-3 py-2">Rank</th>
                    <th class="px-3 py-2">Vessel</th>
                    <th class="px-3 py-2">Company</th>
                    <th class="px-3 py-2">Joining</th>
                    <th class="px-3 py-2">Issued by</th>
                    <th class="px-3 py-2 text-right">Letter</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($letters as $l)
                <tr class="border-t hover:bg-slate-50">
                    <td class="px-3 py-2 font-mono text-xs text-navy-800">{{ $l->reference_no }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ optional($l->letter_date)->toDateString() }}</td>
                    <td class="px-3 py-2">
                        @if ($l->crew_profile_id)<a href="{{ route('crew.show', $l->crew_profile_id) }}" class="text-[#2E74B5] font-medium hover:underline">{{ $l->crew_name }}</a>@else {{ $l->crew_name }} @endif
                    </td>
                    <td class="px-3 py-2">{{ $l->cdc_no }}</td>
                    <td class="px-3 py-2">{{ $l->passport_no }}</td>
                    <td class="px-3 py-2">{{ $l->mobile }}</td>
                    <td class="px-3 py-2">{{ $l->rank }}</td>
                    <td class="px-3 py-2">{{ $l->vessel_name }}</td>
                    <td class="px-3 py-2">{{ $l->company_name }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ optional($l->joining_date)->toDateString() }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <div class="text-navy-800">{{ optional($l->issuedBy)->name ?: 'System' }}</div>
                        <div class="text-[11px] text-slate-400">{{ optional($l->created_at)->format('d M Y, h:i A') }}</div>
                    </td>
                    <td class="px-3 py-2 text-right">
                        @if ($l->candidate_id)
                            <a href="{{ route('selection.signon.letter', $l->candidate_id) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-md border border-[#1F3864]/40 bg-white text-[#1F3864] font-semibold text-xs px-3 py-1.5 hover:bg-[#1F3864] hover:text-white transition">⬇ PDF</a>
                        @else
                            <span class="text-slate-300 text-xs">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="12" class="px-4 py-10 text-center text-slate-400">No sign-on letters issued yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $letters->links() }}</div>
@endsection
