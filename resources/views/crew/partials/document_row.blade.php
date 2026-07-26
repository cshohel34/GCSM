@php $badge = ['valid'=>'bg-emerald-100 text-emerald-700','expiring'=>'bg-amber-100 text-amber-700','expired'=>'bg-red-100 text-red-700','na'=>'bg-slate-100 text-slate-500'][$row->status] ?? 'bg-slate-100 text-slate-500'; @endphp
<tr class="border-t">
    <td class="py-1.5">{{ $row->doc_type }}</td>
    <td>{{ $row->number }}</td>
    <td>{{ optional($row->issue_date)->toDateString() }}</td>
    <td>{{ optional($row->expiry_date)->toDateString() }}</td>
    <td><span class="px-2 py-0.5 rounded text-xs {{ $badge }}">{{ ucfirst($row->status) }}</span></td>
    <td>
        @if ($row->scan_path)
            <span class="inline-flex items-center gap-1.5">
                <a href="{{ asset('storage/'.$row->scan_path) }}" target="_blank" rel="noopener" class="text-xs font-semibold rounded-md border border-slate-300 text-slate-700 px-2.5 py-1 hover:bg-slate-100">View</a>
                <a href="{{ asset('storage/'.$row->scan_path) }}" download class="text-xs font-semibold rounded-md bg-[#1F3864] text-white px-2.5 py-1 hover:bg-[#2E4A7A]">Download</a>
            </span>
        @else <span class="text-slate-300 text-xs">—</span> @endif
    </td>
    <td class="text-right">@can('crew.edit')@if(($editing ?? true))
        <form class="js-del inline" method="POST" action="{{ route('crew.documents.destroy', [$crew, $row]) }}">@csrf @method('DELETE')<button type="submit" class="text-red-500 text-xs">Remove</button></form>
    @endif @endcan</td>
</tr>
