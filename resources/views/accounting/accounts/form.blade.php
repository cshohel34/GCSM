@extends('layouts.app')
@section('title', $account->exists ? 'Edit Account' : 'New Account')
@section('content')
@include('accounting._nav')
<form method="POST" action="{{ $account->exists ? route('accounting.accounts.update', $account) : route('accounting.accounts.store') }}" class="bg-white rounded-lg shadow p-6 max-w-2xl">
    @csrf @if ($account->exists) @method('PUT') @endif
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div><label class="block mb-1">Code *</label><input name="code" value="{{ old('code', $account->code) }}" required class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Name *</label><input name="name" value="{{ old('name', $account->name) }}" required class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Type *</label><select name="type" class="w-full border rounded px-2 py-1.5">
            @foreach (['asset','liability','equity','income','expense'] as $t)<option value="{{ $t }}" @selected(old('type', $account->type)===$t)>{{ ucfirst($t) }}</option>@endforeach</select></div>
        <div><label class="block mb-1">Parent (group)</label><select name="parent_id" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
            @foreach ($parents as $p)<option value="{{ $p->id }}" @selected(old('parent_id', $account->parent_id)==$p->id)>{{ $p->code }} — {{ $p->name }}</option>@endforeach</select></div>
        <div><label class="block mb-1">Currency</label><select name="currency" class="w-full border rounded px-2 py-1.5"><option value="BDT" @selected(old('currency',$account->currency)==='BDT')>BDT</option><option value="USD" @selected(old('currency',$account->currency)==='USD')>USD</option></select></div>
        <div class="flex gap-4 items-end">
            <label class="text-xs"><input type="checkbox" name="is_group" value="1" @checked(old('is_group',$account->is_group))> Group header</label>
            <label class="text-xs"><input type="checkbox" name="is_cash_bank" value="1" @checked(old('is_cash_bank',$account->is_cash_bank))> Cash/Bank</label>
        </div>
        <div><label class="block mb-1">Opening balance</label><input name="opening_balance" value="{{ old('opening_balance', $account->opening_balance) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Opening side</label><select name="opening_side" class="w-full border rounded px-2 py-1.5"><option value="">auto</option><option value="debit" @selected(old('opening_side',$account->opening_side)==='debit')>Debit</option><option value="credit" @selected(old('opening_side',$account->opening_side)==='credit')>Credit</option></select></div>
    </div>
    <div class="flex gap-2 mt-5"><button class="bg-[#1F3864] text-white px-5 py-2 rounded">Save</button><a href="{{ route('accounting.accounts.index') }}" class="px-5 py-2 rounded border">Cancel</a></div>
</form>
@endsection
