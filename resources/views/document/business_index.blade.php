@extends('layouts.app')
@section('title', 'Business Documents')
@section('actions')
    <a href="{{ route('document.index') }}" class="border px-3 py-1.5 rounded text-sm mr-1">Crew Documents</a>
    @can('document.create')<a href="{{ route('document.business.create') }}" class="bg-[#1F3864] text-white px-3 py-1.5 rounded text-sm">+ New</a>@endcan
@endsection
@section('content')
<form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 flex gap-3 text-sm">
    <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Title" class="border rounded px-2 py-1.5">
    <select name="status" class="border rounded px-2 py-1.5"><option value="">Any status</option>
        @foreach (['valid'=>'Valid','expiring'=>'Expiring','expired'=>'Expired'] as $k=>$v)<option value="{{ $k }}" @selected(($filters['status'] ?? '')===$k)>{{ $v }}</option>@endforeach</select>
    <button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Search</button>
    <a href="{{ route('document.business.index') }}" class="px-4 py-1.5 rounded border">Reset</a>
</form>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Title</th><th class="px-4 py-2">Category</th><th class="px-4 py-2">Number</th><th class="px-4 py-2">Expiry</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr></thead>
        <tbody>
        @forelse ($docs as $d)
            <tr class="border-t"><td class="px-4 py-2 font-medium">{{ $d->title }}</td><td class="px-4 py-2">{{ $d->category }}</td><td class="px-4 py-2">{{ $d->number }}</td>
                <td class="px-4 py-2">{{ optional($d->expiry_date)->toDateString() }}</td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs {{ ['valid'=>'bg-emerald-100 text-emerald-700','expiring'=>'bg-amber-100 text-amber-700','expired'=>'bg-red-100 text-red-700','na'=>'bg-slate-100 text-slate-500'][$d->status] }}">{{ ucfirst($d->status) }}</span></td>
                <td class="px-4 py-2 text-right">@if($d->scan_path)<a href="{{ asset('storage/'.$d->scan_path) }}" target="_blank" class="text-[#2E74B5] text-xs mr-2">scan</a>@endif @can('document.edit')<a href="{{ route('document.business.edit', $d) }}" class="text-[#2E74B5] text-xs">edit</a>@endcan</td></tr>
        @empty <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No business documents.</td></tr> @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $docs->links() }}</div>
@endsection
