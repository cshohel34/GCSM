@extends('layouts.app')
@section('title', 'Vouchers')
@section('actions')@can('accounting.create')<a href="{{ route('accounting.vouchers.create') }}" class="bg-[#1F3864] text-white px-3 py-1.5 rounded text-sm">+ New Voucher</a>@endcan @endsection
@section('content')
@include('accounting._nav')
<form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
    <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Voucher no / narration" class="border rounded px-2 py-1.5">
    <select name="type" class="border rounded px-2 py-1.5"><option value="">Any type</option>
        @foreach (['receipt'=>'Receipt','payment'=>'Payment','journal'=>'Journal','contra'=>'Contra'] as $k=>$v)<option value="{{ $k }}" @selected(($filters['type'] ?? '')===$k)>{{ $v }}</option>@endforeach</select>
    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="border rounded px-2 py-1.5">
    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="border rounded px-2 py-1.5">
    <div class="flex gap-2"><button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Search</button><a href="{{ route('accounting.vouchers.index') }}" class="px-4 py-1.5 rounded border">Reset</a></div>
</form>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Voucher</th><th class="px-4 py-2">Type</th><th class="px-4 py-2">Date</th><th class="px-4 py-2">Narration</th><th class="px-4 py-2 text-right">Amount</th><th class="px-4 py-2">Status</th></tr></thead>
        <tbody>
        @forelse ($vouchers as $v)
            <tr class="border-t">
                <td class="px-4 py-2"><a href="{{ route('accounting.vouchers.show', $v) }}" class="text-[#2E74B5] font-mono hover:underline">{{ $v->voucher_no }}</a></td>
                <td class="px-4 py-2 capitalize">{{ $v->voucher_type }}</td>
                <td class="px-4 py-2">{{ $v->date->toDateString() }}</td>
                <td class="px-4 py-2 text-slate-500">{{ \Illuminate\Support\Str::limit($v->narration, 40) }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($v->totalDebit(),2) }}</td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $v->status==='posted' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($v->status) }}</span></td>
            </tr>
        @empty <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No vouchers.</td></tr> @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $vouchers->links() }}</div>
@endsection
