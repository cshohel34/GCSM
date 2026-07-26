@extends('layouts.app')
@section('title', 'Balance Sheet')
@section('actions')<a href="{{ request()->fullUrlWithQuery(['export'=>'pdf']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a><a href="{{ request()->fullUrlWithQuery(['export'=>'excel']) }}" class="border px-3 py-1.5 rounded text-sm">Excel</a>@endsection
@section('content')
@include('accounting._nav')
<form method="GET" class="bg-white rounded-lg shadow p-3 mb-4 flex gap-3 items-end text-sm">
    <div><label class="block text-xs text-slate-400">As of</label><input type="date" name="to" value="{{ $to }}" class="border rounded px-2 py-1.5"></div>
    <button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Apply</button>
</form>
<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-slate-700 mb-2">Assets</h3>
        <table class="w-full text-sm">
            @foreach ($assets as $r)<tr class="border-t"><td class="py-1.5">{{ $r['account']->name }}</td><td class="text-right">{{ number_format($r['amount'],2) }}</td></tr>@endforeach
            <tr class="border-t font-semibold bg-slate-50"><td class="py-1.5">Total assets</td><td class="text-right">{{ number_format($totalAssets,2) }}</td></tr>
        </table>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-slate-700 mb-2">Liabilities & Equity</h3>
        <table class="w-full text-sm">
            @foreach ($liabilities as $r)<tr class="border-t"><td class="py-1.5">{{ $r['account']->name }}</td><td class="text-right">{{ number_format($r['amount'],2) }}</td></tr>@endforeach
            <tr class="border-t font-semibold"><td class="py-1.5">Total liabilities</td><td class="text-right">{{ number_format($totalLiab,2) }}</td></tr>
            @foreach ($equity as $r)<tr class="border-t"><td class="py-1.5">{{ $r['account']->name }}</td><td class="text-right">{{ number_format($r['amount'],2) }}</td></tr>@endforeach
            <tr class="border-t"><td class="py-1.5">Current period profit</td><td class="text-right">{{ number_format($currentProfit,2) }}</td></tr>
            <tr class="border-t font-semibold"><td class="py-1.5">Total equity</td><td class="text-right">{{ number_format($totalEquity,2) }}</td></tr>
            <tr class="border-t font-bold bg-slate-50"><td class="py-1.5">Total liabilities & equity</td><td class="text-right">{{ number_format($totalLiab+$totalEquity,2) }}</td></tr>
        </table>
    </div>
</div>
@php $diff=$totalAssets-($totalLiab+$totalEquity); @endphp
@if(abs($diff)>0.01)<div class="text-amber-600 text-sm mt-2">Note: assets − (liabilities + equity) = {{ number_format($diff,2) }}. Any difference usually means opening balances need entering as an opening journal.</div>@endif
@endsection
