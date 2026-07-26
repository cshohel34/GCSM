@extends('layouts.app')
@section('title', 'Chart of Accounts')
@section('actions')@can('accounting.create')<a href="{{ route('accounting.accounts.create') }}" class="bg-[#1F3864] text-white px-3 py-1.5 rounded text-sm">+ Account</a>@endcan @endsection
@section('content')
@include('accounting._nav')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Code</th><th class="px-4 py-2">Account</th><th class="px-4 py-2">Type</th><th class="px-4 py-2 text-right">Balance</th><th class="px-4 py-2"></th></tr></thead>
        <tbody>
        @foreach ($accounts as $a)
            <tr class="border-t {{ $a->is_group ? 'bg-slate-50 font-semibold' : '' }}">
                <td class="px-4 py-2 font-mono text-xs">{{ $a->code }}</td>
                <td class="px-4 py-2" style="padding-left: {{ $a->is_group ? 16 : 32 }}px">{{ $a->name }} @if($a->is_cash_bank)<span class="text-xs text-blue-600">[{{ $a->currency }}]</span>@endif</td>
                <td class="px-4 py-2 capitalize text-slate-500">{{ $a->type }}</td>
                <td class="px-4 py-2 text-right">@if(!$a->is_group){{ number_format($balances[$a->id] ?? 0, 2) }}@endif</td>
                <td class="px-4 py-2 text-right">@can('accounting.edit')<a href="{{ route('accounting.accounts.edit', $a) }}" class="text-[#2E74B5] text-xs">edit</a>@endcan</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
