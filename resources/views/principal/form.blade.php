@extends('layouts.app')
@section('title', $principal->exists ? 'Edit Company' : 'New Company')
@section('actions')
    <a href="{{ $principal->exists ? route('principal.show', $principal) : route('principal.index') }}" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition">← Cancel</a>
@endsection
@section('content')

<form method="POST" enctype="multipart/form-data"
      action="{{ $principal->exists ? route('principal.update', $principal) : route('principal.store') }}"
      class="bg-white rounded-xl shadow p-6 md:p-8">
    @csrf
    @if ($principal->exists) @method('PUT') @endif

    <div class="flex items-start gap-6 pb-6 mb-6 border-b border-slate-100">
        {{-- Logo --}}
        <div class="shrink-0 text-center">
            @php $logo = $principal->logo_path ? asset('storage/'.$principal->logo_path) : null; @endphp
            <label for="logoInput" title="Click to choose a logo" class="w-28 h-28 rounded-xl ring-1 ring-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center cursor-pointer hover:ring-[#C9A227]">
                <img id="logoPreview" src="{{ $logo }}" alt="logo" class="w-full h-full object-contain {{ $logo ? '' : 'hidden' }}">
                <span id="logoPlaceholder" class="text-slate-300 text-3xl {{ $logo ? 'hidden' : '' }}">🏢</span>
            </label>
            <input type="file" id="logoInput" name="logo" accept="image/*" class="hidden" onchange="gcsmLogoPreview(this)">
            <label for="logoInput" class="mt-2 inline-block text-xs font-semibold text-[#1F3864] cursor-pointer hover:underline">
                {{ $principal->logo_path ? 'Change logo' : 'Add logo' }}
            </label>
            <div class="text-[10px] text-slate-400 mt-0.5">Optional · PNG/JPG</div>
        </div>

        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Company name *</label>
                <input name="name" value="{{ old('name', $principal->name) }}" required class="w-full border border-slate-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1F3864]">
            </div>
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Type *</label>
                <select name="type" class="w-full border border-slate-300 rounded-md px-3 py-2">
                    <option value="principal" @selected(old('type', $principal->type)==='principal')>Principal (owns vessels)</option>
                    <option value="management" @selected(old('type', $principal->type)==='management')>Management company</option>
                </select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Country</label>
            <select name="country" id="countrySelect" data-placeholder="Search a country…" class="w-full border border-slate-300 rounded-md px-3 py-2">
                <option value="">Select country…</option>
                @foreach (config('countries') as $cname => $dial)
                    <option value="{{ $cname }}" data-dial="{{ $dial }}" @selected(old('country', $principal->country)===$cname)>{{ $cname }} ({{ $dial }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Phone</label>
            <input name="phone" id="phoneInput" value="{{ old('phone', $principal->phone) }}" class="w-full border border-slate-300 rounded-md px-3 py-2" placeholder="Country code auto-fills on selecting a country">
        </div>
        <div>
            <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Email</label>
            <input name="email" type="email" value="{{ old('email', $principal->email) }}" class="w-full border border-slate-300 rounded-md px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Website</label>
            <input name="website" value="{{ old('website', $principal->website) }}" class="w-full border border-slate-300 rounded-md px-3 py-2" placeholder="https://">
        </div>
        <div class="md:col-span-2">
            <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Address</label>
            <textarea name="address" rows="2" class="w-full border border-slate-300 rounded-md px-3 py-2">{{ old('address', $principal->address) }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded-md px-3 py-2">{{ old('notes', $principal->notes) }}</textarea>
        </div>
    </div>

    <div class="flex gap-2 mt-6 pt-5 border-t border-slate-100">
        <button class="bg-[#1F3864] text-white font-semibold px-6 py-2 rounded-md hover:bg-[#2E74B5] transition">{{ $principal->exists ? 'Save changes' : 'Create company' }}</button>
        <a href="{{ $principal->exists ? route('principal.show', $principal) : route('principal.index') }}" class="px-6 py-2 rounded-md border border-slate-300 text-slate-700 font-semibold hover:bg-slate-100">Cancel</a>
    </div>
</form>

<script>
    function gcsmLogoPreview(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = document.getElementById('logoPreview');
                var ph = document.getElementById('logoPlaceholder');
                img.src = e.target.result; img.classList.remove('hidden');
                if (ph) ph.classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    (function () {
        var sel = document.getElementById('countrySelect');
        var phone = document.getElementById('phoneInput');
        if (!sel || !phone) return;
        var lastDial = '';
        sel.addEventListener('change', function () {
            var opt = sel.options[sel.selectedIndex];
            var dial = opt ? (opt.getAttribute('data-dial') || '') : '';
            if (!dial) return;
            var cur = phone.value.trim();
            // Set the code only when the field is empty or still holds the previous code.
            if (cur === '' || cur === lastDial) {
                phone.value = dial + ' ';
            }
            lastDial = dial;
        });
    })();
</script>
@endsection
