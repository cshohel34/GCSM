@extends('layouts.app')
@section('title', $staff->name)
@section('actions')@can('staff.edit')<a href="{{ route('staff.edit', $staff) }}" class="bg-[#1F3864] text-white px-3 py-1.5 rounded text-sm">Edit</a>@endcan @endsection
@section('content')
<div class="grid md:grid-cols-3 gap-4 mb-4">
    <div class="bg-white rounded-lg shadow p-4 md:col-span-2">
        <div class="text-xl font-semibold text-[#1F3864]">{{ $staff->name }}
            <span class="text-sm px-2 py-0.5 rounded {{ $staff->status==='active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($staff->status) }}</span>
        </div>
        <div class="text-sm text-slate-500 capitalize">{{ $staff->user_type }} · {{ $staff->getRoleNames()->implode(', ') }} · {{ $staff->office }}</div>
        <div class="text-sm text-slate-500 mt-1">{{ $staff->email }} · {{ $staff->phone }}</div>
        @if ($staff->isPartner())
            <div class="text-sm mt-3 text-slate-600">Fee-share — service charge {{ $staff->share_service_charge_pct ?? 0 }}% · agency fee {{ $staff->share_agency_fee_pct ?? 0 }}% · net profit {{ $staff->share_net_profit_pct ?? 0 }}%
                @if($staff->share_notes)<br><span class="text-xs text-slate-400">{{ $staff->share_notes }}</span>@endif</div>
        @endif
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-sm">
        <div class="text-slate-400">Productivity</div>
        <div class="mt-1">Crew profiles created: <b>{{ $stats['crew_created'] }}</b></div>
        <div>Placements arranged: <b>{{ $stats['placements'] }}</b></div>
        <div>Currently onboard: <b>{{ $stats['onboard'] }}</b></div>
    </div>
</div>

@if ($staff->isPartner())
<div class="bg-white rounded-lg shadow p-4">
    <h3 class="font-semibold text-slate-700 mb-2">Partner payouts</h3>
    <table class="w-full text-sm mb-3">
        <thead class="text-slate-400 text-left"><tr><th class="py-1">Basis</th><th>Base</th><th>%</th><th>Amount</th><th>Placement</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($payouts as $po)
            <tr class="border-t">
                <td class="py-1.5 capitalize">{{ str_replace('_',' ',$po->basis) }}</td>
                <td>{{ $po->base_amount ? number_format($po->base_amount,2) : '—' }}</td>
                <td>{{ $po->percent ? $po->percent.'%' : '—' }}</td>
                <td class="font-medium">{{ number_format($po->amount,2) }}</td>
                <td>{{ optional(optional($po->placement)->principal)->name }}</td>
                <td>@if($po->status==='paid')<span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded">Paid</span>@else<span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">Pending</span>@endif</td>
                <td class="text-right">@can('staff.edit')
                    @if($po->status!=='paid')<form method="POST" action="{{ route('staff.payouts.paid', [$staff,$po]) }}" class="inline">@csrf<button class="text-xs text-emerald-600">mark paid</button></form>@endif
                    <form method="POST" action="{{ route('staff.payouts.destroy', [$staff,$po]) }}" class="inline" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button class="text-xs text-red-400 ml-1">×</button></form>
                @endcan</td>
            </tr>
        @empty <tr><td colspan="7" class="py-3 text-slate-400">No payouts recorded.</td></tr> @endforelse
        </tbody>
    </table>
    @can('staff.edit')
    <form method="POST" action="{{ route('staff.payouts.store', $staff) }}" class="grid grid-cols-2 md:grid-cols-6 gap-2 text-sm items-end border-t pt-3">
        @csrf
        <select name="basis" class="border rounded px-2 py-1">
            <option value="service_charge">Service charge</option><option value="agency_fee">Agency fee</option>
            <option value="net_profit">Net profit</option><option value="negotiated">Negotiated</option></select>
        <input name="base_amount" placeholder="Base amount" class="border rounded px-2 py-1">
        <input name="percent" placeholder="%" class="border rounded px-2 py-1">
        <input name="amount" placeholder="Amount (or auto)" class="border rounded px-2 py-1">
        <input name="placement_id" placeholder="Placement ID (opt)" class="border rounded px-2 py-1">
        <button class="bg-[#1F3864] text-white rounded px-3 py-1">Record payout</button>
    </form>
    <p class="text-xs text-slate-400 mt-2">Leave "Amount" blank to auto-compute base × %. Partners earn only on placements they arranged (TM-04).</p>
    @endcan
</div>
@endif
@endsection
