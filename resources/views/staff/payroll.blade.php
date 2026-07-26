@extends('layouts.app')
@section('title', 'Staff Payroll')
@section('actions')<a href="{{ route('staff.index') }}" class="border px-3 py-1.5 rounded text-sm">Staff list</a>@endsection
@section('content')
<div class="bg-white rounded-lg shadow p-4 mb-4 flex items-end gap-3 text-sm">
    <form method="GET" class="flex items-end gap-2">
        <div><label class="block text-xs text-slate-400">Month</label><input name="month" value="{{ $month }}" class="border rounded px-2 py-1.5"></div>
        <button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">View</button>
    </form>
    @can('staff.edit')
    <form method="POST" action="{{ route('staff.payroll.generate') }}" class="flex items-end gap-2">@csrf<input type="hidden" name="month" value="{{ $month }}"><button class="bg-slate-700 text-white px-4 py-1.5 rounded">Generate for {{ $month }}</button></form>
    @endcan
</div>
<div class="bg-white rounded-lg shadow p-4">
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left"><tr><th class="py-1">Staff</th><th>Basic</th><th>Allowance</th><th>Bonus</th><th>Deduction</th><th>Net</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($rows as $r)
            <tr class="border-t">
                <td class="py-1.5">{{ optional($r->user)->name }}</td>
                @can('staff.edit')
                <form method="POST" action="{{ route('staff.payroll.update', $r) }}" id="pr{{ $r->id }}">@csrf @method('PUT')</form>
                <td><input form="pr{{ $r->id }}" name="basic" value="{{ $r->basic }}" class="border rounded px-1 py-0.5 w-20"></td>
                <td><input form="pr{{ $r->id }}" name="allowance" value="{{ $r->allowance }}" class="border rounded px-1 py-0.5 w-20"></td>
                <td><input form="pr{{ $r->id }}" name="bonus" value="{{ $r->bonus }}" class="border rounded px-1 py-0.5 w-20"></td>
                <td><input form="pr{{ $r->id }}" name="deduction" value="{{ $r->deduction }}" class="border rounded px-1 py-0.5 w-20"></td>
                @else
                <td>{{ $r->basic }}</td><td>{{ $r->allowance }}</td><td>{{ $r->bonus }}</td><td>{{ $r->deduction }}</td>
                @endcan
                <td class="font-medium">{{ number_format($r->net,2) }}</td>
                <td>@if($r->status==='paid')<span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded">Paid</span>@else<span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">Pending</span>@endif</td>
                <td class="text-right">@can('staff.edit')
                    <button form="pr{{ $r->id }}" class="text-xs bg-[#1F3864] text-white rounded px-2 py-0.5">Save</button>
                    @if($r->status!=='paid')<form method="POST" action="{{ route('staff.payroll.pay', $r) }}" class="inline">@csrf<button class="text-xs text-emerald-600 ml-1">pay</button></form>@endif
                @endcan</td>
            </tr>
        @empty <tr><td colspan="8" class="py-6 text-center text-slate-400">No payroll rows. Generate for this month.</td></tr> @endforelse
        </tbody>
    </table>
</div>
@endsection
