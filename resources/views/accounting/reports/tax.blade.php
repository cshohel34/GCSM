@extends('layouts.app')
@section('title', 'Tax / VAT Report')
@section('actions')<a href="{{ request()->fullUrlWithQuery(['export'=>'pdf']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a><a href="{{ request()->fullUrlWithQuery(['export'=>'excel']) }}" class="border px-3 py-1.5 rounded text-sm">Excel</a>@endsection
@section('content')
@include('accounting._nav')
@include('accounting.reports._range')
@if ($data)
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-4 py-2 border-b font-semibold text-slate-700">Tax / VAT Payable ({{ optional($account)->code }})</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Date</th><th class="px-4 py-2">Voucher</th><th class="px-4 py-2">Narration</th><th class="px-4 py-2 text-right">Debit</th><th class="px-4 py-2 text-right">Credit</th><th class="px-4 py-2 text-right">Balance</th></tr></thead>
        <tbody>
            <tr class="border-t bg-slate-50"><td colspan="5" class="px-4 py-1.5 text-right italic">Opening</td><td class="px-4 py-1.5 text-right">{{ number_format($data['opening'],2) }}</td></tr>
            @foreach ($data['rows'] as $row)
                <tr class="border-t"><td class="px-4 py-1.5">{{ $row['line']->tdate }}</td><td class="px-4 py-1.5 font-mono text-xs">{{ $row['line']->voucher_no }}</td><td class="px-4 py-1.5 text-slate-500">{{ $row['line']->narration }}</td>
                    <td class="px-4 py-1.5 text-right">{{ $row['line']->debit>0?number_format($row['line']->debit,2):'' }}</td>
                    <td class="px-4 py-1.5 text-right">{{ $row['line']->credit>0?number_format($row['line']->credit,2):'' }}</td>
                    <td class="px-4 py-1.5 text-right">{{ number_format($row['running'],2) }}</td></tr>
            @endforeach
        </tbody>
        <tfoot class="bg-slate-100 font-semibold"><tr><td colspan="5" class="px-4 py-2 text-right">Closing (tax payable)</td><td class="px-4 py-2 text-right">{{ number_format($data['closing'],2) }}</td></tr></tfoot>
    </table>
</div>
@else <div class="bg-white rounded-lg shadow p-8 text-center text-slate-400">Tax/VAT Payable account (2160) not found.</div> @endif
@endsection
