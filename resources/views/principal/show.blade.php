@extends('layouts.app')
@section('title', $principal->name)
@section('actions')
    <a href="{{ route('salary.index', ['principal_id'=>$principal->id]) }}" class="border px-3 py-1.5 rounded text-sm mr-1">Salary sheets</a>
    <a href="{{ route('principal.crewexport', ['principal'=>$principal->id,'export'=>'pdf']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">Crew PDF</a>
    <a href="{{ route('principal.crewexport', ['principal'=>$principal->id,'export'=>'excel']) }}" class="border px-3 py-1.5 rounded text-sm mr-1">Crew Excel</a>
    @can('principal.edit')<a href="{{ route('principal.edit', $principal) }}" class="bg-[#1F3864] text-white px-3 py-1.5 rounded text-sm">Edit</a>@endcan
@endsection
@section('content')

@unless ($principal->hasContract())
    <div class="mb-4 rounded bg-amber-100 border border-amber-300 text-amber-800 px-4 py-2 text-sm">No contract on file — upload a signed contract below to activate this company (PM-02).</div>
@endunless

<div class="grid md:grid-cols-3 gap-4 mb-4">
    <div class="bg-white rounded-lg shadow p-4 md:col-span-2">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xl font-semibold text-[#1F3864]">{{ $principal->name }}</div>
                <div class="text-sm text-slate-500 capitalize">{{ $principal->type }} company · {{ $principal->country }}</div>
            </div>
            @can('principal.edit')
            <form method="POST" action="{{ route('principal.activate', $principal) }}">@csrf
                <button class="px-3 py-1.5 rounded text-sm {{ $principal->status==='active' ? 'bg-emerald-600 text-white' : 'bg-slate-200' }}">
                    {{ $principal->status==='active' ? 'Active' : 'Activate' }}
                </button>
            </form>
            @endcan
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-y-2 gap-x-4 text-sm mt-4">
            <div><span class="text-slate-400">Phone:</span> {{ $principal->phone ?: '—' }}</div>
            <div><span class="text-slate-400">Email:</span> {{ $principal->email ?: '—' }}</div>
            <div><span class="text-slate-400">Website:</span> {{ $principal->website ?: '—' }}</div>
            <div class="col-span-2 md:col-span-3"><span class="text-slate-400">Address:</span> {{ $principal->address ?: '—' }}</div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-sm">
        <div class="text-slate-400 mb-1">Managing staff</div>
        <div class="text-lg font-semibold text-[#1F3864]">{{ optional($principal->assignedStaff)->name ?: 'Unassigned' }}</div>
        @can('principal.edit')
        <form method="POST" action="{{ route('principal.assign', $principal) }}" class="mt-3 space-y-1">
            @csrf
            <select name="staff_id" required class="w-full border rounded px-2 py-1"><option value="">Assign staff…</option>
                @foreach ($staff as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select>
            <input name="reason" placeholder="Reason for change" class="w-full border rounded px-2 py-1">
            <button class="bg-[#1F3864] text-white rounded px-3 py-1 w-full text-sm">Assign / reassign</button>
        </form>
        @endcan
    </div>
</div>

<div class="grid md:grid-cols-2 gap-4 mb-4">
    {{-- Contacts --}}
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-slate-700 mb-2">Contacts</h3>
        @forelse ($principal->contacts as $c)
            <div class="text-sm border-t py-2 flex justify-between">
                <div>{{ $c->name }} @if($c->is_primary)<span class="text-xs bg-blue-100 text-blue-700 px-1 rounded">primary</span>@endif<br>
                    <span class="text-slate-400 text-xs">{{ $c->designation }} · {{ $c->phone }} · {{ $c->email }}</span></div>
                @can('principal.edit')<form method="POST" action="{{ route('principal.contacts.destroy', [$principal,$c]) }}" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button class="text-red-500 text-xs">×</button></form>@endcan
            </div>
        @empty <div class="text-slate-400 text-sm">No contacts.</div> @endforelse
        @can('principal.edit')
        <form method="POST" action="{{ route('principal.contacts.store', $principal) }}" class="mt-2 grid grid-cols-2 gap-1 text-sm border-t pt-2">
            @csrf
            <input name="name" placeholder="Name *" required class="border rounded px-2 py-1">
            <input name="designation" placeholder="Designation" class="border rounded px-2 py-1">
            <input name="phone" placeholder="Phone" class="border rounded px-2 py-1">
            <input name="email" placeholder="Email" class="border rounded px-2 py-1">
            <label class="flex items-center text-xs col-span-2"><input type="checkbox" name="is_primary" value="1" class="mr-1">Primary contact</label>
            <button class="bg-[#1F3864] text-white rounded px-3 py-1 col-span-2">Add contact</button>
        </form>
        @endcan
    </div>

    {{-- Vessels --}}
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-slate-700 mb-2">Vessels</h3>
        @forelse ($principal->vessels as $v)
            <div class="text-sm border-t py-2 flex justify-between">
                <div>{{ $v->vessel_name }} <span class="text-slate-400 text-xs">{{ $v->vessel_type }} · IMO {{ $v->imo }} · {{ $v->flag }}</span></div>
                @can('principal.edit')<form method="POST" action="{{ route('principal.vessels.destroy', [$principal,$v]) }}" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button class="text-red-500 text-xs">×</button></form>@endcan
            </div>
        @empty <div class="text-slate-400 text-sm">No vessels.</div> @endforelse
        @can('principal.edit')
        <form method="POST" action="{{ route('principal.vessels.store', $principal) }}" class="mt-2 grid grid-cols-2 gap-1 text-sm border-t pt-2">
            @csrf
            <input name="vessel_name" placeholder="Vessel name *" required class="border rounded px-2 py-1">
            <input name="vessel_type" placeholder="Type" class="border rounded px-2 py-1">
            <input name="imo" placeholder="IMO" class="border rounded px-2 py-1">
            <input name="flag" placeholder="Flag" class="border rounded px-2 py-1">
            <button class="bg-[#1F3864] text-white rounded px-3 py-1 col-span-2">Add vessel</button>
        </form>
        @endcan
    </div>
</div>

{{-- Documents / contract --}}
<div class="bg-white rounded-lg shadow p-4 mb-4">
    <h3 class="font-semibold text-slate-700 mb-2">Documents & contract</h3>
    <table class="w-full text-sm mb-3">
        <thead class="text-slate-400 text-left"><tr><th class="py-1">Type</th><th>Title</th><th>Signed</th><th>File</th><th></th></tr></thead>
        <tbody>
        @forelse ($principal->documents as $d)
            <tr class="border-t"><td class="py-1.5"><span class="px-2 py-0.5 rounded text-xs {{ $d->doc_type==='contract' ? 'bg-blue-100 text-blue-700':'bg-slate-100 text-slate-600' }}">{{ ucfirst($d->doc_type) }}</span></td>
                <td>{{ $d->title }}</td><td>{{ optional($d->signed_date)->toDateString() }}</td>
                <td><a href="{{ asset('storage/'.$d->file_path) }}" target="_blank" class="text-[#2E74B5] hover:underline">Open</a></td>
                <td class="text-right">@can('principal.edit')<form method="POST" action="{{ route('principal.documents.destroy', [$principal,$d]) }}" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button class="text-red-500 text-xs">Remove</button></form>@endcan</td></tr>
        @empty <tr><td colspan="5" class="py-3 text-slate-400">No documents.</td></tr> @endforelse
        </tbody>
    </table>
    @can('principal.edit')
    <form method="POST" action="{{ route('principal.documents.store', $principal) }}" enctype="multipart/form-data" class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm items-end border-t pt-3">
        @csrf
        <select name="doc_type" class="border rounded px-2 py-1"><option value="contract">Contract</option><option value="other">Other</option></select>
        <input name="title" placeholder="Title *" required class="border rounded px-2 py-1">
        <input type="date" name="signed_date" class="border rounded px-2 py-1">
        <input type="file" name="file" required class="text-xs">
        <button class="bg-[#1F3864] text-white rounded px-3 py-1">Upload</button>
    </form>
    @endcan
</div>

{{-- Crew placed --}}
<div id="placements" class="bg-white rounded-lg shadow p-4 mb-4">
    <h3 class="font-semibold text-slate-700 mb-2">Crew currently onboard ({{ $onboard->count() }})</h3>
    <table class="w-full text-sm mb-3">
        <thead class="text-slate-400 text-left"><tr><th class="py-1">Crew</th><th>Rank</th><th>Vessel</th><th>Sign-on</th><th>Tenure</th><th></th></tr></thead>
        <tbody>
        @forelse ($onboard as $pl)
            <tr class="border-t">
                <td class="py-1.5"><a href="{{ route('crew.show', $pl->crew_profile_id) }}" class="text-[#2E74B5] hover:underline">{{ optional($pl->crewProfile)->name }}</a></td>
                <td>{{ $pl->rank }}</td><td>{{ optional($pl->vessel)->vessel_name }}</td>
                <td>{{ optional($pl->sign_on_date)->toDateString() }}</td>
                <td>{{ $pl->sign_on_date ? $pl->sign_on_date->diffForHumans(null, true) : '—' }}</td>
                <td class="text-right">@can('principal.edit')
                    <form method="POST" action="{{ route('principal.placements.signoff', [$principal,$pl]) }}" class="flex flex-wrap gap-1 justify-end items-center">@csrf
                        <input type="date" name="sign_off_date" required title="Sign-off date" class="border rounded px-1 py-0.5 text-xs">
                        <input type="date" name="available_from" title="Available from (future = Resting)" class="border rounded px-1 py-0.5 text-xs">
                        <select name="job_urgency" title="Urgency after resting" class="border rounded px-1 py-0.5 text-xs"><option value="normal">Normal</option><option value="high">High</option><option value="urgent">Urgent</option></select>
                        <input type="date" name="job_deadline" title="Placement deadline (High/Urgent)" class="border rounded px-1 py-0.5 text-xs">
                        <label class="text-xs"><input type="checkbox" name="has_dues" value="1"> dues</label>
                        <button class="text-xs bg-slate-700 text-white rounded px-2 py-0.5 font-semibold hover:bg-slate-800">Sign off</button>
                    </form>@endcan</td>
            </tr>
        @empty <tr><td colspan="6" class="py-3 text-slate-400">No crew onboard.</td></tr> @endforelse
        </tbody>
    </table>
    @can('principal.edit')
    <form method="POST" action="{{ route('principal.placements.store', $principal) }}" class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm items-end border-t pt-3">
        @csrf
        <input name="crew_profile_id" placeholder="Crew ID (admission's DB id) *" required class="border rounded px-2 py-1" title="Enter the crew's numeric ID; the Selection module will automate this in a later phase.">
        <select name="principal_vessel_id" class="border rounded px-2 py-1"><option value="">Vessel…</option>
            @foreach ($principal->vessels as $v)<option value="{{ $v->id }}">{{ $v->vessel_name }}</option>@endforeach</select>
        <input name="rank" placeholder="Rank" class="border rounded px-2 py-1">
        <input type="date" name="sign_on_date" class="border rounded px-2 py-1">
        <button class="bg-[#1F3864] text-white rounded px-3 py-1">Place crew</button>
    </form>
    <p class="text-xs text-slate-400 mt-2">Note: crew placement is entered manually here for now; Module 2 (Crew Selection) will create placements automatically from the selection pipeline.</p>
    @endcan
</div>

{{-- Past crew + assignment history --}}
<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-slate-700 mb-2">Past crew ({{ $past->count() }})</h3>
        <table class="w-full text-sm">
            <thead class="text-slate-400 text-left"><tr><th class="py-1">Crew</th><th>Rank</th><th>Vessel</th><th>Off</th></tr></thead>
            <tbody>
            @forelse ($past as $pl)
                <tr class="border-t"><td class="py-1.5"><a href="{{ route('crew.show', $pl->crew_profile_id) }}" class="text-[#2E74B5] hover:underline">{{ optional($pl->crewProfile)->name }}</a></td>
                    <td>{{ $pl->rank }}</td><td>{{ optional($pl->vessel)->vessel_name }}</td><td>{{ optional($pl->sign_off_date)->toDateString() }}</td></tr>
            @empty <tr><td colspan="4" class="py-3 text-slate-400">None.</td></tr> @endforelse
            </tbody>
        </table>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-slate-700 mb-2">Managing-staff history</h3>
        <table class="w-full text-sm">
            <thead class="text-slate-400 text-left"><tr><th class="py-1">Staff</th><th>From</th><th>To</th><th>Reason</th></tr></thead>
            <tbody>
            @forelse ($principal->assignments as $a)
                <tr class="border-t"><td class="py-1.5">{{ optional($a->staff)->name }}</td>
                    <td>{{ optional($a->assigned_at)->toDateString() }}</td>
                    <td>{{ $a->unassigned_at ? $a->unassigned_at->toDateString() : 'current' }}</td>
                    <td class="text-slate-500">{{ $a->reason }}</td></tr>
            @empty <tr><td colspan="4" class="py-3 text-slate-400">No history.</td></tr> @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
