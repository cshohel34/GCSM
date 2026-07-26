<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans, sans-serif;font-size:9px;color:#222}
h1{color:#1F3864;font-size:14px;margin:0}
.sub{color:#666;font-size:10px;margin:2px 0 8px}
.meta{color:#444;font-size:9px;margin-bottom:8px}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #bbb;padding:3px 5px;text-align:left}
th{background:#1F3864;color:#fff;font-size:8px}
td.r,th.r{text-align:right}
tr.tot td{font-weight:bold;background:#eee}
</style></head><body>
    <h1>GOLDEN CAREER SHIP MANAGEMENT</h1>
    <div class="sub">{{ $title }}</div>
    <div class="meta">
        @foreach ($meta as $k => $v)<b>{{ $k }}:</b> {{ $v }} &nbsp; @endforeach
        <span style="float:right">Generated {{ now()->toDayDateTimeString() }}</span>
    </div>
    <table>
        <thead><tr>@foreach ($columns as $i => $c)<th class="{{ in_array($i,$numeric) ? 'r' : '' }}">{{ $c }}</th>@endforeach</tr></thead>
        <tbody>
        @foreach ($rows as $row)
            <tr class="{{ !empty($row['_total']) ? 'tot' : '' }}">
                @foreach ($columns as $i => $c)
                    <td class="{{ in_array($i,$numeric) ? 'r' : '' }}">{{ $row[$i] ?? '' }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</body></html>
