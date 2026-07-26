<tr class="border-t">
    <td class="py-1.5">{{ $row->vessel_name }}</td>
    <td>{{ $row->imo_no ?: '—' }}</td>
    <td>{{ $row->vessel_type }}</td>
    <td>{{ $row->rank }}</td>
    <td>{{ $row->company_name }}</td>
    <td>{{ optional($row->sign_on)->toDateString() }}</td>
    <td>{{ optional($row->sign_off)->toDateString() }}</td>
    <td>{{ $row->duration_days }}</td>
    <td class="text-right">@can('crew.edit')@if(($editing ?? true) && $row->source!=='oma')
        <form class="js-del inline" method="POST" action="{{ route('crew.sea.destroy', [$crew, $row]) }}">@csrf @method('DELETE')<button type="submit" class="text-red-500 text-xs">Remove</button></form>
    @endif @endcan</td>
</tr>
