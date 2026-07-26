<option value="">—</option>
@php $lastDept = null; @endphp
@foreach ($ranks as $r)
    @if ($r->department !== $lastDept)
        @if ($lastDept !== null)</optgroup>@endif
        <optgroup label="{{ $r->department ?: 'Other' }}">
        @php $lastDept = $r->department; @endphp
    @endif
    <option value="{{ $r->id }}" @selected((string)($selected ?? '') === (string)$r->id)>{{ $r->rank_name }}</option>
@endforeach
@if ($lastDept !== null)</optgroup>@endif
