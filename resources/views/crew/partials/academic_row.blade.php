<tr class="border-t">
    <td class="py-1.5">{{ $row->description }}</td>
    <td>{{ $row->board }}</td>
    <td>{{ $row->group }}</td>
    <td>{{ $row->passing_year }}</td>
    <td>{{ $row->gpa }}</td>
    <td class="text-right">@can('crew.edit')@if(($editing ?? true))
        <form class="js-del inline" method="POST" action="{{ route('crew.academics.destroy', [$crew, $row]) }}">@csrf @method('DELETE')<button type="submit" class="text-red-500 text-xs">Remove</button></form>
    @endif @endcan</td>
</tr>
