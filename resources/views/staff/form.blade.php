@extends('layouts.app')
@section('title', $staff->exists ? 'Edit '.$staff->name : 'New Staff / Partner')
@section('content')
<form method="POST" action="{{ $staff->exists ? route('staff.update', $staff) : route('staff.store') }}" class="bg-white rounded-lg shadow p-6 max-w-3xl">
    @csrf @if ($staff->exists) @method('PUT') @endif
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div><label class="block mb-1">Name *</label><input name="name" value="{{ old('name', $staff->name) }}" required class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Email *</label><input name="email" value="{{ old('email', $staff->email) }}" required class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Phone</label><input name="phone" value="{{ old('phone', $staff->phone) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Password {{ $staff->exists ? '(leave blank to keep)' : '*' }}</label><input type="password" name="password" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Type *</label><select name="user_type" class="w-full border rounded px-2 py-1.5">
            <option value="staff" @selected(old('user_type', $staff->user_type)==='staff')>Staff</option>
            <option value="partner" @selected(old('user_type', $staff->user_type)==='partner')>Partner</option></select></div>
        <div><label class="block mb-1">Status *</label><select name="status" class="w-full border rounded px-2 py-1.5">
            <option value="active" @selected(old('status', $staff->status)==='active')>Active</option>
            <option value="inactive" @selected(old('status', $staff->status)==='inactive')>Inactive (blocks login)</option></select></div>
        <div><label class="block mb-1">Office</label><select name="office" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
            <option value="Dhaka" @selected(old('office', $staff->office)==='Dhaka')>Dhaka</option>
            <option value="Chittagong" @selected(old('office', $staff->office)==='Chittagong')>Chittagong</option></select></div>
        <div><label class="block mb-1">Role</label><select name="role" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
            @foreach ($roles as $r)<option value="{{ $r->name }}" @selected($staff->exists && $staff->hasRole($r->name))>{{ $r->name }}</option>@endforeach</select></div>
    </div>

    <h3 class="font-semibold text-slate-700 mt-6 mb-2 text-sm">Partner fee-share (only applies to partners)</h3>
    <div class="grid grid-cols-3 gap-4 text-sm">
        <div><label class="block mb-1">% of service charge</label><input name="share_service_charge_pct" value="{{ old('share_service_charge_pct', $staff->share_service_charge_pct) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">% of agency fee</label><input name="share_agency_fee_pct" value="{{ old('share_agency_fee_pct', $staff->share_agency_fee_pct) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">% of net profit</label><input name="share_net_profit_pct" value="{{ old('share_net_profit_pct', $staff->share_net_profit_pct) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div class="col-span-3"><label class="block mb-1">Share notes (e.g. negotiated terms)</label><input name="share_notes" value="{{ old('share_notes', $staff->share_notes) }}" class="w-full border rounded px-2 py-1.5"></div>
    </div>

    <div class="flex gap-2 mt-5">
        <button class="bg-[#1F3864] text-white px-5 py-2 rounded">{{ $staff->exists ? 'Save' : 'Create' }}</button>
        <a href="{{ $staff->exists ? route('staff.show', $staff) : route('staff.index') }}" class="px-5 py-2 rounded border">Cancel</a>
    </div>
</form>
@endsection
