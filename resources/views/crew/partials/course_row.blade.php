@php
    $vst = 'na';
    if ($row->expiry_date) {
        $t = now()->startOfDay();
        $vst = $row->expiry_date->lt($t) ? 'expired' : ($row->expiry_date->lte($t->copy()->addDays(30)) ? 'expiring' : 'valid');
    }
    $vstyle = ['valid'=>'bg-emerald-50 text-emerald-700','expiring'=>'bg-amber-50 text-amber-700','expired'=>'bg-rose-50 text-rose-700','na'=>'bg-slate-100 text-slate-400'];
@endphp
<tr class="border-t">
    <td class="py-1.5">{{ $row->category ?: $row->course_name }}</td>
    <td class="text-slate-500">{{ $row->course_code ?: '—' }}</td>
    <td>{{ $row->certificate_no }}</td>
    <td>{{ optional($row->issue_date)->toDateString() }}</td>
    <td>{{ optional($row->expiry_date)->toDateString() ?: '—' }}@if($row->expiry_date) <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $vstyle[$vst] }}">{{ ucfirst($vst) }}</span>@endif</td>
    <td>{{ $row->issuer }}</td>
    <td>{{ $row->issuing_authority }}</td>
    <td><span class="px-2 py-0.5 rounded text-xs {{ $row->source==='oma'?'bg-blue-100 text-blue-700':'bg-slate-100 text-slate-600' }}">{{ strtoupper($row->source) }}</span></td>
    <td>
        @if ($row->scan_path)
            <span class="inline-flex items-center gap-1.5">
                <a href="{{ asset('storage/'.$row->scan_path) }}" target="_blank" rel="noopener" class="text-xs font-semibold rounded-md border border-slate-300 text-slate-700 px-2.5 py-1 hover:bg-slate-100">View</a>
                <a href="{{ asset('storage/'.$row->scan_path) }}" download class="text-xs font-semibold rounded-md bg-[#1F3864] text-white px-2.5 py-1 hover:bg-[#2E4A7A]">Download</a>
            </span>
        @else <span class="text-slate-300 text-xs">—</span> @endif
    </td>
    <td class="text-right">@can('crew.edit')@if(($editing ?? true) && $row->source!=='oma')
        <form class="js-del inline" method="POST" action="{{ route('crew.courses.destroy', [$crew, $row]) }}">@csrf @method('DELETE')<button type="submit" class="text-red-500 text-xs">Remove</button></form>
    @endif @endcan</td>
</tr>
