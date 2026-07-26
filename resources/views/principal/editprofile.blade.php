@extends('layouts.app')
@section('title', 'Edit — '.$principal->name)
@section('actions')
    <a href="{{ route('principal.show', $principal) }}" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition mr-1">← View profile</a>
    <a href="{{ route('principal.edit', $principal) }}" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-sm px-4 py-1.5 hover:bg-[#2E74B5] transition">✎ Edit company info</a>
@endsection
@section('content')

@php $currentManagers = $principal->assignments->whereNull('unassigned_at'); @endphp

@if ($principal->offences->isNotEmpty())
    <div class="mb-4 rounded bg-amber-100 border border-amber-300 text-amber-800 px-4 py-2 text-sm">⚠ This company has {{ $principal->offences->count() }} offence record(s). Review before selection.</div>
@endif

{{-- Header --}}
<div class="bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-start gap-5">
        @if ($principal->logo_path)
            <img src="{{ asset('storage/'.$principal->logo_path) }}" alt="logo" class="w-20 h-20 rounded-xl object-contain ring-1 ring-slate-200 bg-white shrink-0">
        @else
            <div class="w-20 h-20 rounded-xl bg-[#1F3864] text-white flex items-center justify-center text-2xl font-bold shrink-0">{{ strtoupper(mb_substr($principal->name,0,2)) }}</div>
        @endif
        <div class="flex-1 min-w-0">
            <div class="text-2xl font-bold text-navy-800 tracking-tight leading-tight">{{ $principal->name }} <span class="text-xs text-amber-600 font-semibold align-middle">· Editing</span></div>
            <div class="text-sm text-slate-500 mt-1 capitalize">{{ $principal->type }} company @if($principal->country)<span class="text-slate-300">·</span> {{ $principal->country }}@endif</div>
        </div>
        <div class="ml-auto shrink-0">
            <form method="POST" action="{{ route('principal.activate', $principal) }}">@csrf
                <button class="inline-flex items-center gap-1 rounded-md border font-semibold text-sm px-3 py-1.5 transition {{ $principal->status==='active' ? 'border-emerald-300 text-emerald-700 hover:bg-emerald-50' : 'border-slate-300 text-slate-700 hover:bg-slate-100' }}">{{ $principal->status==='active' ? '● Active — deactivate' : '○ Activate' }}</button>
            </form>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="sticky top-[57px] z-30 bg-white rounded-lg shadow-sm border border-slate-100 px-2 py-2 mb-4">
    <div class="js-tabs flex flex-wrap gap-1">
        <button type="button" class="ptab" data-tab="contacts">Contacts</button>
        <button type="button" class="ptab" data-tab="vessels">Vessels</button>
        <button type="button" class="ptab" data-tab="docs">Document &amp; Contract</button>
        <button type="button" class="ptab" data-tab="onboard">Crew On Board</button>
        <button type="button" class="ptab" data-tab="past">Past Crew</button>
        <button type="button" class="ptab" data-tab="staff">Managing Staff / Partner</button>
        <button type="button" class="ptab" data-tab="offence">Offences</button>
        <button type="button" class="ptab" data-tab="notes">Notes</button>
        <button type="button" class="ptab" data-tab="editlog">Edit Log</button>
    </div>
</div>

{{-- Contacts --}}
<div data-panel="contacts" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-navy-800">Contacts ({{ $principal->contacts->count() }})</h3>
        <button type="button" onclick="gcsmToggleForm('addContactForm')" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">+ Add contact</button>
    </div>
    @forelse ($principal->contacts as $c)
        <div class="border border-slate-100 rounded-xl p-4 mb-3 flex items-start gap-4">
            @if ($c->photo_path)
                <img src="{{ asset('storage/'.$c->photo_path) }}" alt="" class="w-14 h-14 rounded-full object-cover ring-1 ring-slate-200 shrink-0">
            @else
                <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-lg shrink-0">{{ strtoupper(mb_substr($c->name,0,1)) }}</div>
            @endif
            <div class="flex-1 min-w-0 text-sm">
                <div class="font-semibold text-navy-800">{{ $c->name }} @if($c->is_primary)<span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full">Primary</span>@endif</div>
                <div class="text-xs text-slate-500">{{ $c->designation }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ collect([$c->phone, $c->email, $c->whatsapp ? 'WA: '.$c->whatsapp : null, $c->wechat_id ? 'WeChat: '.$c->wechat_id : null])->filter()->implode(' · ') }}</div>
            </div>
            <form method="POST" action="{{ route('principal.contacts.destroy', [$principal,$c]) }}" onsubmit="return confirm('Remove this contact?')">@csrf @method('DELETE')<button class="text-red-500 text-xs font-semibold hover:underline">Remove</button></form>
        </div>
    @empty <div class="text-slate-400 text-sm mb-2">No contacts yet. Click <span class="font-semibold">+ Add contact</span> to add one.</div> @endforelse

    <div id="addContactForm" class="{{ $errors->hasAny(['name','designation','email']) ? '' : 'hidden' }} border-t pt-4 mt-2">
        <form method="POST" action="{{ route('principal.contacts.store', $principal) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            @csrf
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Name *</label><input name="name" required class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Designation</label><input name="designation" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Phone</label><input name="phone" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Email</label><input name="email" type="email" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">WhatsApp number</label><input name="whatsapp" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">WeChat ID</label><input name="wechat_id" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">LinkedIn profile</label><input name="linkedin" placeholder="https://" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Facebook profile</label><input name="facebook" placeholder="https://" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div class="md:col-span-2"><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Office address</label><textarea name="office_address" rows="2" class="w-full border border-slate-300 rounded-md px-3 py-2"></textarea></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Photo</label><input type="file" name="photo" accept="image/*" class="w-full text-xs"></div>
            <label class="flex items-center gap-2 text-sm mt-6"><input type="checkbox" name="is_primary" value="1"> Primary contact</label>
            <div class="md:col-span-2 flex gap-2"><button class="bg-[#1F3864] text-white font-semibold px-5 py-2 rounded-md hover:bg-[#2E74B5]">Save contact</button><button type="button" onclick="gcsmToggleForm('addContactForm')" class="border border-slate-300 text-slate-700 font-semibold px-5 py-2 rounded-md hover:bg-slate-100">Cancel</button></div>
        </form>
    </div>
