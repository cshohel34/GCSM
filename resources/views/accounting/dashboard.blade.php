@extends('layouts.app')
@section('title', 'Accounting')
@section('content')
@include('accounting._nav')
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
    <div class="bg-emerald-600 text-white rounded-lg p-4"><div class="text-2xl font-bold">{{ number_format($income,2) }}</div><div class="text-sm opacity-90">Income (this month)</div></div>
    <div class="bg-red-600 text-white rounded-lg p-4"><div class="text-2xl font-bold">{{ number_format($expense,2) }}</div><div class="text-sm opacity-90">Expense (this month)</div></div>
    <div class="bg-[#1F3864] text-white rounded-lg p-4"><div class="text-2xl font-bold">{{ number_format($profit,2) }}</div><div class="text-sm opacity-90">Net profit (this month)</div></div>
</div>
<div class="bg-white rounded-lg shadow p-4">
    <h3 class="font-semibold text-slate-700 mb-2">Cash & bank balances</h3>
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left"><tr><th class="py-1">Account</th><th>Currency</th><th class="text-right">Balance</th></tr></thead>
        <tbody>
        @foreach ($cashBank as $c)
            <tr class="border-t"><td class="py-1.5">{{ $c['account']->name }}</td><td>{{ $c['account']->currency }}</td><td class="text-right font-medium">{{ number_format($c['balance'],2) }}</td></tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4 flex gap-2">
        @can('accounting.create')
        <a href="{{ route('accounting.vouchers.create', ['type'=>'receipt']) }}" class="bg-emerald-600 text-white px-3 py-1.5 rounded text-sm">+ Receipt</a>
        <a href="{{ route('accounting.vouchers.create', ['type'=>'payment']) }}" class="bg-red-600 text-white px-3 py-1.5 rounded text-sm">+ Payment</a>
        <a href="{{ route('accounting.vouchers.create', ['type'=>'journal']) }}" class="bg-[#1F3864] text-white px-3 py-1.5 rounded text-sm">+ Journal</a>
        <a href="{{ route('accounting.vouchers.create', ['type'=>'contra']) }}" class="bg-slate-600 text-white px-3 py-1.5 rounded text-sm">+ Contra</a>
        @endcan
    </div>
</div>

@can('accounting.post')
<div class="bg-white rounded-lg shadow p-4 mt-4">
    <h3 class="font-semibold text-slate-700 mb-2">Period lock</h3>
    <form method="POST" action="{{ route('accounting.close') }}" class="flex items-end gap-2 text-sm">
        @csrf
        <div><label class="block text-xs text-slate-400">Close books up to (no posting on/before this date)</label>
            <input type="date" name="books_closed_upto" value="{{ \App\Models\Setting::get('books_closed_upto') }}" class="border rounded px-2 py-1.5"></div>
        <button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Save lock</button>
    </form>
</div>
@endcan
@endsection