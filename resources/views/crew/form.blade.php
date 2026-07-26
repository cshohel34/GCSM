@extends('layouts.app')
@section('title', $crew->exists ? 'Edit Crew' : 'New Crew')
@section('content')
<form method="POST" action="{{ $crew->exists ? route('crew.update', $crew) : route('crew.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 w-full">
    @csrf
    @if ($crew->exists) @method('PUT') @endif

    @if (session('duplicates'))
        <div class="mb-4 rounded bg-red-50 border border-red-300 text-red-800 px-4 py-3 text-sm">
            <b>Duplicate profile found — cannot create.</b>
            @foreach (session('duplicates') as $d)
                <div class="mt-1">{{ $d['name'] }} <span class="text-xs text-red-500">({{ $d['display_id'] }})</span> — matched on {{ $d['reason'] }}
                    <a href="{{ route('crew.show', $d['id']) }}" class="underline">open</a></div>
            @endforeach
        </div>
    @endif
    @unless ($crew->exists)
        <div class="mb-4 rounded bg-blue-50 border border-blue-200 text-blue-800 px-4 py-2 text-xs">
            OMA students are fetched via API and get an Admission ID automatically. Use this form only for non-OMA
            seafarers — no Admission ID is needed; a GCSM ID is generated. The system checks for duplicates before saving.
        </div>
    @endunless

    @unless ($crew->exists)
        <div class="mb-5 flex items-center gap-2 text-sm">
            <span class="text-slate-500">Adding by:</span>
            <a href="{{ route('crew.create', ['mode' => 'cv']) }}" class="ptab {{ ($mode ?? '') === 'cv' ? 'active' : '' }}">CV upload</a>
            <a href="{{ route('crew.create', ['mode' => 'manual']) }}" class="ptab {{ ($mode ?? '') === 'manual' ? 'active' : '' }}">Manual entry</a>
        </div>
    @endunless

    @if (! $crew->exists && ($mode ?? '') === 'cv')
    <div class="mb-6 rounded-lg border-2 border-dashed border-[#2E74B5] bg-blue-50/60 p-4">
        <label class="block text-sm font-semibold text-navy-800 mb-1">Step 1 — Upload CV (text-based PDF)</label>
        <p class="text-xs text-slate-500 mb-3">Choose the seafarer's CV PDF and press "Read CV &amp; fill". The form below fills in automatically. Please check every field before saving. (Scanned/image PDFs cannot be read — just type the details in.)</p>
        <div class="flex items-center gap-3">
            <input type="file" id="cvAutofill" accept="application/pdf" class="text-xs">
            <button type="button" id="cvAutofillBtn" class="bg-[#1F3864] text-white text-sm px-4 py-1.5 rounded">Read CV &amp; fill</button>
            <span id="cvAutofillMsg" class="text-xs text-slate-500"></span>
        </div>
    </div>
    @endif

    <div class="gcsm-head"><h3 class="font-semibold text-sm md:text-base">Identity</h3></div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm mb-6">
        @if ($crew->exists)
        <div><label class="block mb-1">ID</label>
            <input value="{{ $crew->admission_id ?: ($crew->gc_id ?: ('#'.$crew->id)) }}" readonly class="w-full border rounded px-2 py-1.5 bg-slate-100">
            <input type="hidden" name="admission_id" value="{{ $crew->admission_id }}"></div>
        @endif
        <div><label class="block mb-1">Name *</label>
            <input name="name" value="{{ old('name', $crew->name) }}" required class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Name (Chinese)</label>
            <input name="name_chinese" value="{{ old('name_chinese', $crew->name_chinese) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Current rank</label>
            <select name="current_rank_id" class="w-full border rounded px-2 py-1.5">@include('crew.partials.rank_options', ['selected' => old('current_rank_id', $crew->current_rank_id)])</select></div>
        <div><label class="block mb-1">Rank applied for</label>
            <select name="rank_applied_id" class="w-full border rounded px-2 py-1.5">@include('crew.partials.rank_options', ['selected' => old('rank_applied_id', $crew->rank_applied_id)])</select></div>
        <div><label class="block mb-1">Photo</label><input type="file" name="photo" accept="image/*" class="w-full text-xs"></div>
        <div><label class="block mb-1">CDC No</label><input name="cdc_no" value="{{ old('cdc_no', $crew->cdc_no) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Passport No</label><input name="passport_no" value="{{ old('passport_no', $crew->passport_no) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">COC No</label><input name="coc_no" value="{{ old('coc_no', $crew->coc_no) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Father's name</label><input name="father_name" value="{{ old('father_name', $crew->father_name) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Mother's name</label><input name="mother_name" value="{{ old('mother_name', $crew->mother_name) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">SID No</label><input name="sid_no" value="{{ old('sid_no', $crew->sid_no) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">National ID</label><input name="nid_no" value="{{ old('nid_no', $crew->nid_no) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Birth Reg No</label><input name="birth_registration_no" value="{{ old('birth_registration_no', $crew->birth_registration_no) }}" class="w-full border rounded px-2 py-1.5"></div>
    </div>

    <div class="gcsm-head"><h3 class="font-semibold text-sm md:text-base">Personal</h3></div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm mb-6">
        <div><label class="block mb-1">Date of birth</label><input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($crew->date_of_birth)->toDateString()) }}" class="w-full border rounded px-2 py-1.5"></div>
        @php $bdDistricts = ['Bagerhat','Bandarban','Barguna','Barishal','Bhola','Bogura','Brahmanbaria','Chandpur','Chattogram','Chuadanga',"Cox's Bazar",'Cumilla','Dhaka','Dinajpur','Faridpur','Feni','Gaibandha','Gazipur','Gopalganj','Habiganj','Jamalpur','Jashore','Jhalokati','Jhenaidah','Joypurhat','Khagrachhari','Khulna','Kishoreganj','Kurigram','Kushtia','Lakshmipur','Lalmonirhat','Madaripur','Magura','Manikganj','Meherpur','Moulvibazar','Munshiganj','Mymensingh','Naogaon','Narail','Narayanganj','Narsingdi','Natore','Chapainawabganj','Netrokona','Nilphamari','Noakhali','Pabna','Panchagarh','Patuakhali','Pirojpur','Rajbari','Rajshahi','Rangamati','Rangpur','Satkhira','Shariatpur','Sherpur','Sirajganj','Sunamganj','Sylhet','Tangail','Thakurgaon']; @endphp
        <div><label class="block mb-1">Place of birth <span class="text-slate-400 text-xs">(search district)</span></label>
            <select name="place_of_birth" data-placeholder="Select district…" class="w-full border rounded px-2 py-1.5">
                <option value="">—</option>
                @foreach ($bdDistricts as $d)<option value="{{ $d }}" @selected(old('place_of_birth', $crew->place_of_birth) === $d)>{{ $d }}</option>@endforeach
            </select></div>
        <div><label class="block mb-1">Nationality</label><input name="nationality" value="{{ old('nationality', $crew->nationality ?? 'Bangladeshi') }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Gender</label><select name="gender" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
            @foreach (['Male','Female'] as $g)<option @selected(old('gender', $crew->gender) === $g)>{{ $g }}</option>@endforeach</select></div>
        <div><label class="block mb-1">Marital status</label><select name="marital_status" class="w-full border rounded px-2 py-1.5"><option value="">—</option>
            @foreach (['Single','Married','Widowed','Separated','Divorced','Not specified'] as $m)<option @selected(old('marital_status', $crew->marital_status) === $m)>{{ $m }}</option>@endforeach</select></div>
        <div><label class="block mb-1">Blood group</label>
            <select name="blood_group" data-placeholder="Select…" class="w-full border rounded px-2 py-1.5">
                <option value="">—</option>
                @foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)<option value="{{ $bg }}" @selected(old('blood_group', $crew->blood_group) === $bg)>{{ $bg }}</option>@endforeach
            </select></div>
    </div>

    <div class="gcsm-head"><h3 class="font-semibold text-sm md:text-base">Contact &amp; status</h3></div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm mb-6">
        <div><label class="block mb-1">Mobile</label><input name="mobile" value="{{ old('mobile', $crew->mobile) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Email</label><input name="email" value="{{ old('email', $crew->email) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div><label class="block mb-1">Emergency contact</label><input name="emergency_contact" value="{{ old('emergency_contact', $crew->emergency_contact) }}" class="w-full border rounded px-2 py-1.5"></div>
        <div class="md:col-span-3"><label class="block mb-1">Present address</label><input name="present_address" value="{{ old('present_address', $crew->present_address) }}" class="w-full border rounded px-2 py-1.5"></div>
    </div>
    <p class="text-xs text-slate-400 -mt-4 mb-6">Availability &amp; job urgency are set from the <b>Placement History</b> tab after the profile is created.</p>

    <div class="flex gap-2">
        <button class="bg-[#1F3864] text-white px-5 py-2 rounded hover:bg-[#2E74B5]">{{ $crew->exists ? 'Save changes' : 'Save (draft) & continue' }}</button>
        <a href="{{ $crew->exists ? route('crew.show', $crew) : route('crew.index') }}" class="px-5 py-2 rounded border">Cancel</a>
    </div>
    @unless ($crew->exists)
    <p class="text-xs text-slate-500 mt-3">Only the Name is required to save. The profile is kept as a <b>Draft</b> — nothing is lost — and you complete the rest (maritime education, certificates, sea service, etc.) on the profile page. It becomes <b>Complete</b> automatically at 100%.</p>
    @endunless
    <p class="text-xs text-slate-400 mt-1">Sea service, certificates, documents, bank accounts, offences and notes are managed from the crew profile page after creation.</p>
    @if ($crew->exists)
        <div class="mt-4 text-sm"><label class="block mb-1 text-slate-500">Reason for change (staff edits need Manager/Super Admin approval)</label>
            <input name="change_reason" class="w-full border rounded px-2 py-1.5 max-w-xl" placeholder="e.g. updated mobile / corrected passport"></div>
    @endif
</form>

<script>
(function () {
    var btn = document.getElementById('cvAutofillBtn');
    var input = document.getElementById('cvAutofill');
    var msg = document.getElementById('cvAutofillMsg');
    if (!btn) return;

    btn.addEventListener('click', function () {
        if (!input.files || !input.files.length) { msg.textContent = 'Choose a PDF first.'; return; }
        var fd = new FormData();
        fd.append('cv', input.files[0]);
        fd.append('_token', document.querySelector('input[name=_token]').value);
        msg.textContent = 'Reading CV…';
        btn.disabled = true;
        fetch('{{ route('crew.parsecv') }}', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
            .then(function (res) {
                btn.disabled = false;
                if (!res.ok || !res.d.ok) { msg.textContent = (res.d && res.d.message) || 'Could not read this CV.'; return; }
                var p = res.d.profile || {};
                var filled = 0;
                Object.keys(p).forEach(function (key) {
                    var el = document.querySelector('[name="' + key + '"]');
                    if (el && p[key] != null && p[key] !== '') { el.value = p[key]; filled++; }
                });
                msg.textContent = filled
                    ? ('Filled ' + filled + ' field(s) from the CV — please review before saving.')
                    : 'No matching fields found in this CV. Please fill the form manually.';
            })
            .catch(function () { btn.disabled = false; msg.textContent = 'Upload failed. Try again.'; });
    });
})();
</script>
@endsection
