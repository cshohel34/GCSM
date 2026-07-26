@extends('layouts.app')
@section('title', 'Trial Balance')
@section('actions')<a href="{{ request()->fullUrlWithQuery(['export'=>'pdf']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a><a href="{{ request()->fullUrlWithQuery(['export'=>'excel']) }}" class="border px-3 py-1.5 rounded text-sm">Excel</a>@endsection
@section('content')
@include('accounting._nav')
@include('accounting.reports._range')
@php $td=collect($rows)->sum('debit'); $tc=collect($rows)->sum('credit'); @endphp
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Code</th><th class="px-4 py-2">Account</th><th class="px-4 py-2 text-right">Debit</th><th class="px-4 py-2 text-right">Credit</th></tr></thead>
        <tbody>
        @forelse ($rows as $r)
            <tr class="border-t"><td class="px-4 py-1.5 font-mono text-xs">{{ $r['account']->code }}</td><td class="px-4 py-1.5">{{ $r['account']->name }}</td>
                <td class="px-4 py-1.5 text-right">{{ $r['debit'] ? number_format($r['debit'],2) : '' }}</td>
                <td class="px-4 py-1.5 text-right">{{ $r['credit'] ? number_format($r['credit'],2) : '' }}</td></tr>
        @empty <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">No balances.</td></tr> @endforelse
        </tbody>
        <tfoot class="bg-slate-100 font-semibold"><tr><td colspan="2" class="px-4 py-2 text-right">Total</td><td class="px-4 py-2 text-right">{{ number_format($td,2) }}</td><td class="px-4 py-2 text-right">{{ number_format($tc,2) }}</td></tr></tfoot>
    </table>
</div>
@if(abs($td-$tc)>0.005)<div class="text-red-600 text-sm mt-2">⚠ Out of balance by {{ number_format($td-$tc,2) }}</div>@endif
@endsection
