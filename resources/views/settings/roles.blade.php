@extends('layouts.app')
@section('title', 'Roles & Permissions')
@section('content')
@include('settings._nav')
<p class="text-sm text-slate-500 mb-4">Tick the permissions each role should have. Super Admin should keep all.</p>
@foreach ($roles as $role)
<div class="bg-white rounded-lg shadow p-4 mb-4">
    <form method="POST" action="{{ route('settings.roles.update', $role) }}">
        @csrf @method('PUT')
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-semibold text-[#1F3864]">{{ $role->name }}</h3>
            @can('settings.edit')<button class="bg-[#1F3864] text-white px-4 py-1.5 rounded text-sm">Save</button>@endcan
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
        @foreach ($permissions as $group => $perms)
            <div class="border rounded p-2">
                <div class="font-medium text-slate-600 capitalize mb-1">{{ $group }}</div>
                @foreach ($perms as $p)
                    <label class="flex items-center text-xs gap-1 py-0.5"><input type="checkbox" name="permissions[]" value="{{ $p->name }}" @checked($role->hasPermissionTo($p->name)) @cannot('settings.edit') disabled @endcannot> {{ $p->name }}</label>
                @endforeach
            </div>
        @endforeach
        </div>
    </form>
</div>
@endforeach
@endsection
