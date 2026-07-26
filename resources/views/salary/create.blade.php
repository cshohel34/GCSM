@extends('layouts.app')
@section('title', 'Generate Salary Sheet')
@section('content')
<form method="POST" action="{{ route('salary.store') }}" class="bg-white rounded-lg shadow p-6 max-w-2xl">
    @csrf
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div><label class="block mb-1">Company *</label>
            <select name="principal_id" id="principal" required class="w-full border rounded px-2 py-1.5" onchange="filterVessels()"><option value="">—</option>
                @foreach ($principals as $p)<option value="{{ $p->id }}" @selected(old('principal_id')==$p->id)>{{ $p->name }}</option>@endforeach
            </select></div>
        <div><label class="block mb-1">Vessel (optional)</label>
            <select name="principal_vessel_id" id="vessel" class="w-full border rounded px-2 py-1.5"><option value="">All vessels</option>
                @foreach ($principals as $p)@foreach ($p->vessels as $v)
                    <option value="{{ $v->id }}" data-principal="{{ $p->id }}">{{ $v->vessel_name }}</option>
                @endforeach @endforeach
            </select></div>
        <div><label class="block mb-1">Month *</label><input name="month" value="{{ old('month') }}" placeholder="e.g. FEB-26" required class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">USD → BDT rate *</label><input name="usd_rate" type="number" step="0.0001" value="{{ old('usd_rate') }}" required class="w-full border rounded px-2 py-1.5"></div>
        <div class="col-span-2"><label class="block mb-1">Reference</label><input name="reference" value="{{ old('reference') }}" placeholder="e.g. XIAMENHAITAO/GCSM/2025/007" class="w-full border rounded px-2 py-1.5"></div>
    </div>
    <div class="flex gap-2 mt-5">
        <button class="bg-[#1F3864] text-white px-5 py-2 rounded hover:bg-[#2E74B5]">Generate from onboard crew</button>
        <a href="{{ route('salary.index') }}" class="px-5 py-2 rounded border">Cancel</a>
    </div>
    <p class="text-xs text-slate-400 mt-3">The sheet is pre-filled with every crew currently onboard for the selected company/vessel, using their contract salary & agency fee. You can then edit each line.</p>
</form>
<script>
function filterVessels(){
    var pid = document.getElementById('principal').value;
    document.querySelectorAll('#vessel option[data-principal]').forEach(function(o){
        o.hidden = (o.dataset.principal !== pid);
    });
    document.getElementById('vessel').value = '';
}
</script>
@endsection
