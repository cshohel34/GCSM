@php
    $fields = [
        'Bank Name' => $b->bank_name,
        'Account Name' => $b->account_name,
        'Account Number' => $b->account_number,
        'Branch' => $b->branch,
        'Routing Number' => $b->routing_number,
        'Swift Code' => $b->swift_code,
        'Mobile Number' => $b->mobile_number,
    ];
    if (! $b->is_own_account) {
        $fields['Relation with Account Crew'] = $b->owner_relationship;
        $fields['Account Owner NID'] = $b->owner_nid;
    }
    $docs = [
        'Cheque Book Page Scan'     => $b->cheque_scan_path,
        'Owner NID Card'            => $b->owner_nid_scan_path,
        'Owner Passport-size Photo' => $b->owner_photo_path,
    ];
    $hasDocs = collect($docs)->filter()->isNotEmpty();
@endphp
<div class="border rounded-xl overflow-hidden">
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 px-4 py-2.5 bg-slate-50 border-b border-slate-100">
        <span class="font-bold text-navy-800">{{ $b->bank_name ?: 'Bank account' }}</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $b->is_own_account ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $b->is_own_account ? 'Own account' : 'Third-party'.($b->owner_relationship ? ' · '.$b->owner_relationship : '') }}</span>
        @if (($editable ?? false))
            @can('crew.edit')<form method="POST" action="{{ route('crew.bank.destroy', [$crew, $b]) }}" class="ml-auto" onsubmit="return confirm('Remove this account?')">@csrf @method('DELETE')<button class="text-red-500 text-xs hover:underline">Remove</button></form>@endcan
        @endif
    </div>
    <div class="grid md:grid-cols-2">
        {{-- LEFT: labelled fields with copy --}}
        <div class="p-4 space-y-1.5">
            @foreach ($fields as $label => $val)
                <div class="flex items-center gap-2 text-sm">
                    <div class="w-44 shrink-0 text-[11px] text-slate-400 uppercase tracking-wide">{{ $label }}</div>
                    <div class="flex-1 font-medium text-navy-800 truncate">{{ $val ?: '—' }}</div>
                    @if ($val)<button type="button" class="js-copy text-slate-300 hover:text-[#1F3864] shrink-0" data-copy="{{ $val }}" title="Copy {{ $label }}">⧉</button>@endif
                </div>
            @endforeach
        </div>
        {{-- RIGHT: uploaded documents with view / download --}}
        <div class="p-4 md:border-l border-slate-100 space-y-2">
            <div class="text-[11px] text-slate-400 uppercase tracking-wide">Uploaded Documents</div>
            @if ($hasDocs)
                @foreach ($docs as $label => $path)
                    @if ($path)
                        <div class="flex items-center justify-between gap-2 bg-slate-50 border border-slate-100 rounded-lg px-3 py-2">
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-navy-800 truncate">{{ $label }}</div>
                                <div class="text-[11px] text-slate-400 truncate">{{ basename($path) }}</div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ asset('storage/'.$path) }}" target="_blank" rel="noopener" class="text-xs font-semibold rounded-md border border-slate-300 text-slate-700 px-2.5 py-1 hover:bg-slate-100">View</a>
                                <a href="{{ asset('storage/'.$path) }}" download class="text-xs font-semibold rounded-md bg-[#1F3864] text-white px-2.5 py-1 hover:bg-[#2E4A7A]">Download</a>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="text-xs text-slate-400">No documents uploaded.</div>
            @endif
        </div>
    </div>
</div>
