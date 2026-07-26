<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }
    body { margin: 0; font-family: 'DejaVu Sans', sans-serif; color: #12233F; }

    /* GCSM official letterhead as the page background (unchanged) */
    .pad { position: absolute; top: 0; left: 0; width: 210mm; height: 297mm; }

    /* Content sits in the blank body area, clear of the header and footer */
    .content { position: absolute; top: 33mm; left: 12mm; width: 186mm; }

    .title { text-align: center; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }

    table { width: 100%; border-collapse: collapse; }
    .info td { font-size: 9.5px; padding: 3px 6px; border: 0.6px solid #9aa4b2; }
    .info .lbl { font-weight: bold; width: 22%; background: #f1f4f8; }

    .list { margin-top: 10px; }
    .list th { font-size: 9px; text-transform: uppercase; letter-spacing: .3px; background: #1F3864; color: #fff; padding: 5px 6px; border: 0.6px solid #1F3864; text-align: left; }
    .list td { font-size: 9.5px; padding: 4px 6px; border: 0.6px solid #c8d0dc; vertical-align: top; }
    .c-sl { width: 8%; text-align: center; }
    .c-doc { width: 40%; }
    .c-st { width: 12%; text-align: center; }
    .c-rm { width: 40%; }
    .yes { color: #16a34a; font-weight: bold; font-size: 18px; line-height: 1; }
    .no  { color: #dc2626; font-weight: bold; font-size: 18px; line-height: 1; }
    .foot { margin-top: 12px; font-size: 9px; }
    .sign { margin-top: 70px; font-size: 10.5px; }
    .sign td { width: 50%; padding-top: 22px; }
    .sign .line { border-top: 0.8px solid #333; padding-top: 3px; font-weight: bold; }
</style>
</head>
<body>
    <img class="pad" src="{{ public_path('img/gcsm-pad.png') }}">

    <div class="content">
        <div class="title">Crew On Board Check List</div>

        <table class="info">
            <tr>
                <td class="lbl">Name</td><td>{{ optional($crew)->name }}</td>
                <td class="lbl">Vessel</td><td>{{ optional(optional($candidate->position)->vessel)->vessel_name ?: '—' }}</td>
            </tr>
            <tr>
                <td class="lbl">Rank</td><td>{{ optional(optional($crew)->currentRank)->rank_name ?: '—' }}</td>
                <td class="lbl">Crew ID</td><td>{{ optional($crew)->display_id }}</td>
            </tr>
            <tr>
                <td class="lbl">Passport No</td><td>{{ optional($crew)->passport_no ?: '—' }}</td>
                <td class="lbl">CDC No</td><td>{{ optional($crew)->cdc_no ?: '—' }}</td>
            </tr>
            <tr>
                <td class="lbl">Principal / Company</td><td>{{ optional(optional(optional($candidate->position)->requisition)->principal)->name ?: '—' }}</td>
                <td class="lbl">Date</td><td>{{ now()->format('d-M-Y') }}</td>
            </tr>
        </table>

        <table class="list">
            <thead>
                <tr>
                    <th class="c-sl">SL No</th>
                    <th class="c-doc">Document Check List</th>
                    <th class="c-st">Status</th>
                    <th class="c-rm">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $it)
                    <tr>
                        <td class="c-sl">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="c-doc">{{ $it->item }}</td>
                        <td class="c-st">
                            @if ($it->is_received)<span class="yes">&#10004;</span>@else<span class="no">&#10008;</span>@endif
                        </td>
                        <td class="c-rm">{{ $it->notes }}{{ $it->code ? '' : ' (custom)' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="foot">
            <span style="color:#16a34a; font-weight:bold;">&#10004;</span> = document received / mapped &nbsp;&nbsp;·&nbsp;&nbsp;
            <span style="color:#dc2626; font-weight:bold;">&#10008;</span> = to be collected
        </div>

        <table class="sign">
            <tr>
                <td><div class="line">Prepared by</div></td>
                <td style="text-align:right;"><div class="line" style="display:inline-block; min-width:60%;">Authorised Signature</div></td>
            </tr>
        </table>
    </div>
</body>
</html>
