@extends('layouts.app')
@section('title', 'New '.ucfirst($type).' Voucher')
@section('content')
<form method="POST" action="{{ route('accounting.vouchers.store') }}" class="bg-white rounded-lg shadow p-6">
    @csrf
    <input type="hidden" name="voucher_type" value="{{ $type }}">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
        <div><label class="block mb-1">Type</label><input value="{{ ucfirst($type) }}" disabled class="w-full border rounded px-2 py-1.5 bg-slate-100"></div>
        <div><label class="block mb-1">Date *</label><input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Reference</label><input name="reference" value="{{ old('reference') }}" class="w-full border rounded px-2 py-1.5"></div>
        <div class="md:col-span-1"><label class="block mb-1">Narration</label><input name="narration" value="{{ old('narration') }}" class="w-full border rounded px-2 py-1.5"></div>
    </div>

    <table class="w-full text-sm mb-2" id="lines">
        <thead class="text-slate-400 text-left"><tr><th class="py-1 w-2/5">Account</th><th>Party (optional)</th><th>Memo</th><th class="text-right">Debit</th><th class="text-right">Credit</th><th></th></tr></thead>
        <tbody></tbody>
        <tfoot class="font-semibold"><tr><td colspan="3" class="text-right py-2">Total</td><td class="text-right"><span id="tot-dr">0.00</span></td><td class="text-right"><span id="tot-cr">0.00</span></td><td><span id="bal" class="text-xs"></span></td></tr></tfoot>
    </table>
    <button type="button" onclick="addRow()" class="border rounded px-3 py-1 text-sm mb-4">+ Add line</button>

    <div class="flex gap-2">
        <button id="submitBtn" class="bg-[#1F3864] text-white px-5 py-2 rounded">Post voucher</button>
        <a href="{{ route('accounting.vouchers.index') }}" class="px-5 py-2 rounded border">Cancel</a>
    </div>
    <p class="text-xs text-slate-400 mt-3">Double-entry: total debit must equal total credit. Attribute a line to a party (principal/crew/partner/staff) to build subsidiary ledgers.</p>
</form>

<script>
const ACCOUNTS = [@foreach($accounts as $a){id:{{ $a->id }},t:"{{ $a->code }} — {{ addslashes($a->name) }}"},@endforeach];
const PARTIES = [@foreach($parties as $k=>$v){k:"{{ $k }}",t:"{{ $v }}"},@endforeach];
let idx = 0;
function addRow(){
    const tb = document.querySelector('#lines tbody');
    const i = idx++;
    const accOpts = ACCOUNTS.map(a=>`<option value="${a.id}">${a.t}</option>`).join('');
    const partyOpts = '<option value="">—</option>'+PARTIES.map(p=>`<option value="${p.k}">${p.t}</option>`).join('');
    const tr = document.createElement('tr');
    tr.className='border-t';
    tr.innerHTML = `
        <td class="py-1"><select name="lines[${i}][account_id]" class="w-full border rounded px-1 py-1"><option value="">— account —</option>${accOpts}</select></td>
        <td><div class="flex gap-1"><select name="lines[${i}][party_type]" class="border rounded px-1 py-1">${partyOpts}</select><input name="lines[${i}][party_id]" placeholder="id" class="border rounded px-1 py-1 w-16"></div></td>
        <td><input name="lines[${i}][memo]" class="border rounded px-1 py-1 w-full"></td>
        <td><input name="lines[${i}][debit]" type="number" step="0.01" min="0" value="0" oninput="recalc()" class="border rounded px-1 py-1 w-24 text-right"></td>
        <td><input name="lines[${i}][credit]" type="number" step="0.01" min="0" value="0" oninput="recalc()" class="border rounded px-1 py-1 w-24 text-right"></td>
        <td><button type="button" onclick="this.closest('tr').remove();recalc()" class="text-red-400 px-1">×</button></td>`;
    tb.appendChild(tr);
}
function recalc(){
    let dr=0,cr=0;
    document.querySelectorAll('input[name$="[debit]"]').forEach(e=>dr+=parseFloat(e.value||0));
    document.querySelectorAll('input[name$="[credit]"]').forEach(e=>cr+=parseFloat(e.value||0));
    document.getElementById('tot-dr').textContent=dr.toFixed(2);
    document.getElementById('tot-cr').textContent=cr.toFixed(2);
    const bal=document.getElementById('bal'), ok=Math.abs(dr-cr)<0.005 && dr>0;
    bal.textContent = ok?'✓ balanced':'✗ diff '+(dr-cr).toFixed(2);
    bal.className='text-xs '+(ok?'text-emerald-600':'text-red-600');
    document.getElementById('submitBtn').disabled=!ok;
    document.getElementById('submitBtn').className='px-5 py-2 rounded '+(ok?'bg-[#1F3864] text-white':'bg-slate-300 text-slate-500');
}
addRow();addRow();recalc();
</script>
@endsection
