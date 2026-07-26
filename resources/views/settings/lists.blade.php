@extends('layouts.app')
@section('title', 'Settings — Lists & Dropdowns')
@section('content')

@include('settings._nav')

<div class="grid lg:grid-cols-2 gap-4">
    {{-- Ranks --}}
    <div class="bg-white rounded-lg shadow p-4">
        <div class="gcsm-head justify-between">
            <h3 class="font-semibold text-sm md:text-base">Marine Ranks</h3>
            <span class="text-xs text-slate-400">{{ $ranks->count() }} ranks</span>
        </div>
        @can('settings.edit')
        <form method="POST" action="{{ route('settings.ranks.store') }}" class="flex flex-wrap gap-2 mb-3 text-sm">
            @csrf
            <input name="rank_name" placeholder="New rank *" required class="border rounded px-2 py-1.5 flex-1 min-w-[140px]">
            <input name="department" placeholder="Department (Deck / Engine…)" class="border rounded px-2 py-1.5 w-44">
            <button class="bg-[#1F3864] text-white rounded px-4 py-1.5">Add rank</button>
        </form>
        @endcan
        <div class="max-h-[28rem] overflow-auto">
            <table class="w-full text-sm">
                <thead class="text-slate-400 text-left"><tr><th class="py-1">Rank</th><th>Department</th><th>Status</th>@can('settings.edit')<th></th>@endcan</tr></thead>
                <tbody>
                @foreach ($ranks as $rank)
                    <tr class="border-t {{ $rank->active ? '' : 'opacity-50' }}">
                        @can('settings.edit')
                        <td class="py-1.5" colspan="2">
                            <form method="POST" action="{{ route('settings.ranks.update', $rank) }}" class="flex gap-1">@csrf @method('PUT')
                                <input name="rank_name" value="{{ $rank->rank_name }}" class="border rounded px-2 py-1 text-sm flex-1">
                                <input name="department" value="{{ $rank->department }}" class="border rounded px-2 py-1 text-sm w-32">
                                <button class="text-xs text-[#2E74B5] hover:underline px-1" title="Save">Save</button>
                            </form>
                        </td>
                        @else
                        <td class="py-1.5">{{ $rank->rank_name }}</td><td>{{ $rank->department }}</td>
                        @endcan
                        <td><span class="px-2 py-0.5 rounded text-xs {{ $rank->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $rank->active ? 'Active' : 'Off' }}</span></td>
                        @can('settings.edit')
                        <td class="text-right"><form method="POST" action="{{ route('settings.ranks.toggle', $rank) }}">@csrf<button class="text-xs {{ $rank->active ? 'text-red-500' : 'text-emerald-600' }} hover:underline">{{ $rank->active ? 'Deactivate' : 'Activate' }}</button></form></td>
                        @endcan
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Designations --}}
    <div class="bg-white rounded-lg shadow p-4">
        <div class="gcsm-head justify-between">
            <h3 class="font-semibold text-sm md:text-base">Office Staff Designations</h3>
            <span class="text-xs text-slate-400">{{ $designations->count() }} designations</span>
        </div>
        @can('settings.edit')
        <form method="POST" action="{{ route('settings.designations.store') }}" class="flex flex-wrap gap-2 mb-3 text-sm">
            @csrf
            <input name="name" placeholder="New designation *" required class="border rounded px-2 py-1.5 flex-1 min-w-[160px]">
            <button class="bg-[#1F3864] text-white rounded px-4 py-1.5">Add designation</button>
        </form>
        @endcan
        <div class="max-h-[28rem] overflow-auto">
            <table class="w-full text-sm">
                <thead class="text-slate-400 text-left"><tr><th class="py-1">Designation</th><th>Status</th>@can('settings.edit')<th></th>@endcan</tr></thead>
                <tbody>
                @foreach ($designations as $d)
                    <tr class="border-t {{ $d->active ? '' : 'opacity-50' }}">
                        @can('settings.edit')
                        <td class="py-1.5">
                            <form method="POST" action="{{ route('settings.designations.update', $d) }}" class="flex gap-1">@csrf @method('PUT')
                                <input name="name" value="{{ $d->name }}" class="border rounded px-2 py-1 text-sm flex-1">
                                <button class="text-xs text-[#2E74B5] hover:underline px-1">Save</button>
                            </form>
                        </td>
                        @else <td class="py-1.5">{{ $d->name }}</td> @endcan
                        <td><span class="px-2 py-0.5 rounded text-xs {{ $d->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $d->active ? 'Active' : 'Off' }}</span></td>
                        @can('settings.edit')
                        <td class="text-right"><form method="POST" action="{{ route('settings.designations.toggle', $d) }}">@csrf<button class="text-xs {{ $d->active ? 'text-red-500' : 'text-emerald-600' }} hover:underline">{{ $d->active ? 'Deactivate' : 'Activate' }}</button></form></td>
                        @endcan
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Maritime Education Institutes --}}
    <div class="bg-white rounded-lg shadow p-4">
        <div class="gcsm-head justify-between">
            <h3 class="font-semibold text-sm md:text-base">Maritime Education Institutes</h3>
            <span class="text-xs text-slate-400">{{ $academies->count() }} institutes</span>
        </div>
        @can('settings.edit')
        <form method="POST" action="{{ route('settings.academies.store') }}" class="flex flex-wrap gap-2 mb-3 text-sm">
            @csrf
            <input name="name" placeholder="New academy / institute *" required class="border rounded px-2 py-1.5 flex-1 min-w-[160px]">
            <select name="category" class="border rounded px-2 py-1.5 w-32 no-enhance"><option value="Govt.">Govt.</option><option value="Private">Private</option></select>
            <button class="bg-[#1F3864] text-white rounded px-4 py-1.5">Add</button>
        </form>
        @endcan
        <div class="max-h-[28rem] overflow-auto">
            <table class="w-full text-sm">
                <thead class="text-slate-400 text-left"><tr><th class="py-1">Institute</th><th>Type</th><th>Status</th>@can('settings.edit')<th></th>@endcan</tr></thead>
                <tbody>
                @foreach ($academies as $a)
                    <tr class="border-t {{ $a->active ? '' : 'opacity-50' }}">
                        @can('settings.edit')
                        <td class="py-1.5" colspan="2">
                            <form method="POST" action="{{ route('settings.academies.update', $a) }}" class="flex gap-1">@csrf @method('PUT')
                                <input name="name" value="{{ $a->name }}" class="border rounded px-2 py-1 text-sm flex-1">
                                <select name="category" class="border rounded px-2 py-1 text-sm w-28 no-enhance"><option value="Govt." @selected($a->category==='Govt.')>Govt.</option><option value="Private" @selected($a->category==='Private')>Private</option></select>
                                <button class="text-xs text-[#2E74B5] hover:underline px-1">Save</button>
                            </form>
                        </td>
                        @else <td class="py-1.5">{{ $a->name }}</td><td>{{ $a->category }}</td> @endcan
                        <td><span class="px-2 py-0.5 rounded text-xs {{ $a->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $a->active ? 'Active' : 'Off' }}</span></td>
                        @can('settings.edit')
                        <td class="text-right"><form method="POST" action="{{ route('settings.academies.toggle', $a) }}">@csrf<button class="text-xs {{ $a->active ? 'text-red-500' : 'text-emerald-600' }} hover:underline">{{ $a->active ? 'Deactivate' : 'Activate' }}</button></form></td>
                        @endcan
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Vessel Types --}}
    <div class="bg-white rounded-lg shadow p-4">
        <div class="gcsm-head justify-between">
            <h3 class="font-semibold text-sm md:text-base">Vessel Types</h3>
            <span class="text-xs text-slate-400">{{ $vesselTypes->count() }} types</span>
        </div>
        @can('settings.edit')
        <form method="POST" action="{{ route('settings.vesseltypes.store') }}" class="flex flex-wrap gap-2 mb-3 text-sm">
            @csrf
            <input name="type_name" placeholder="New vessel type *" required class="border rounded px-2 py-1.5 flex-1 min-w-[160px]">
            <button class="bg-[#1F3864] text-white rounded px-4 py-1.5">Add</button>
        </form>
        @endcan
        <div class="max-h-[28rem] overflow-auto">
            <table class="w-full text-sm">
                <thead class="text-slate-400 text-left"><tr><th class="py-1">Vessel Type</th><th>Status</th>@can('settings.edit')<th></th>@endcan</tr></thead>
                <tbody>
                @foreach ($vesselTypes as $vt)
                    <tr class="border-t {{ $vt->active ? '' : 'opacity-50' }}">
                        @can('settings.edit')
                        <td class="py-1.5">
                            <form method="POST" action="{{ route('settings.vesseltypes.update', $vt) }}" class="flex gap-1">@csrf @method('PUT')
                                <input name="type_name" value="{{ $vt->type_name }}" class="border rounded px-2 py-1 text-sm flex-1">
                                <button class="text-xs text-[#2E74B5] hover:underline px-1">Save</button>
                            </form>
                        </td>
                        @else <td class="py-1.5">{{ $vt->type_name }}</td> @endcan
                        <td><span class="px-2 py-0.5 rounded text-xs {{ $vt->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $vt->active ? 'Active' : 'Off' }}</span></td>
                        @can('settings.edit')
                        <td class="text-right"><form method="POST" action="{{ route('settings.vesseltypes.toggle', $vt) }}">@csrf<button class="text-xs {{ $vt->active ? 'text-red-500' : 'text-emerald-600' }} hover:underline">{{ $vt->active ? 'Deactivate' : 'Activate' }}</button></form></td>
                        @endcan
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Maritime Education Departments --}}
    <div class="bg-white rounded-lg shadow p-4">
        <div class="gcsm-head justify-between">
            <h3 class="font-semibold text-sm md:text-base">Maritime Education Departments</h3>
            <span class="text-xs text-slate-400">{{ $departments->count() }} departments</span>
        </div>
        @can('settings.edit')
        <form method="POST" action="{{ route('settings.departments.store') }}" class="flex flex-wrap gap-2 mb-3 text-sm">
            @csrf
            <input name="name" placeholder="New department *" required class="border rounded px-2 py-1.5 flex-1 min-w-[160px]">
            <select name="category" class="border rounded px-2 py-1.5 w-36 no-enhance"><option value="Cadet Course">Cadet Course</option><option value="Rating Course">Rating Course</option></select>
            <button class="bg-[#1F3864] text-white rounded px-4 py-1.5">Add</button>
        </form>
        @endcan
        <div class="max-h-[28rem] overflow-auto">
            <table class="w-full text-sm">
                <thead class="text-slate-400 text-left"><tr><th class="py-1">Department</th><th>Course</th><th>Status</th>@can('settings.edit')<th></th>@endcan</tr></thead>
                <tbody>
                @foreach ($departments as $dep)
                    <tr class="border-t {{ $dep->active ? '' : 'opacity-50' }}">
                        @can('settings.edit')
                        <td class="py-1.5" colspan="2">
                            <form method="POST" action="{{ route('settings.departments.update', $dep) }}" class="flex gap-1">@csrf @method('PUT')
                                <input name="name" value="{{ $dep->name }}" class="border rounded px-2 py-1 text-sm flex-1">
                                <select name="category" class="border rounded px-2 py-1 text-sm w-32 no-enhance"><option value="Cadet Course" @selected($dep->category==='Cadet Course')>Cadet Course</option><option value="Rating Course" @selected($dep->category==='Rating Course')>Rating Course</option></select>
                                <button class="text-xs text-[#2E74B5] hover:underline px-1">Save</button>
                            </form>
                        </td>
                        @else <td class="py-1.5">{{ $dep->name }}</td><td>{{ $dep->category }}</td> @endcan
                        <td><span class="px-2 py-0.5 rounded text-xs {{ $dep->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $dep->active ? 'Active' : 'Off' }}</span></td>
                        @can('settings.edit')
                        <td class="text-right"><form method="POST" action="{{ route('settings.departments.toggle', $dep) }}">@csrf<button class="text-xs {{ $dep->active ? 'text-red-500' : 'text-emerald-600' }} hover:underline">{{ $dep->active ? 'Deactivate' : 'Activate' }}</button></form></td>
                        @endcan
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Crew Document Checklist template --}}
<div class="bg-white rounded-lg shadow p-4 mt-4">
    <div class="gcsm-head justify-between">
        <h3 class="font-semibold text-sm md:text-base">Crew Document Checklist</h3>
        <span class="text-xs text-slate-400">{{ $checklistItems->where('active', true)->count() }} active items</span>
    </div>
    <p class="text-xs text-slate-400 -mt-2 mb-3">These are the documents checked for every crew during selection. Changes here apply to every crew automatically. Items marked <span class="font-semibold text-emerald-600">Auto</span> are mapped from the crew profile and cannot be added or renamed.</p>

    @can('settings.edit')
    <form method="POST" action="{{ route('settings.checklist.store') }}" class="flex flex-wrap gap-2 mb-3 text-sm">
        @csrf
        <input name="label" placeholder="New checklist item (e.g. Medical Insurance) *" required class="border rounded px-2 py-1.5 flex-1 min-w-[220px]">
        <button class="bg-[#1F3864] text-white rounded px-4 py-1.5">+ Add item</button>
    </form>
    @endcan

    <div class="max-h-[32rem] overflow-auto">
        <table class="w-full text-sm">
            <thead class="text-slate-400 text-left"><tr><th class="py-1 w-10">SL</th><th>Checklist Item</th><th class="w-24">Mapping</th><th class="w-20">Status</th>@can('settings.edit')<th class="w-40 text-right">Actions</th>@endcan</tr></thead>
            <tbody>
            @foreach ($checklistItems as $ci)
                <tr class="border-t {{ $ci->active ? '' : 'opacity-50' }}">
                    <td class="py-1.5 text-slate-400">{{ $loop->iteration }}</td>
                    <td class="py-1.5">
                        @if ($ci->match_rule)
                            <span class="font-medium text-navy-800">{{ $ci->label }}</span>
                        @elseif (auth()->user()->can('settings.edit'))
                            <form method="POST" action="{{ route('settings.checklist.update', $ci) }}" class="flex gap-1">@csrf @method('PUT')
                                <input name="label" value="{{ $ci->label }}" class="border rounded px-2 py-1 text-sm flex-1">
                                <button class="text-xs text-[#2E74B5] hover:underline px-1">Save</button>
                            </form>
                        @else
                            {{ $ci->label }}
                        @endif
                    </td>
                    <td>
                        @if ($ci->match_rule)
                            <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700" title="Mapped from the crew profile">Auto</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600">Manual</span>
                        @endif
                    </td>
                    <td><span class="px-2 py-0.5 rounded text-xs {{ $ci->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $ci->active ? 'Active' : 'Off' }}</span></td>
                    @can('settings.edit')
                    <td class="text-right whitespace-nowrap">
                        <form method="POST" action="{{ route('settings.checklist.toggle', $ci) }}" class="inline">@csrf<button class="text-xs {{ $ci->active ? 'text-amber-600' : 'text-emerald-600' }} hover:underline mr-2">{{ $ci->active ? 'Deactivate' : 'Activate' }}</button></form>
                        <form method="POST" action="{{ route('settings.checklist.destroy', $ci) }}" class="inline" data-confirm="Remove “{{ $ci->label }}” from every crew's checklist?" data-confirm-title="Remove checklist item" data-confirm-ok="Remove" data-confirm-danger>@csrf @method('DELETE')<button class="text-xs text-red-500 hover:underline">Delete</button></form>
                    </td>
                    @endcan
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Sign-Off Reasons --}}
<div class="bg-white rounded-lg shadow p-4 mt-4">
    <div class="gcsm-head justify-between">
        <h3 class="font-semibold text-sm md:text-base">Crew Sign-Off Reasons</h3>
        <span class="text-xs text-slate-400">{{ $signOffReasons->where('active', true)->count() }} active reasons</span>
    </div>
    <p class="text-xs text-slate-400 -mt-2 mb-3">These reasons appear in the dropdown when a crew is signed off. Reasons marked <span class="font-semibold text-amber-600">Note</span> require a written note. “Voyage Completed Successfully” needs no note.</p>

    @can('settings.edit')
    <form method="POST" action="{{ route('settings.signoffreasons.store') }}" class="flex flex-wrap items-center gap-2 mb-3 text-sm">
        @csrf
        <input name="label" placeholder="New sign-off reason *" required class="border rounded px-2 py-1.5 flex-1 min-w-[220px]">
        <label class="inline-flex items-center gap-1 text-xs text-slate-600"><input type="checkbox" name="note_required" value="1" checked> requires note</label>
        <button class="bg-[#1F3864] text-white rounded px-4 py-1.5">+ Add reason</button>
    </form>
    @endcan

    <div class="max-h-[32rem] overflow-auto">
        <table class="w-full text-sm">
            <thead class="text-slate-400 text-left"><tr><th class="py-1 w-10">SL</th><th>Reason</th><th class="w-24">Note</th><th class="w-20">Status</th>@can('settings.edit')<th class="w-48 text-right">Actions</th>@endcan</tr></thead>
            <tbody>
            @foreach ($signOffReasons as $r)
                <tr class="border-t {{ $r->active ? '' : 'opacity-50' }}">
                    <td class="py-1.5 text-slate-400">{{ $loop->iteration }}</td>
                    <td class="py-1.5">
                        @if (auth()->user()->can('settings.edit'))
                            <form method="POST" action="{{ route('settings.signoffreasons.update', $r) }}" class="flex items-center gap-2">@csrf @method('PUT')
                                <input name="label" value="{{ $r->label }}" class="border rounded px-2 py-1 text-sm flex-1">
                                <label class="inline-flex items-center gap-1 text-[11px] text-slate-500 whitespace-nowrap"><input type="checkbox" name="note_required" value="1" @checked($r->note_required)> note</label>
                                <button class="text-xs text-[#2E74B5] hover:underline px-1">Save</button>
                            </form>
                        @else
                            {{ $r->label }}
                        @endif
                    </td>
                    <td><span class="px-2 py-0.5 rounded text-xs {{ $r->note_required ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">{{ $r->note_required ? 'Required' : 'Optional' }}</span></td>
                    <td><span class="px-2 py-0.5 rounded text-xs {{ $r->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $r->active ? 'Active' : 'Off' }}</span></td>
                    @can('settings.edit')
                    <td class="text-right whitespace-nowrap">
                        <form method="POST" action="{{ route('settings.signoffreasons.toggle', $r) }}" class="inline">@csrf<button class="text-xs {{ $r->active ? 'text-amber-600' : 'text-emerald-600' }} hover:underline mr-2">{{ $r->active ? 'Deactivate' : 'Activate' }}</button></form>
                        <form method="POST" action="{{ route('settings.signoffreasons.destroy', $r) }}" class="inline" data-confirm="Remove “{{ $r->label }}” from the sign-off reason list?" data-confirm-title="Remove sign-off reason" data-confirm-ok="Remove" data-confirm-danger>@csrf @method('DELETE')<button class="text-xs text-red-500 hover:underline">Delete</button></form>
                    </td>
                    @endcan
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<p class="text-xs text-slate-400 mt-4">Ranks, marine academies and education departments added here sync into every matching dropdown across the software (crew profiles, maritime education, etc.). Deactivated items stay on existing records but are hidden from new dropdowns.</p>
@endsection
