<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans, sans-serif;font-size:10px;color:#222}
h1{color:#1F3864;font-size:16px;margin:0}
table{width:100%;border-collapse:collapse;margin-top:10px}
th,td{border:1px solid #bbb;padding:4px 6px;text-align:left;vertical-align:top}
th{background:#1F3864;color:#fff;font-size:9px}
</style></head><body>
    <h1>GOLDEN CAREER SHIP MANAGEMENT — Principal / Management Company Directory</h1>
    <div style="color:#666">Generated {{ now()->toDayDateTimeString() }} · {{ $principals->count() }} companies</div>
    <table>
        <thead><tr><th>Company</th><th>Type</th><th>Country</th><th>Phone / Email</th><th>Managing Staff / Partner</th><th>Contacts</th><th>Status</th></tr></thead>
        <tbody>
        @foreach ($principals as $p)
            <tr>
                <td><b>{{ $p->name }}</b></td>
                <td>{{ ucfirst($p->type) }}</td>
                <td>{{ $p->country }}</td>
                <td>{{ $p->phone }}<br>{{ $p->email }}</td>
                <td>{{ optional($p->assignedStaff)->name }}</td>
                <td>@foreach ($p->contacts as $c){{ $c->name }}@if($c->designation) ({{ $c->designation }})@endif — {{ $c->phone }}<br>@endforeach</td>
                <td>{{ ucfirst($p->status) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body></html>
