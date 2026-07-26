@extends('layouts.app')
@section('title', 'Party Ledger')
@section('actions')<a href="{{ request()->fullUrlWithQuery(['export'=>'pdf']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a><a href="{{ request()->fullUrlWithQuery(['export'=>'excel']) }}" class="border px-3 py-1.5 rounded text-sm">Excel</a>@endsection
@section('content')
@include('accounting._nav')
<form method="GET" class="bg-white rounded-lg shadow p-3 mb-4 flex flex-wrap gap-3 items-end text-sm">
    <div><label class="block text-xs text-slate-400">Party type</label>
        <select name="party_type" class="border rounded px-2 py-1.5">
            <option value="principal" @selected($party_type==='principal')>Principal</option>
            <option value="partner" @selected($party_type==='partner')>Partner</option>
            <option value="crew" @selected($party_type==='crew')>Crew</option>
            <option value="staff" @selected($party_type==='staff')>Staff</option>
        </select></div>
    <div><label class="block text-xs text-slate-400">Party ID</label><input name="party_id" value="{{ $party_id }}" placeholder="numeric id" class="border rounded px-2 py-1.5 w-28"></div>
    <div><label class="block text-xs text-slate-400">From</label><input type="date" name="from" value="{{ $from }}" class="border rounded px-2 py-1.5"></div>
    <div><label class="block text-xs text-slate-400">To</label><input type="date" name="to" value="{{ $to }}" class="border rounded px-2 py-1.5"></div>
    <button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">View</button>
</form>
<p class="text-xs text-slate-400 mb-3">Tip: principal IDs from Principals module, partner/staff IDs from Staff module, crew IDs from Crew module. Positive balance = they owe GCSM (receivable); negative = GCSM owes them (payable).</p>
@if ($data)
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Date</th><th class="px-4 py-2">Voucher</th><th class="px-4 py-2">Account</th><th class="px-4 py-2 text-right">Debit</th><th class="px-4 py-2 text-right">Credit</th><th class="px-4 py-2 text-right">Balance</th></tr></thead>
        <tbody>
        @forelse ($data['rows'] as $row)
            <tr class="border-t"><td class="px-4 py-1.5">{{ $row['line']->tdate }}</td><td class="px-4 py-1.5 font-mono text-xs">{{ $row['line']->voucher_no }}</td>
                <td class="px-4 py-1.5">{{ $row['line']->account_name }}</td>
                <td class="px-4 py-1.5 text-right">{{ $row['line']->debit>0?number_format($row['line']->debit,2):'' }}</td>
                <td class="px-4 py-1.5 text-right">{{ $row['line']->credit>0?number_format($row['line']->credit,2):'' }}</td>
                <td class="px-4 py-1.5 text-right">{{ number_format($row['running'],2) }}</td></tr>
        @empty <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No entries.</td></tr> @endforelse
        </tbody>
        <tfoot class="bg-slate-100 font-semibold"><tr><td colspan="5" class="px-4 py-2 text-right">Closing balance</td><td class="px-4 py-2 text-right">{{ number_format($data['closing'],2) }}</td></tr></tfoot>
    </table>
</div>
@endif
@endsection
