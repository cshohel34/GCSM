@extends('layouts.app')
@section('title', 'Cash / Bank Book')
@section('actions')<a href="{{ request()->fullUrlWithQuery(['export'=>'pdf']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a><a href="{{ request()->fullUrlWithQuery(['export'=>'excel']) }}" class="border px-3 py-1.5 rounded text-sm">Excel</a>@endsection
@section('content')
@include('accounting._nav')
@include('accounting.reports._range', ['slot' => '<div><label class="block text-xs text-slate-400">Account</label><select name="account_id" class="border rounded px-2 py-1.5">'.collect($accounts)->map(fn($a)=>'<option value="'.$a->id.'" '.(($account&&$account->id==$a->id)?'selected':'').'>'.e($a->name).' ['.$a->currency.']</option>')->implode('').'</select></div>'])
@if ($data)
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-4 py-2 border-b font-semibold text-slate-700">{{ $account->name }} [{{ $account->currency }}]</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Date</th><th class="px-4 py-2">Voucher</th><th class="px-4 py-2">Narration</th><th class="px-4 py-2 text-right">In (Dr)</th><th class="px-4 py-2 text-right">Out (Cr)</th><th class="px-4 py-2 text-right">Balance</th></tr></thead>
        <tbody>
            <tr class="border-t bg-slate-50"><td colspan="5" class="px-4 py-1.5 text-right italic">Opening</td><td class="px-4 py-1.5 text-right">{{ number_format($data['opening'],2) }}</td></tr>
            @foreach ($data['rows'] as $row)
                <tr class="border-t"><td class="px-4 py-1.5">{{ $row['line']->tdate }}</td><td class="px-4 py-1.5 font-mono text-xs">{{ $row['line']->voucher_no }}</td>
                    <td class="px-4 py-1.5 text-slate-500">{{ $row['line']->narration }}</td>
                    <td class="px-4 py-1.5 text-right">{{ $row['line']->debit>0?number_format($row['line']->debit,2):'' }}</td>
                    <td class="px-4 py-1.5 text-right">{{ $row['line']->credit>0?number_format($row['line']->credit,2):'' }}</td>
                    <td class="px-4 py-1.5 text-right">{{ number_format($row['running'],2) }}</td></tr>
            @endforeach
        </tbody>
        <tfoot class="bg-slate-100 font-semibold"><tr><td colspan="5" class="px-4 py-2 text-right">Closing</td><td class="px-4 py-2 text-right">{{ number_format($data['closing'],2) }}</td></tr></tfoot>
    </table>
</div>
@else <div class="bg-white rounded-lg shadow p-8 text-center text-slate-400">No cash/bank account.</div> @endif
@endsection
