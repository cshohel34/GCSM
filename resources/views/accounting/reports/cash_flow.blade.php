@extends('layouts.app')
@section('title', 'Cash Flow')
@section('actions')<a href="{{ request()->fullUrlWithQuery(['export'=>'pdf']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a><a href="{{ request()->fullUrlWithQuery(['export'=>'excel']) }}" class="border px-3 py-1.5 rounded text-sm">Excel</a>@endsection
@section('content')
@include('accounting._nav')
@include('accounting.reports._range')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Account</th><th class="px-4 py-2 text-right">Opening</th><th class="px-4 py-2 text-right">Inflow</th><th class="px-4 py-2 text-right">Outflow</th><th class="px-4 py-2 text-right">Closing</th></tr></thead>
        <tbody>
        @foreach ($data as $d)
            <tr class="border-t"><td class="px-4 py-1.5">{{ $d['account']->name }} [{{ $d['account']->currency }}]</td>
                <td class="px-4 py-1.5 text-right">{{ number_format($d['opening'],2) }}</td>
                <td class="px-4 py-1.5 text-right text-emerald-700">{{ number_format($d['in'],2) }}</td>
                <td class="px-4 py-1.5 text-right text-red-700">{{ number_format($d['out'],2) }}</td>
                <td class="px-4 py-1.5 text-right font-medium">{{ number_format($d['closing'],2) }}</td></tr>
        @endforeach
        </tbody>
        <tfoot class="bg-slate-100 font-semibold"><tr><td class="px-4 py-2 text-right">Total flow</td><td></td><td class="px-4 py-2 text-right">{{ number_format($ti,2) }}</td><td class="px-4 py-2 text-right">{{ number_format($to2,2) }}</td><td></td></tr></tfoot>
    </table>
</div>
@endsection
