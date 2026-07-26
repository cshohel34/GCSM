@php $logs = $crew->statusLogs; @endphp
<div class="bg-white rounded-lg shadow p-4 text-sm">
    <div class="gcsm-head justify-between">
        <h3 class="font-semibold text-sm md:text-base">Availability &amp; Urgency — Change Log</h3>
        <span class="text-xs text-slate-400">{{ $logs->count() }} change(s)</span>
    </div>
    <div class="max-h-80 overflow-auto pr-1">
    @forelse ($logs as $log)
        <div class="border-t py-2.5">
            <div class="flex items-center justify-between gap-2">
                <div class="text-xs font-semibold text-navy-800">
                    {{ optional($log->changedBy)->name ?: 'System' }}
                    <span class="ml-1 inline-block px-1.5 py-0.5 rounded text-[10px] bg-navy-800 text-gold-300">{{ $log->context_label }}</span>
                </div>
                <div class="text-[11px] text-slate-400">{{ $log->created_at->format('d M Y, h:i A') }}</div>
            </div>
            <div class="text-xs text-slate-600 mt-1 flex flex-wrap gap-x-4 gap-y-0.5">
                @if ($log->old_availability !== $log->new_availability)
                    <span>Availability:
                        <b class="text-slate-400">{{ $log->old_availability ? ucfirst(str_replace('_',' ',$log->old_availability)) : '—' }}</b>
                        <span class="text-gold-500">→</span>
                        <b class="text-navy-700">{{ ucfirst(str_replace('_',' ',$log->new_availability)) }}</b>
                    </span>
                @endif
                @if ($log->old_urgency !== $log->new_urgency)
                    <span>Urgency:
                        <b class="text-slate-400">{{ $log->old_urgency ? ucfirst($log->old_urgency) : '—' }}</b>
                        <span class="text-gold-500">→</span>
                        <b class="text-navy-700">{{ ucfirst($log->new_urgency) }}</b>
                    </span>
                @endif
                @if (optional($log->old_deadline)->toDateString() !== optional($log->new_deadline)->toDateString())
                    <span>Deadline:
                        <b class="text-slate-400">{{ optional($log->old_deadline)->toDateString() ?: '—' }}</b>
                        <span class="text-gold-500">→</span>
                        <b class="text-navy-700">{{ optional($log->new_deadline)->toDateString() ?: '—' }}</b>
                    </span>
                @endif
                @if (optional($log->old_available_from)->toDateString() !== optional($log->new_available_from)->toDateString())
                    <span>Available from:
                        <b class="text-slate-400">{{ optional($log->old_available_from)->toDateString() ?: '—' }}</b>
                        <span class="text-gold-500">→</span>
                        <b class="text-navy-700">{{ optional($log->new_available_from)->toDateString() ?: '—' }}</b>
                    </span>
                @endif
            </div>
            @if ($log->reason)
                <div class="text-xs text-slate-500 mt-1"><span class="text-slate-400">Reason:</span> {{ $log->reason }}</div>
            @endif
        </div>
    @empty
        <div class="text-slate-400 text-xs border-t pt-2">No availability or urgency changes recorded yet.</div>
    @endforelse
    </div>
</div>
