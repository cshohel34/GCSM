@extends('layouts.app')
@section('title', $doc->exists ? 'Edit Document' : 'New Business Document')
@section('content')
<form method="POST" action="{{ $doc->exists ? route('document.business.update', $doc) : route('document.business.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 max-w-2xl">
    @csrf @if ($doc->exists) @method('PUT') @endif
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div class="col-span-2"><label class="block mb-1">Title *</label><input name="title" value="{{ old('title', $doc->title) }}" required class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Category</label><input name="category" value="{{ old('category', $doc->category) }}" placeholder="agreement / insurance / registration" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Number</label><input name="number" value="{{ old('number', $doc->number) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Issue date</label><input type="date" name="issue_date" value="{{ old('issue_date', optional($doc->issue_date)->toDateString()) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Expiry date</label><input type="date" name="expiry_date" value="{{ old('expiry_date', optional($doc->expiry_date)->toDateString()) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div class="col-span-2"><label class="block mb-1">Scan</label><input type="file" name="scan" class="text-xs">@if($doc->scan_path)<a href="{{ asset('storage/'.$doc->scan_path) }}" target="_blank" class="text-[#2E74B5] text-xs ml-2">current</a>@endif</div>
        <div class="col-span-2"><label class="block mb-1">Notes</label><textarea name="notes" class="w-full border rounded px-2 py-1.5">{{ old('notes', $doc->notes) }}</textarea></div>
    </div>
    <div class="flex gap-2 mt-5"><button class="bg-[#1F3864] text-white px-5 py-2 rounded">Save</button><a href="{{ route('document.business.index') }}" class="px-5 py-2 rounded border">Cancel</a>
        @if ($doc->exists)@can('document.delete')<form method="POST" action="{{ route('document.business.destroy', $doc) }}" onsubmit="return confirm('Delete?')" class="ml-auto">@csrf @method('DELETE')<button class="text-red-500 px-3 py-2">Delete</button></form>@endcan @endif
    </div>
</form>
@endsection
