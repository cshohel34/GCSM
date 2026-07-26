<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans, sans-serif;font-size:9px;color:#222}
h1{color:#1F3864;font-size:14px;margin:0}
.sub{color:#666;font-size:9px;margin:2px 0 8px}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #bbb;padding:3px 5px;text-align:left}
th{background:#1F3864;color:#fff;font-size:8px}
</style></head><body>
    <h1>GOLDEN CAREER SHIP MANAGEMENT</h1>
    <div class="sub">Crew List · {{ $crew->count() }} records · {{ now()->toDayDateTimeString() }}</div>
    <table>
        <thead><tr><th>Admission ID</th><th>Name</th><th>Rank</th><th>Mobile</th><th>CDC</th><th>Passport</th><th>Availability</th></tr></thead>
        <tbody>
        @foreach ($crew as $c)
            <tr><td>{{ $c->admission_id }}</td><td>{{ $c->name }}</td><td>{{ optional($c->currentRank)->rank_name }}</td>
                <td>{{ $c->mobile }}</td><td>{{ $c->cdc_no }}</td><td>{{ $c->passport_no }}</td>
                <td>{{ $c->availability === 'available' ? 'Available' : 'Not available' }}</td></tr>
        @endforeach
        </tbody>
    </table>
</body></html>
