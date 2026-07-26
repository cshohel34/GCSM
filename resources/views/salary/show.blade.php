@extends('layouts.app')
@section('title', 'Salary Sheet · '.$sheet->month)
@section('actions')
    <a href="{{ route('salary.pdf', $sheet) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a>
    <a href="{{ route('salary.excel', $sheet) }}" class="border px-3 py-1.5 rounded text-sm mr-1">Excel</a>
    @can('salary.edit')
        @if ($sheet->status==='draft')
        <form method="POST" action="{{ route('salary.reconcile', $sheet) }}" class="inline">@csrf<button class="border px-3 py-1.5 rounded text-sm">Mark reconciled</button></form>
        @endif
    @endcan
    @can('salary.approve')
        @if ($sheet->status==='reconciled')
        <form method="POST" action="{{ route('salary.approve', $sheet) }}" class="inline" onsubmit="return confirm('Approve and lock? No further edits allowed.')">@csrf<button class="bg-emerald-600 text-white px-3 py-1.5 rounded text-sm">Approve & lock</button></form>
        @endif
    @endcan
@endsection
@section('content')

<div class="bg-white rounded-lg shadow p-4 mb-4 flex justify-between items-center">
    <div>
        <div class="text-lg font-semibold text-[#1F3864]">{{ optional($sheet->principal)->name }} — {{ $sheet->month }}</div>
        <div class="text-sm text-slate-500">Ref {{ $sheet->reference ?: '—' }} · Vessel {{ optional($sheet->vessel)->vessel_name ?: 'All' }} · USD rate {{ $sheet->usd_rate }}
            <span class="ml-1 px-2 py-0.5 rounded text-xs {{ ['draft'=>'bg-slate-100 text-slate-600','reconciled'=>'bg-amber-100 text-amber-700','locked'=>'bg-emerald-100 text-emerald-700'][$sheet->status] }}">{{ ucfirst($sheet->status) }}</span>
            @if ($sheet->status==='locked')<span class="text-xs text-slate-400">approved by {{ optional($sheet->approvedBy)->name }} {{ optional($sheet->approved_at)->toDateString() }}</span>@endif
        </div>
    </div>
</div>

@can('salary.edit')
@if ($sheet->isEditable())
<div class="bg-white rounded-lg shadow p-3 mb-4 flex items-center gap-2 text-sm">
    <form method="POST" action="{{ route('salary.company_sheet', $sheet) }}" enctype="multipart/form-data" class="flex items-center gap-2">
        @csrf
        <span class="text-slate-500">Company salary sheet:</span>
        <input type="file" name="company_sheet" required class="text-xs">
        <button class="bg-slate-700 text-white rounded px-3 py-1">Upload</button>
    </form>
    @if ($sheet->company_sheet_path)<a href="{{ asset('storage/'.$sheet->company_sheet_path) }}" target="_blank" class="text-[#2E74B5] text-xs">view uploaded</a>@endif
    <span class="text-xs text-slate-400 ml-2">Enter each line's company amount below; reconcile is allowed only when all match.</span>
</div>
@endif
@endcan

