@php
    $lv = $level ?? 'normal';
    $dl = $deadline ?? null;
    $days = ($dl && in_array($lv, ['high','urgent'])) ? (int) now()->startOfDay()->diffInDays($dl->startOfDay(), false) : null;
@endphp
@if ($lv === 'urgent')
    <span class="urgency-urgent inline-block px-2 py-0.5 rounded text-xs font-bold bg-red-600 text-white" title="Job urgency: Urgent">URGENT</span>
@elseif ($lv === 'high')
    <span class="urgency-high inline-block px-2 py-0.5 rounded text-xs font-bold bg-orange-500 text-white" title="Job urgency: High">HIGH</span>
@else
    <span class="urgency-normal inline-block px-2 py-0.5 rounded text-xs font-semibold bg-slate-200 text-slate-600" title="Job urgency: Normal">Normal</span>
@endif
@if (! is_null($days))
    <span class="ml-1 text-[11px] font-semibold {{ $days < 0 ? 'text-red-600' : ($days <= 7 ? 'text-orange-600' : 'text-slate-500') }}"
          title="Placement deadline {{ $dl->toDateString() }}">
        🕒 {{ $days < 0 ? abs($days).'d overdue' : $days.'d left' }}
    </span>
@endif
