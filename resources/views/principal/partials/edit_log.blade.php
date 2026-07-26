@php
    $typeLabels = [
        \App\Models\Principal::class         => 'Company / Profile',
        \App\Models\PrincipalContact::class  => 'Contact',
        \App\Models\PrincipalVessel::class   => 'Vessel',
        \App\Models\PrincipalDocument::class => 'Document / Contract',
        \App\Models\PrincipalNote::class     => 'Note',
        \App\Models\PrincipalOffence::class  => 'Offence',
    ];
    $eventStyle = [
        'created'  => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'updated'  => 'bg-blue-50 text-blue-700 ring-blue-200',
        'deleted'  => 'bg-rose-50 text-rose-700 ring-rose-200',
        'restored' => 'bg-amber-50 text-amber-800 ring-amber-200',
    ];
    $hide = ['updated_at','created_at','deleted_at','id','principal_id'];
    $fmt = function ($v) {
        if (is_array($v)) $v = json_encode($v);
        if ($v === true) $v = 'Yes'; if ($v === false) $v = 'No';
        return ($v === null || $v === '') ? '—' : (string) $v;
    };
    $log = $editLog ?? collect();
@endphp
<div class="text-sm">
    <div class="flex items-center justify-between mb-1">
        <h3 class="font-semibold text-navy-800">Edit Log — every change to this company</h3>
        <span class="text-xs text-slate-400">{{ $log->count() }} {{ \Illuminate\Support\Str::plural('entry', $log->count()) }}</span>
    </div>
    <p class="text-xs text-slate-400 mb-3">Every change to the company, its contacts, vessels, documents, notes and offences is recorded automatically with who changed it, when, and the old &rarr; new value. Entries can never be deleted.</p>
    <div class="max-h-[34rem] overflow-auto pr-1">
    @forelse ($log as $a)
        @php
            $new = (array) ($a->new_values ?? []);
            $old = (array) ($a->old_values ?? []);
            $fields = array_values(array_diff(array_keys($new + $old), $hide));
        @endphp
        <div class="border-t py-2.5">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold ring-1 {{ $eventStyle[$a->event] ?? 'bg-slate-50 text-slate-600 ring-slate-200' }}">{{ ucfirst($a->event) }}</span>
                    <span class="text-xs font-semibold text-navy-800">{{ $typeLabels[$a->auditable_type] ?? class_basename($a->auditable_type) }}</span>
                    <span class="text-[11px] text-slate-500">by <b class="text-navy-700">{{ optional($a->user)->name ?: 'System' }}</b></span>
                </div>
                <div class="text-[11px] text-slate-400">{{ optional($a->created_at)->format('d M Y, h:i A') }}</div>
            </div>
            @if (count($fields))
                <div class="mt-1.5 grid gap-1">
                    @foreach ($fields as $field)
                        <div class="text-xs text-slate-600 flex flex-wrap gap-x-2">
                            <span class="text-slate-400 min-w-[9rem]">{{ ucwords(str_replace('_',' ', $field)) }}:</span>
                            @if ($a->event === 'created')
                                <b class="text-navy-700">{{ $fmt($new[$field] ?? null) }}</b>
                            @else
                                <span><b class="text-slate-400 line-through decoration-slate-300">{{ $fmt($old[$field] ?? null) }}</b> <span class="text-gold-500">&rarr;</span> <b class="text-navy-700">{{ $fmt($new[$field] ?? null) }}</b></span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="text-slate-400 text-xs border-t pt-2">No edits recorded yet. Any change to this company will appear here.</div>
    @endforelse
    </div>
</div>
