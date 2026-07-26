@extends('layouts.app')
@section('title', 'Voucher '.$voucher->voucher_no)
@section('actions')
    <a href="{{ route('accounting.vouchers.index') }}" class="border px-3 py-1.5 rounded text-sm">Back</a>
    @can('accounting.post')@if($voucher->status==='posted')<form method="POST" action="{{ route('accounting.vouchers.void', $voucher) }}" class="inline" onsubmit="return confirm('Void this voucher?')">@csrf<button class="border px-3 py-1.5 rounded text-sm text-red-600 ml-1">Void</button></form>@endif @endcan
@endsection
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-3xl">
    <div class="flex justify-between mb-4">
        <div>
            <div class="text-lg font-semibold text-[#1F3864]">{{ ucfirst($voucher->voucher_type) }} Voucher — {{ $voucher->voucher_no }}</div>
            <div class="text-sm text-slate-500">{{ $voucher->date->toDateString() }} · Ref {{ $voucher->reference ?: '—' }}
                <span class="px-2 py-0.5 rounded text-xs {{ $voucher->status==='posted' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($voucher->status) }}</span></div>
        </div>
    </div>
    <table class="w-full text-sm mb-3">
        <thead class="text-slate-400 text-left border-b"><tr><th class="py-1">Account</th><th>Party</th><th class="text-right">Debit</th><th class="text-right">Credit</th></tr></thead>
        <tbody>
        @foreach ($voucher->lines as $l)
            <tr class="border-b"><td class="py-1.5">{{ $l->account->code }} — {{ $l->account->name }}<br><span class="text-xs text-slate-400">{{ $l->memo }}</span></td>
                <td>{{ $l->partyName() }}</td>
                <td class="text-right">{{ $l->debit>0 ? number_format($l->debit,2) : '' }}</td>
                <td class="text-right">{{ $l->credit>0 ? number_format($l->credit,2) : '' }}</td></tr>
        @endforeach
        </tbody>
        <tfoot class="font-semibold"><tr><td colspan="2" class="text-right py-1">Total</td><td class="text-right">{{ number_format($voucher->totalDebit(),2) }}</td><td class="text-right">{{ number_format($voucher->totalCredit(),2) }}</td></tr></tfoot>
    </table>
    @if ($voucher->narration)<div class="text-sm text-slate-600">Narration: {{ $voucher->narration }}</div>@endif
    <div class="text-xs text-slate-400 mt-2">Entered by {{ optional($voucher->createdBy)->name }}</div>
</div>
@endsection
