@extends('layouts.app')
@section('title', $license->exists ? 'Edit Licence' : 'New Licence')
@section('content')
<form method="POST" action="{{ $license->exists ? route('license.update', $license) : route('license.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 max-w-2xl">
    @csrf @if ($license->exists) @method('PUT') @endif
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div class="col-span-2"><label class="block mb-1">Licence name *</label><input name="name" value="{{ old('name', $license->name) }}" required class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Licence no.</label><input name="license_no" value="{{ old('license_no', $license->license_no) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Issuing authority</label><input name="issuing_authority" value="{{ old('issuing_authority', $license->issuing_authority) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Issue date</label><input type="date" name="issue_date" value="{{ old('issue_date', optional($license->issue_date)->toDateString()) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Expiry date</label><input type="date" name="expiry_date" value="{{ old('expiry_date', optional($license->expiry_date)->toDateString()) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div class="col-span-2"><label class="block mb-1">Scan copy</label><input type="file" name="scan" class="text-xs">@if($license->scan_path)<a href="{{ asset('storage/'.$license->scan_path) }}" target="_blank" class="text-[#2E74B5] text-xs ml-2">current</a>@endif</div>
        <div class="col-span-2"><label class="block mb-1">Notes</label><textarea name="notes" class="w-full border rounded px-2 py-1.5">{{ old('notes', $license->notes) }}</textarea></div>
    </div>
    <div class="flex gap-2 mt-5">
        <button class="bg-[#1F3864] text-white px-5 py-2 rounded">{{ $license->exists ? 'Save' : 'Create' }}</button>
        <a href="{{ route('license.index') }}" class="px-5 py-2 rounded border">Cancel</a>
        @if ($license->exists)@can('license.delete')
            <form method="POST" action="{{ route('license.destroy', $license) }}" onsubmit="return confirm('Delete?')" class="ml-auto">@csrf @method('DELETE')<button class="text-red-500 px-3 py-2">Delete</button></form>
        @endcan @endif
    </div>
    <p class="text-xs text-slate-400 mt-3">Status is derived automatically from the expiry date. Daily reminders start one month before expiry.</p>
</form>
@endsection
