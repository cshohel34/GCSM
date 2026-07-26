@extends('layouts.app')
@section('title', 'Staff & Partners')
@section('actions')<a href="{{ route('staff.payroll.index') }}" class="border px-3 py-1.5 rounded text-sm mr-1">Payroll</a>@can('staff.create')<a href="{{ route('staff.create') }}" class="bg-[#1F3864] text-white px-3 py-1.5 rounded text-sm hover:bg-[#2E74B5]">+ New</a>@endcan @endsection
@section('content')
<form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
    <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name" class="border rounded px-2 py-1.5">
    <select name="user_type" class="border rounded px-2 py-1.5"><option value="">Staff & partners</option>
        <option value="staff" @selected(($filters['user_type'] ?? '')==='staff')>Staff</option>
        <option value="partner" @selected(($filters['user_type'] ?? '')==='partner')>Partners</option></select>
    <select name="status" class="border rounded px-2 py-1.5"><option value="">Any status</option>
        <option value="active" @selected(($filters['status'] ?? '')==='active')>Active</option>
        <option value="inactive" @selected(($filters['status'] ?? '')==='inactive')>Inactive</option></select>
    <div class="flex gap-2"><button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Search</button><a href="{{ route('staff.index') }}" class="px-4 py-1.5 rounded border">Reset</a></div>
</form>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left"><tr><th class="px-4 py-2">Name</th><th class="px-4 py-2">Type</th><th class="px-4 py-2">Email</th><th class="px-4 py-2">Role</th><th class="px-4 py-2">Status</th></tr></thead>
        <tbody>
        @forelse ($users as $u)
            <tr class="border-t">
                <td class="px-4 py-2"><a href="{{ route('staff.show', $u) }}" class="text-[#2E74B5] font-medium hover:underline">{{ $u->name }}</a></td>
                <td class="px-4 py-2 capitalize">{{ $u->user_type }}</td>
                <td class="px-4 py-2">{{ $u->email }}</td>
                <td class="px-4 py-2">{{ $u->getRoleNames()->implode(', ') }}</td>
                <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $u->status==='active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($u->status) }}</span></td>
            </tr>
        @empty <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">None found.</td></tr> @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