{{-- Full 20-column view (matches Appendix B; scroll horizontally / print via PDF) --}}
<div class="bg-white rounded-lg shadow overflow-x-auto mb-4">
    <table class="text-xs whitespace-nowrap w-full">
        <thead class="bg-[#1F3864] text-white text-left">
            <tr>
                <th class="px-2 py-1">SL</th><th class="px-2 py-1">Crew</th><th class="px-2 py-1">Ship</th><th class="px-2 py-1">Rank</th>
                <th class="px-2 py-1">Salary USD</th><th class="px-2 py-1">Bonus</th><th class="px-2 py-1">Days</th><th class="px-2 py-1">Work</th><th class="px-2 py-1">Ded</th>
                <th class="px-2 py-1">Gross USD</th><th class="px-2 py-1">Net USD</th><th class="px-2 py-1">Net BDT</th>
                <th class="px-2 py-1">Agent USD</th><th class="px-2 py-1">Agent Net</th><th class="px-2 py-1">Agent BDT</th><th class="px-2 py-1">Remarks</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($sheet->lines as $l)
            <tr class="border-t {{ $l->is_held ? 'bg-amber-50' : '' }}">
                <td class="px-2 py-1">{{ $l->sl_no }}</td>
                <td class="px-2 py-1">{{ $l->crew_name }} @if($l->is_held)<span class="text-amber-600">(held)</span>@endif</td>
                <td class="px-2 py-1">{{ $l->ship_name }}</td><td class="px-2 py-1">{{ $l->rank }}</td>
                <td class="px-2 py-1 text-right">{{ number_format($l->salary_usd,2) }}</td>
                <td class="px-2 py-1 text-right">{{ number_format($l->bonus_usd,2) }}</td>
                <td class="px-2 py-1 text-right">{{ $l->total_days }}</td>
                <td class="px-2 py-1 text-right">{{ $l->working_days }}</td>
                <td class="px-2 py-1 text-right">{{ $l->deduct_days }}</td>
                <td class="px-2 py-1 text-right">{{ number_format($l->gross_usd,2) }}</td>
                <td class="px-2 py-1 text-right">{{ number_format($l->net_usd,2) }}</td>
                <td class="px-2 py-1 text-right font-medium">{{ number_format($l->net_bdt,2) }}</td>
                <td class="px-2 py-1 text-right">{{ number_format($l->agent_fee_usd,2) }}</td>
                <td class="px-2 py-1 text-right">{{ number_format($l->agent_net_usd,2) }}</td>
                <td class="px-2 py-1 text-right">{{ number_format($l->agent_net_bdt,2) }}</td>
                <td class="px-2 py-1 max-w-xs truncate" title="{{ $l->remarks }}">{{ $l->remarks }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot class="bg-slate-100 font-semibold">
            <tr><td colspan="11" class="px-2 py-1 text-right">TOTAL BDT (crew / agent):</td>
                <td class="px-2 py-1 text-right">{{ number_format($sheet->totalNetBdt(),2) }}</td>
                <td colspan="2"></td>
                <td class="px-2 py-1 text-right">{{ number_format($sheet->totalAgentBdt(),2) }}</td><td></td></tr>
        </tfoot>
    </table>
</div>

@can('salary.edit')
@if ($sheet->isEditable())
<div class="bg-white rounded-lg shadow p-4 mb-4">
    <h3 class="font-semibold text-slate-700 mb-3">Edit lines</h3>
    <div class="space-y-3">
    @foreach ($sheet->lines as $l)
        <div class="border-b pb-3">
            <form method="POST" action="{{ route('salary.lines.update', [$sheet,$l]) }}" class="grid grid-cols-2 md:grid-cols-10 gap-2 text-xs items-end">
                @csrf @method('PUT')
                <div class="md:col-span-2"><span class="font-medium">{{ $l->sl_no }}. {{ $l->crew_name }}</span></div>
                <div><label class="block text-slate-400">Salary</label><input name="salary_usd" value="{{ $l->salary_usd }}" class="w-full border rounded px-1 py-0.5"></div>
                <div><label class="block text-slate-400">Bonus</label><input name="bonus_usd" value="{{ $l->bonus_usd }}" class="w-full border rounded px-1 py-0.5"></div>
                <div><label class="block text-slate-400">Days</label><input name="total_days" value="{{ $l->total_days }}" class="w-full border rounded px-1 py-0.5"></div>
                <div><label class="block text-slate-400">Work</label><input name="working_days" value="{{ $l->working_days }}" class="w-full border rounded px-1 py-0.5"></div>
                <div><label class="block text-slate-400">Deduct</label><input name="deduct_days" value="{{ $l->deduct_days }}" class="w-full border rounded px-1 py-0.5"></div>
                <div><label class="block text-slate-400">Transfer chg</label><input name="transfer_charge_usd" value="{{ $l->transfer_charge_usd }}" class="w-full border rounded px-1 py-0.5"></div>
                <div><label class="block text-slate-400">Agent fee</label><input name="agent_fee_usd" value="{{ $l->agent_fee_usd }}" class="w-full border rounded px-1 py-0.5"></div>
                <div><label class="block text-slate-400">Agent chg</label><input name="agent_fee_charge_usd" value="{{ $l->agent_fee_charge_usd }}" class="w-full border rounded px-1 py-0.5"></div>
                <div class="md:col-span-5"><label class="block text-slate-400">Remarks (bank A/C)</label><input name="remarks" value="{{ $l->remarks }}" class="w-full border rounded px-1 py-0.5"></div>
                <div><label class="block text-slate-400">Company amt</label><input name="company_amount" value="{{ $l->company_amount }}" class="w-full border rounded px-1 py-0.5"></div>
                <div><label class="block text-slate-400">Match</label><div class="px-1 py-0.5 text-xs {{ $l->company_amount!==null && abs((float)$l->company_amount-(float)$l->net_usd)<0.01 ? 'text-emerald-600' : 'text-red-600' }}">{{ $l->company_amount===null ? '—' : (abs((float)$l->company_amount-(float)$l->net_usd)<0.01 ? '✓' : 'Δ '.number_format((float)$l->company_amount-(float)$l->net_usd,2)) }}</div></div>
                <div class="md:col-span-2"><button class="bg-[#1F3864] text-white rounded px-3 py-1 w-full">Save</button></div>
            </form>
            <div class="flex gap-1 mt-1 text-xs">
                @if ($l->is_held)
                    <form method="POST" action="{{ route('salary.release', [$sheet,$l]) }}">@csrf<button class="bg-amber-500 text-white rounded px-2 py-0.5">Release hold</button></form>
                @else
                    <form method="POST" action="{{ route('salary.hold', [$sheet,$l]) }}" class="flex gap-1">@csrf<input name="reason" placeholder="hold reason" class="border rounded px-1 py-0.5"><button class="bg-amber-100 text-amber-700 rounded px-2 py-0.5">Hold</button></form>
                @endif
                <form method="POST" action="{{ route('salary.lines.destroy', [$sheet,$l]) }}" onsubmit="return confirm('Remove line?')">@csrf @method('DELETE')<button class="text-red-500 px-2">Remove line</button></form>
            </div>
        </div>
    @endforeach
    </div>

    <form method="POST" action="{{ route('salary.lines.store', $sheet) }}" class="grid grid-cols-2 md:grid-cols-5 gap-2 text-xs items-end mt-4 border-t pt-3">
        @csrf
        <input name="crew_name" placeholder="Crew name *" required class="border rounded px-2 py-1">
        <input name="rank" placeholder="Rank" class="border rounded px-2 py-1">
        <input name="salary_usd" placeholder="Salary USD *" required class="border rounded px-2 py-1">
        <input name="crew_profile_id" placeholder="Crew DB id (optional)" class="border rounded px-2 py-1">
        <button class="bg-slate-700 text-white rounded px-3 py-1">Add manual line</button>
    </form>
    <p class="text-xs text-slate-400 mt-2">Note: a held month is excluded from "paid". Approving the sheet (Super Admin) locks it — no further edits.</p>
</div>
@else
<div class="mb-4 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2 text-sm">This sheet is locked and cannot be edited.</div>
@endif
@endcan
@endsection
