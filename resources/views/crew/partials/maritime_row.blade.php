<tr class="border-t">
    <td class="py-1.5">{{ $row->institute }}</td>
    <td>{{ $row->department }}</td>
    <td>{{ $row->year_of_graduation }}</td>
    <td class="text-right">@can('crew.edit')@if(($editing ?? true))
        <form class="js-del inline" method="POST" action="{{ route('crew.maritime.destroy', [$crew, $row]) }}">@csrf @method('DELETE')<button type="submit" class="text-red-500 text-xs">Remove</button></form>
    @endif @endcan</td>
</tr>
