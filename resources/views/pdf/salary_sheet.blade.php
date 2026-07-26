<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans, sans-serif;font-size:8px;color:#222}
h1{color:#1F3864;font-size:13px;margin:0}
table{width:100%;border-collapse:collapse;margin-top:8px}
th,td{border:1px solid #bbb;padding:2px 3px;text-align:right}
th{background:#1F3864;color:#fff;font-size:7px;text-align:center}
td.l,th.l{text-align:left}
tfoot td{font-weight:bold;background:#eee}
</style></head><body>
    <h1>GOLDEN CAREER SHIP MANAGEMENT — Crew Salary Sheet</h1>
    <div style="color:#666">{{ optional($sheet->principal)->name }} · {{ $sheet->month }} · Ref {{ $sheet->reference }} · USD Rate {{ $sheet->usd_rate }} · {{ ucfirst($sheet->status) }}</div>
    <table>
        <thead><tr>
            <th>SL</th><th class="l">Crew</th><th class="l">Ship</th><th>Rank</th><th>Salary USD</th><th>Rate</th><th>Bonus</th><th>Join</th>
            <th>Days</th><th>Work</th><th>Ded</th><th>Gross USD</th><th>Net USD</th><th>Net BDT</th>
            <th>Agent USD</th><th>Agent Net</th><th>Agent BDT</th><th class="l">Remarks</th>
        </tr></thead>
        <tbody>
        @foreach ($sheet->lines as $l)
            <tr>
                <td>{{ $l->sl_no }}</td><td class="l">{{ $l->crew_name }}</td><td class="l">{{ $l->ship_name }}</td><td>{{ $l->rank }}</td>
                <td>{{ number_format($l->salary_usd,2) }}</td><td>{{ $l->usd_rate }}</td><td>{{ number_format($l->bonus_usd,2) }}</td><td>{{ optional($l->joining_date)->format('d-m-y') }}</td>
                <td>{{ $l->total_days }}</td><td>{{ $l->working_days }}</td><td>{{ $l->deduct_days }}</td>
                <td>{{ number_format($l->gross_usd,2) }}</td><td>{{ number_format($l->net_usd,2) }}</td><td>{{ number_format($l->net_bdt,2) }}</td>
                <td>{{ number_format($l->agent_fee_usd,2) }}</td><td>{{ number_format($l->agent_net_usd,2) }}</td><td>{{ number_format($l->agent_net_bdt,2) }}</td>
                <td class="l">{{ $l->remarks }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot><tr><td colspan="13" class="l">TOTAL</td><td>{{ number_format($sheet->lines->sum('net_bdt'),2) }}</td><td colspan="2"></td><td>{{ number_format($sheet->lines->sum('agent_net_bdt'),2) }}</td><td></td></tr></tfoot>
    </table>
</body></html>
