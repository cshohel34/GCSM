@extends('layouts.app')
@section('title', $title)
@section('actions')<a href="{{ request()->fullUrlWithQuery(['export'=>'pdf']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a><a href="{{ request()->fullUrlWithQuery(['export'=>'excel']) }}" class="border px-3 py-1.5 rounded text-sm">Excel</a>@endsection
@section('content')
@include('accounting._nav')
@php $total=collect($rows)->sum('balance'); @endphp
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-4 py-2 border-b font-semibold text-slate-700">{{ $title }} ({{ $kind }})</div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Party type</th><th class="px-4 py-2">Party</th><th class="px-4 py-2 text-right">Balance</th></tr></thead>
        <tbody>
        @forelse ($rows as $r)
            <tr class="border-t"><td class="px-4 py-1.5 capitalize">{{ $r['party_type'] }}</td>
                <td class="px-4 py-1.5"><a href="{{ route('accounting.reports.party', ['party_type'=>$r['party_type'],'party_id'=>$r['party_id']]) }}" class="text-[#2E74B5] hover:underline">{{ $r['name'] }}</a></td>
                <td class="px-4 py-1.5 text-right font-medium">{{ number_format($r['balance'],2) }}</td></tr>
        @empty <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">Nothing outstanding.</td></tr> @endforelse
        </tbody>
        <tfoot class="bg-slate-100 font-semibold"><tr><td colspan="2" class="px-4 py-2 text-right">Total</td><td class="px-4 py-2 text-right">{{ number_format($total,2) }}</td></tr></tfoot>
    </table>
</div>
@endsection
