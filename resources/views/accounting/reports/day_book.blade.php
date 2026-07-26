@extends('layouts.app')
@section('title', 'Day Book')
@section('actions')<a href="{{ request()->fullUrlWithQuery(['export'=>'pdf']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a><a href="{{ request()->fullUrlWithQuery(['export'=>'excel']) }}" class="border px-3 py-1.5 rounded text-sm">Excel</a>@endsection
@section('content')
@include('accounting._nav')
@include('accounting.reports._range')
<div class="space-y-3">
@forelse ($vouchers as $v)
    <div class="bg-white rounded-lg shadow p-3 text-sm">
        <div class="flex justify-between border-b pb-1 mb-1">
            <span><a href="{{ route('accounting.vouchers.show', $v) }}" class="font-mono text-[#2E74B5]">{{ $v->voucher_no }}</a> · {{ ucfirst($v->voucher_type) }} · {{ $v->date->toDateString() }}</span>
            <span class="text-slate-400">{{ $v->narration }}</span>
        </div>
        @foreach ($v->lines as $l)
            <div class="flex justify-between {{ $l->credit>0 ? 'pl-6' : '' }}">
                <span>{{ $l->account->code }} — {{ $l->account->name }} @if($l->partyName())<span class="text-slate-400 text-xs">({{ $l->partyName() }})</span>@endif</span>
                <span>{{ $l->debit>0 ? 'Dr '.number_format($l->debit,2) : 'Cr '.number_format($l->credit,2) }}</span>
            </div>
        @endforeach
    </div>
@empty <div class="bg-white rounded-lg shadow p-8 text-center text-slate-400">No entries in this range.</div> @endforelse
</div>
@endsection