</div>

{{-- Vessels --}}
<div data-panel="vessels" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-navy-800">Vessels ({{ $principal->vessels->count() }})</h3>
        <button type="button" onclick="gcsmToggleForm('addVesselForm')" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">+ Add vessel</button>
    </div>
    <table class="w-full text-sm mb-3">
        <thead class="text-slate-400 text-left border-b"><tr><th class="py-2">Name</th><th>IMO</th><th>Type</th><th>GRT</th><th>DWT</th><th>Engine</th><th>BHP</th><th>Flag</th><th>Trading Area</th><th></th></tr></thead>
        <tbody>
        @forelse ($principal->vessels as $v)
            <tr class="border-t">
                <td class="py-2 font-medium text-navy-800">{{ $v->vessel_name }}</td>
                <td>{{ $v->imo ?: '—' }}</td><td>{{ $v->vessel_type ?: '—' }}</td><td>{{ $v->grt ?: '—' }}</td><td>{{ $v->dwt ?: '—' }}</td>
                <td>{{ $v->engine_type ?: '—' }}</td><td>{{ $v->bhp ?: '—' }}</td><td>{{ $v->flag ?: '—' }}</td><td>{{ $v->trading_area ?: '—' }}</td>
                <td class="text-right"><form method="POST" action="{{ route('principal.vessels.destroy', [$principal,$v]) }}" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button class="text-red-500 text-xs font-semibold hover:underline">Remove</button></form></td>
            </tr>
        @empty <tr><td colspan="10" class="py-3 text-slate-400">No vessels yet. Click <span class="font-semibold">+ Add vessel</span> to add one.</td></tr> @endforelse
        </tbody>
    </table>
    <div id="addVesselForm" class="{{ $errors->has('vessel_name') ? '' : 'hidden' }} border-t pt-4">
        <form method="POST" action="{{ route('principal.vessels.store', $principal) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            @csrf
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Name of Vessel *</label><input name="vessel_name" required class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Ship IMO</label><input name="imo" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Vessel Type</label>
                <select name="vessel_type" data-placeholder="Search vessel type…" class="w-full border border-slate-300 rounded-md px-3 py-2">
                    <option value="">Select type…</option>
                    @foreach ($vesselTypes as $vt)<option value="{{ $vt->type_name }}">{{ $vt->type_name }}</option>@endforeach
                </select>
            </div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">GRT</label><input name="grt" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">DWT</label><input name="dwt" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Engine Type</label><input name="engine_type" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">BHP</label><input name="bhp" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Flag</label><input name="flag" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div class="md:col-span-2"><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Trading Area</label><input name="trading_area" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div class="md:col-span-3 flex gap-2"><button class="bg-[#1F3864] text-white font-semibold px-5 py-2 rounded-md hover:bg-[#2E74B5]">Save vessel</button><button type="button" onclick="gcsmToggleForm('addVesselForm')" class="border border-slate-300 text-slate-700 font-semibold px-5 py-2 rounded-md hover:bg-slate-100">Cancel</button></div>
        </form>
    </div>
</div>

{{-- Documents & Contract --}}
<div data-panel="docs" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-navy-800">Document &amp; Contract ({{ $principal->documents->count() }})</h3>
        <button type="button" onclick="gcsmToggleForm('addDocForm')" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">+ Add document</button>
    </div>
    <table class="w-full text-sm mb-3">
        <thead class="text-slate-400 text-left border-b"><tr><th class="py-2">Type</th><th>Title</th><th>Signed</th><th>File</th><th></th></tr></thead>
        <tbody>
        @forelse ($principal->documents as $d)
            <tr class="border-t">
                <td class="py-2"><span class="px-2 py-0.5 rounded text-xs {{ $d->doc_type==='contract' ? 'bg-blue-100 text-blue-700':'bg-slate-100 text-slate-600' }}">{{ ucfirst($d->doc_type) }}</span></td>
                <td>{{ $d->title }}</td>
                <td>{{ optional($d->signed_date)->toDateString() ?: '—' }}</td>
                <td>
                    <span class="inline-flex items-center gap-1.5">
                        <a href="{{ asset('storage/'.$d->file_path) }}" target="_blank" rel="noopener" class="text-xs font-semibold rounded-md border border-slate-300 text-slate-700 px-2.5 py-1 hover:bg-slate-100">View</a>
                        <a href="{{ asset('storage/'.$d->file_path) }}" download class="text-xs font-semibold rounded-md bg-[#1F3864] text-white px-2.5 py-1 hover:bg-[#2E4A7A]">Download</a>
                    </span>
                </td>
                <td class="text-right"><form method="POST" action="{{ route('principal.documents.destroy', [$principal,$d]) }}" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button class="text-red-500 text-xs font-semibold hover:underline">Remove</button></form></td>
            </tr>
        @empty <tr><td colspan="5" class="py-3 text-slate-400">No documents yet. Click <span class="font-semibold">+ Add document</span> to upload one.</td></tr> @endforelse
        </tbody>
    </table>
    <div id="addDocForm" class="{{ $errors->hasAny(['title','file']) ? '' : 'hidden' }} border-t pt-4">
        <form method="POST" action="{{ route('principal.documents.store', $principal) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-5 gap-3 text-sm items-end">
            @csrf
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Type</label>
                <select name="doc_type" class="w-full border border-slate-300 rounded-md px-3 py-2 no-enhance"><option value="contract">Contract</option><option value="other">Other</option></select>
            </div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Title *</label><input name="title" required class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Signed date</label><input type="date" name="signed_date" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">File *</label><input type="file" name="file" required class="w-full text-xs"></div>
            <div class="flex gap-2"><button class="bg-[#1F3864] text-white font-semibold px-5 py-2 rounded-md hover:bg-[#2E74B5] w-full">Upload</button></div>
        </form>
    </div>
</div>

{{-- Crew On Board (with sign-off) --}}
<div data-panel="onboard" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <h3 class="font-semibold text-navy-800 mb-4">Crew On Board ({{ $onboard->count() }})</h3>
    <p class="text-xs text-slate-400 mb-3">Crew are placed automatically from the Crew Selection module. Signing a crew off here updates their sea-service record and availability everywhere in the system.</p>
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left border-b"><tr><th class="py-2">Crew</th><th>Rank</th><th>Vessel</th><th>Sign-on</th><th class="text-right">Sign off</th></tr></thead>
        <tbody>
        @forelse ($onboard as $pl)
            <tr class="border-t align-top">
                <td class="py-2"><a href="{{ route('crew.show', $pl->crew_profile_id) }}" class="text-[#2E74B5] hover:underline">{{ optional($pl->crewProfile)->name }}</a></td>
                <td>{{ $pl->rank }}</td>
                <td>{{ optional($pl->vessel)->vessel_name }}</td>
                <td>{{ optional($pl->sign_on_date)->toDateString() }}</td>
                <td class="text-right">
                    <form method="POST" action="{{ route('principal.placements.signoff', [$principal,$pl]) }}" class="flex flex-wrap gap-1 justify-end items-center">@csrf
                        <input type="date" name="sign_off_date" required title="Sign-off date" class="border rounded px-1 py-0.5 text-xs">
                        <input type="date" name="available_from" title="Available from (future = Resting)" class="border rounded px-1 py-0.5 text-xs">
                        <select name="job_urgency" title="Urgency after resting" class="border rounded px-1 py-0.5 text-xs no-enhance"><option value="normal">Normal</option><option value="high">High</option><option value="urgent">Urgent</option></select>
                        <input type="date" name="job_deadline" title="Placement deadline" class="border rounded px-1 py-0.5 text-xs">
                        <label class="text-xs flex items-center gap-1"><input type="checkbox" name="has_dues" value="1"> dues</label>
                        <button class="text-xs bg-[#1F3864] text-white rounded px-2 py-0.5 font-semibold hover:bg-[#2E74B5]">Sign off</button>
                    </form>
                </td>
            </tr>
        @empty <tr><td colspan="5" class="py-4 text-slate-400">No crew onboard.</td></tr> @endforelse
        </tbody>
    </table>
</div>

{{-- Past Crew --}}
<div data-panel="past" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <h3 class="font-semibold text-navy-800 mb-4">Past Crew ({{ $past->count() }})</h3>
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left border-b"><tr><th class="py-2">Crew</th><th>Rank</th><th>Vessel</th><th>Sign-off</th></tr></thead>
        <tbody>
        @forelse ($past as $pl)
            <tr class="border-t">
                <td class="py-2"><a href="{{ route('crew.show', $pl->crew_profile_id) }}" class="text-[#2E74B5] hover:underline">{{ optional($pl->crewProfile)->name }}</a></td>
                <td>{{ $pl->rank }}</td><td>{{ optional($pl->vessel)->vessel_name }}</td><td>{{ optional($pl->sign_off_date)->toDateString() }}</td>
            </tr>
        @empty <tr><td colspan="4" class="py-4 text-slate-400">No past crew records.</td></tr> @endforelse
        </tbody>
    </table>
</div>

{{-- Managing Staff (multiple) --}}
<div data-panel="staff" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-navy-800">Managing Staff / Partner ({{ $currentManagers->count() }})</h3>
        <button type="button" onclick="gcsmToggleForm('addStaffForm')" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">+ Add staff / partner</button>
    </div>

    {{-- Current managers --}}
    @forelse ($currentManagers as $a)
        @php $isPartner = (optional($a->staff)->user_type ?? null) === 'partner'; @endphp
        <div class="border rounded-xl p-3 mb-2 flex items-center gap-3 text-sm {{ $isPartner ? 'border-amber-200 bg-amber-50/40' : 'border-blue-200 bg-blue-50/40' }}">
            <span class="w-9 h-9 rounded-full text-white flex items-center justify-center text-xs font-semibold shrink-0 {{ $isPartner ? 'bg-[#C9A227]' : 'bg-[#1F3864]' }}">{{ strtoupper(mb_substr(optional($a->staff)->name ?: '?',0,1)) }}</span>
            <div class="flex-1 min-w-0">
                <div class="font-semibold text-navy-800">{{ optional($a->staff)->name ?: '—' }} <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-semibold {{ $isPartner ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-700' }}">{{ $isPartner ? 'Partner' : 'Staff' }}</span></div>
                <div class="text-[11px] text-slate-400">Since {{ optional($a->assigned_at)->toDateString() }}@if($a->reason) · {{ $a->reason }}@endif</div>
            </div>
            <form method="POST" action="{{ route('principal.staff.remove', [$principal,$a]) }}" onsubmit="return confirm('Remove this staff / partner?')">@csrf @method('DELETE')<button class="text-red-500 text-xs font-semibold hover:underline">Remove</button></form>
        </div>
    @empty <div class="text-slate-400 text-sm mb-2">No managing staff or partner assigned. Click <span class="font-semibold">+ Add staff / partner</span> to assign.</div> @endforelse

    <div id="addStaffForm" class="{{ $errors->has('staff_id') ? '' : 'hidden' }} border-t pt-4 mt-2">
        <form method="POST" action="{{ route('principal.assign', $principal) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            @csrf
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Staff / Partner *</label>
                <select name="staff_id" required data-placeholder="Select staff / partner…" class="w-full border border-slate-300 rounded-md px-3 py-2">
                    <option value="">Select staff / partner…</option>
                    @foreach ($staff as $u)<option value="{{ $u->id }}">{{ $u->name }}{{ ($u->user_type ?? null) === 'partner' ? ' (Partner)' : ' (Staff)' }}</option>@endforeach
                </select>
            </div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Reason / note</label><input name="reason" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div class="flex items-end gap-2"><button class="bg-[#1F3864] text-white font-semibold px-5 py-2 rounded-md hover:bg-[#2E74B5]">Add staff</button><button type="button" onclick="gcsmToggleForm('addStaffForm')" class="border border-slate-300 text-slate-700 font-semibold px-5 py-2 rounded-md hover:bg-slate-100">Cancel</button></div>
        </form>
    </div>

    {{-- Assignment history --}}
    <h4 class="font-semibold text-navy-800 text-sm mt-6 mb-2">Assignment history</h4>
    <table class="w-full text-sm">
        <thead class="text-slate-400 text-left border-b"><tr><th class="py-2">Staff</th><th>From</th><th>To</th><th>Reason</th></tr></thead>
        <tbody>
        @forelse ($principal->assignments as $a)
            <tr class="border-t">
                <td class="py-2">{{ optional($a->staff)->name }}</td>
                <td>{{ optional($a->assigned_at)->toDateString() }}</td>
                <td>{{ $a->unassigned_at ? $a->unassigned_at->toDateString() : 'current' }}</td>
                <td class="text-slate-500">{{ $a->reason }}</td>
            </tr>
        @empty <tr><td colspan="4" class="py-4 text-slate-400">No history.</td></tr> @endforelse
        </tbody>
    </table>
</div>

{{-- Offences --}}
<div data-panel="offence" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-navy-800">Offence / Incident Records ({{ $principal->offences->count() }})</h3>
        <button type="button" onclick="gcsmToggleForm('addOffenceForm')" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">+ Add offence</button>
    </div>
    @forelse ($principal->offences as $o)
        <div class="border-t py-3 text-sm flex items-start justify-between gap-3">
            <div>
                <div class="font-semibold text-navy-800">{{ optional($o->offence_date)->toDateString() ?: 'Undated' }} @if($o->action_taken)<span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800">{{ $o->action_taken }}</span>@endif</div>
                <div class="text-slate-700 mt-1">{{ $o->description }}</div>
                <div class="text-[11px] text-slate-400 mt-1">@if($o->source)Source: {{ $o->source }} · @endif recorded by {{ optional($o->recordedBy)->name ?: 'System' }} · {{ $o->created_at->diffForHumans() }}</div>
            </div>
            <form method="POST" action="{{ route('principal.offences.destroy', [$principal,$o]) }}" onsubmit="return confirm('Remove this record?')">@csrf @method('DELETE')<button class="text-red-500 text-xs font-semibold hover:underline">Remove</button></form>
        </div>
    @empty <div class="text-slate-400 text-sm mb-2">No offence records. Click <span class="font-semibold">+ Add offence</span> to record one.</div> @endforelse

    <div id="addOffenceForm" class="{{ $errors->has('description') ? '' : 'hidden' }} border-t pt-4 mt-2">
        <form method="POST" action="{{ route('principal.offences.store', $principal) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            @csrf
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Date</label><input type="date" name="offence_date" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Source</label><input name="source" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Action taken</label><input name="action_taken" class="w-full border border-slate-300 rounded-md px-3 py-2"></div>
            <div class="md:col-span-3"><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Description *</label><textarea name="description" rows="2" required class="w-full border border-slate-300 rounded-md px-3 py-2"></textarea></div>
            <div class="md:col-span-3 flex gap-2"><button class="bg-[#1F3864] text-white font-semibold px-5 py-2 rounded-md hover:bg-[#2E74B5]">Save record</button><button type="button" onclick="gcsmToggleForm('addOffenceForm')" class="border border-slate-300 text-slate-700 font-semibold px-5 py-2 rounded-md hover:bg-slate-100">Cancel</button></div>
        </form>
    </div>
</div>

{{-- Notes --}}
<div data-panel="notes" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-navy-800">Notes ({{ $principal->companyNotes->count() }})</h3>
        <button type="button" onclick="gcsmToggleForm('addNoteForm')" class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">+ Add note</button>
    </div>
    @forelse ($principal->companyNotes as $n)
        <div class="border-t py-3 text-sm flex items-start justify-between gap-3">
            <div>
                <div class="text-slate-700 whitespace-pre-line">{{ $n->note }}</div>
                <div class="text-[11px] text-slate-400 mt-1">{{ optional($n->author)->name ?: 'System' }} · {{ $n->created_at->diffForHumans() }}</div>
            </div>
            <form method="POST" action="{{ route('principal.notes.destroy', [$principal,$n]) }}" onsubmit="return confirm('Remove this note?')">@csrf @method('DELETE')<button class="text-red-500 text-xs font-semibold hover:underline">Remove</button></form>
        </div>
    @empty <div class="text-slate-400 text-sm mb-2">No notes yet. Click <span class="font-semibold">+ Add note</span> to add one.</div> @endforelse

    <div id="addNoteForm" class="{{ $errors->has('note') ? '' : 'hidden' }} border-t pt-4 mt-2">
        <form method="POST" action="{{ route('principal.notes.store', $principal) }}" class="flex flex-col gap-3 text-sm">
            @csrf
            <div><label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">New note *</label><textarea name="note" rows="3" required class="w-full border border-slate-300 rounded-md px-3 py-2"></textarea></div>
            <div class="flex gap-2"><button class="bg-[#1F3864] text-white font-semibold px-5 py-2 rounded-md hover:bg-[#2E74B5]">Save note</button><button type="button" onclick="gcsmToggleForm('addNoteForm')" class="border border-slate-300 text-slate-700 font-semibold px-5 py-2 rounded-md hover:bg-slate-100">Cancel</button></div>
        </form>
    </div>
</div>

{{-- Edit Log --}}
<div data-panel="editlog" class="tab-panel hidden bg-white rounded-lg shadow p-5 mb-4">
    @include('principal.partials.edit_log')
</div>

<script>
(function () {
    var KEY = 'gcsmPrincipalEditTab:{{ $principal->id }}';
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.js-tabs .ptab'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('.tab-panel[data-panel]'));

    function show(name) {
        panels.forEach(function (p) { p.classList.toggle('hidden', p.getAttribute('data-panel') !== name); });
        tabs.forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-tab') === name); });
        // Remember the active tab so a form submit (which reloads the page) returns here.
        try { sessionStorage.setItem(KEY, name); } catch (e) {}
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { show(t.getAttribute('data-tab')); }); });

    function hasPanel(name) { return name && document.querySelector('.tab-panel[data-panel="' + name + '"]'); }

    // Priority: a tab with a validation error → the last tab the user was on → Contacts.
    var errForm = document.querySelector('[id^="add"]:not(.hidden)');
    if (errForm && errForm.closest('.tab-panel')) {
        show(errForm.closest('.tab-panel').getAttribute('data-panel'));
    } else {
        var stored = null;
        try { stored = sessionStorage.getItem(KEY); } catch (e) {}
        show(hasPanel(stored) ? stored : 'contacts');
    }
})();

function gcsmToggleForm(id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('hidden');
    if (!el.classList.contains('hidden')) {
        var f = el.querySelector('input, select, textarea');
        if (f) { try { f.focus(); } catch (e) {} }
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}
</script>
@endsection
