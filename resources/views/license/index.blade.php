@extends('layouts.app')
@section('title', 'Company Licences')
@section('actions')<a href="{{ route('license.export', ['export'=>'pdf']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">PDF</a><a href="{{ route('license.export', ['export'=>'excel']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">Excel</a>@can('license.create')<a href="{{ route('license.create') }}" class="bg-[#1F3864] text-white px-3 py-1.5 rounded text-sm hover:bg-[#2E74B5]">+ New Licence</a>@endcan @endsection
@section('content')
<form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 flex gap-3 text-sm">
    <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name" class="border rounded px-2 py-1.5">
    <select name="status" class="border rounded px-2 py-1.5"><option value="">Any status</option>
        @foreach (['valid'=>'Valid','expiring'=>'Expiring','expired'=>'Expired'] as $k=>$v)<option value="{{ $k }}" @selected(($filters['status'] ?? '')===$k)>{{ $v }}</option>@endforeach
    </select>
    <button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Search</button>
    <a href="{{ route('license.index') }}" class="px-4 py-1.5 rounded border">Reset</a>
</form>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Licence</th><th class="px-4 py-2">No.</th><th class="px-4 py-2">Authority</th><th class="px-4 py-2">Expiry</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr></thead>
        <tbody>
        @forelse ($licenses as $l)
            <tr class="border-t">
                <td class="px-4 py-2 font-medium">{{ $l->name }}</td><td class="px-4 py-2">{{ $l->license_no }}</td>
                <td class="px-4 py-2">{{ $l->issuing_authority }}</td><td class="px-4 py-2">{{ optional($l->expiry_date)->toDateString() }}</td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs {{ ['valid'=>'bg-emerald-100 text-emerald-700','expiring'=>'bg-amber-100 text-amber-700','expired'=>'bg-red-100 text-red-700','na'=>'bg-slate-100 text-slate-500'][$l->status] }}">{{ ucfirst($l->status) }}</span></td>
                <td class="px-4 py-2 text-right">
                    @if($l->scan_path)<a href="{{ asset('storage/'.$l->scan_path) }}" target="_blank" class="text-[#2E74B5] text-xs mr-2">scan</a>@endif
                    @can('license.edit')<a href="{{ route('license.edit', $l) }}" class="text-[#2E74B5] text-xs">edit</a>@endcan <a href="{{ route('license.history', $l) }}" class="text-[#2E74B5] text-xs ml-1">history</a>
                </td>
            </tr>
        @empty <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No licences.</td></tr> @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $licenses->links() }}</div>
@endsection
