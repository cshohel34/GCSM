@extends('layouts.app')
@section('title', 'Profit & Loss')
@section('actions')<a href="{{ request()->fullUrlWithQuery(['export'=>'pdf']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a><a href="{{ request()->fullUrlWithQuery(['export'=>'excel']) }}" class="border px-3 py-1.5 rounded text-sm">Excel</a>@endsection
@section('content')
@include('accounting._nav')
@include('accounting.reports._range')
<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-emerald-700 mb-2">Income</h3>
        <table class="w-full text-sm">
            @forelse ($income as $r)<tr class="border-t"><td class="py-1.5">{{ $r['account']->name }}</td><td class="text-right">{{ number_format($r['amount'],2) }}</td></tr>
            @empty <tr><td class="py-2 text-slate-400">No income.</td></tr> @endforelse
            <tr class="border-t font-semibold bg-slate-50"><td class="py-1.5">Total income</td><td class="text-right">{{ number_format($totalIncome,2) }}</td></tr>
        </table>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-red-700 mb-2">Expenses</h3>
        <table class="w-full text-sm">
            @forelse ($expense as $r)<tr class="border-t"><td class="py-1.5">{{ $r['account']->name }}</td><td class="text-right">{{ number_format($r['amount'],2) }}</td></tr>
            @empty <tr><td class="py-2 text-slate-400">No expenses.</td></tr> @endforelse
            <tr class="border-t font-semibold bg-slate-50"><td class="py-1.5">Total expense</td><td class="text-right">{{ number_format($totalExpense,2) }}</td></tr>
        </table>
    </div>
</div>
<div class="bg-[#1F3864] text-white rounded-lg p-4 mt-4 flex justify-between">
    <span class="font-semibold">Net {{ ($totalIncome-$totalExpense)>=0 ? 'Profit' : 'Loss' }}</span>
    <span class="text-xl font-bold">{{ number_format($totalIncome-$totalExpense,2) }}</span>
</div>
@endsection
