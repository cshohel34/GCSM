@php $canEdit = ($editing ?? false); @endphp
<div id="voyages" class="bg-white rounded-lg shadow p-4 text-sm">
    <div class="gcsm-head justify-between">
        <h3 class="font-semibold text-sm md:text-base">GCSM Job Placement History</h3>
        <span class="text-xs text-slate-400">{{ $crew->placements->count() }} voyage(s)</span>
    </div>
    <div class="max-h-72 overflow-auto pr-1">
    @forelse ($crew->placements as $pl)
        <div class="border-t py-2">
            <div class="flex items-center justify-between gap-2">
                <div class="font-medium text-slate-700 truncate">{{ optional($pl->principal)->name ?: '—' }}</div>
                <span class="shrink-0 px-2 py-0.5 rounded text-[10px] {{ $pl->status === 'onboard' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }}">{{ $pl->status === 'onboard' ? 'Onboard' : 'Completed' }}</span>
            </div>
            <div class="text-xs text-slate-500">{{ optional($pl->vessel)->vessel_name ?: '—' }} · {{ $pl->rank ?: '—' }}</div>
            <div class="text-xs text-slate-400">
                {{ optional($pl->sign_on_date)->toDateString() ?: '—' }} → {{ optional($pl->sign_off_date)->toDateString() ?: 'onboard' }}
                @if ($pl->sign_on_date && $pl->sign_off_date) · {{ $pl->sign_on_date->diffInDays($pl->sign_off_date) }}d @endif
                @if ($pl->has_dues) <span class="text-amber-600">· dues</span> @endif
            </div>
            @if ($pl->status === 'signed_off' && $pl->sign_off_reason)
                <div class="text-xs text-slate-500 mt-1"><span class="text-slate-400">Sign-off reason:</span> {{ $pl->sign_off_reason }}@if ($pl->sign_off_note) — {{ $pl->sign_off_note }}@endif</div>
            @endif
            @if ($canEdit && $pl->status === 'onboard')
                <button type="button" onclick="gcsmSignOff({ action: @js(route('placements.signoff', $pl)), signOnDate: @js(optional($pl->sign_on_date)->toDateString()) })"
                        class="mt-2 inline-flex items-center gap-1 rounded-md bg-slate-700 text-white text-xs px-3 py-1.5 font-semibold hover:bg-slate-800">⚓ Sign off (complete voyage)</button>
            @endif
        </div>
    @empty
        <div class="text-slate-400 text-xs border-t pt-2">No voyages yet. Sign-On is recorded in the Crew Selection module after the selection steps.</div>
    @endforelse
    </div>
</div>
