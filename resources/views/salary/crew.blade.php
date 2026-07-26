@extends('layouts.app')
@section('title', 'Salary history · '.$crew->name)
@section('actions')<a href="{{ route('crew.show', $crew) }}" class="border px-3 py-1.5 rounded text-sm">Back to profile</a>@endsection
@section('content')
<div class="grid grid-cols-3 gap-4 mb-4">
    <div class="bg-[#1F3864] text-white rounded-lg p-4"><div class="text-2xl font-bold">{{ number_format($totalNet,2) }}</div><div class="text-sm opacity-90">Lifetime net (BDT)</div></div>
    <div class="bg-emerald-600 text-white rounded-lg p-4"><div class="text-2xl font-bold">{{ number_format($paidNet,2) }}</div><div class="text-sm opacity-90">Paid (BDT)</div></div>
    <div class="bg-amber-500 text-white rounded-lg p-4"><div class="text-2xl font-bold">{{ number_format($totalNet-$paidNet,2) }}</div><div class="text-sm opacity-90">Pending (BDT)</div></div>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Month</th><th class="px-4 py-2">Company</th><th class="px-4 py-2">Ship</th><th class="px-4 py-2 text-right">Net USD</th><th class="px-4 py-2 text-right">Net BDT</th><th class="px-4 py-2">State</th></tr></thead>
        <tbody>
        @forelse ($lines as $l)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $l->month }}</td>
                <td class="px-4 py-2">{{ optional(optional($l->sheet)->principal)->name }}</td>
                <td class="px-4 py-2">{{ $l->ship_name }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($l->net_usd,2) }}</td>
                <td class="px-4 py-2 text-right font-medium">{{ number_format($l->net_bdt,2) }}</td>
                <td class="px-4 py-2">
                    @if ($l->is_held)<span class="px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-700">Held</span>
                    @elseif ($l->is_paid)<span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-700">Paid</span>
                    @else<span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-500">Pending</span>@endif
                </td>
            </tr>
        @empty <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No salary records.</td></tr> @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $lines->links() }}</div>
@endsection
