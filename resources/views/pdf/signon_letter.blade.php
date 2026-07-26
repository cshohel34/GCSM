<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }
    body { margin: 0; font-family: 'DejaVu Serif', serif; color: #111; }
    /* GCSM official letterhead as the page background (unchanged) */
    .pad { position: absolute; top: 0; left: 0; width: 210mm; height: 297mm; }
    /* Letter body sits in the blank area, clear of the printed header & footer */
    .content { position: absolute; top: 33mm; left: 16mm; width: 178mm; font-size: 11.5px; line-height: 1.5; }
    .refrow { width: 100%; margin-bottom: 8px; }
    .refrow td { font-weight: bold; font-size: 12px; }
    .sub { font-weight: bold; text-decoration: underline; margin: 8px 0; }
    p { margin: 5px 0; text-align: justify; }
    table.grid { width: 100%; border-collapse: collapse; margin: 6px 0; }
    table.grid th, table.grid td { border: 1px solid #333; padding: 3px 4px; font-size: 10px; text-align: center; }
    table.grid th { background: #eef2f8; }
    .kv { margin: 3px 0; font-weight: bold; }
    .sign { margin-top: 10px; }
    .sign .nm { font-weight: bold; }
    .sign-gap { height: 42px; }
</style>
</head>
<body>
    <img class="pad" src="{{ public_path('img/gcsm-pad.png') }}">
    <div class="content">
        <table class="refrow"><tr>
            <td style="text-align:left;">Ref: {{ $ref }}</td>
            <td style="text-align:right;">Date: {{ $letterDate }}</td>
        </tr></table>

        To<br>
        The Director General,<br>
        Department Of Shipping,<br>
        141-143, Motijheel, Dhaka-1000

        <div class="sub">SUB: REQUEST FOR SIGN ON UNDER MENTIONED BANGLADESHI OFFICER &amp; IMMIGRATION LETTER.</div>

        <div>Dear Sir,</div>
        <p>We shall be highly pleased if you kindly sign on the under mentioned officer to join below named vessel
        <b>{{ optional($vessel)->vessel_name ?: '[vessel name]' }}</b> in <b>{{ $placeOfJoining ?: '[location]' }}</b>
        Voyage on <b>{{ $joiningDate ?: '[joining date]' }}</b> or any subsequent day.</p>

        <div>The details of candidate as follows:</div>
        <table class="grid">
            <tr>
                <th>SL NO</th><th>Name of Officer/Crew</th><th>CDC No</th><th>Passport No</th>
                <th>Date of issue</th><th>DOB</th><th>Rank</th><th>Salary</th>
            </tr>
            <tr>
                <td>01</td>
                <td>{{ optional($crew)->name }}</td>
                <td>{{ optional($crew)->cdc_no }}</td>
                <td>{{ optional($crew)->passport_no }}</td>
                <td>{{ $passportIssue }}</td>
                <td>{{ optional(optional($crew)->date_of_birth)->format('d-M-Y') }}</td>
                <td>{{ optional(optional($position)->rank)->rank_name ?: optional(optional($crew)->currentRank)->rank_name }}</td>
                <td>{{ $salary }}</td>
            </tr>
        </table>

        <div style="font-weight:bold; margin-top:4px;">VESSEL PARTICULARS:</div>
        <table class="grid">
            <tr><th>NAME OF VESSEL</th><th>FLAG</th><th>IMO NO</th><th>GRT</th><th>DWT</th></tr>
            <tr>
                <td>{{ optional($vessel)->vessel_name }}</td>
                <td>{{ optional($vessel)->flag }}</td>
                <td>{{ optional($vessel)->imo }}</td>
                <td>{{ optional($vessel)->grt }}</td>
                <td>{{ optional($vessel)->dwt }}</td>
            </tr>
        </table>

        <div class="kv">(A) Principal Name &amp; Address: {{ optional($principal)->name }}{{ optional($principal)->address ? ', '.$principal->address : '' }}</div>
        <div class="kv">(B) PLACE OF JOINING: {{ $placeOfJoining }}</div>
        <div class="kv">(C) EXPECTED DATE OF JOINING: {{ $joiningDate }}</div>

        <p>We are assuring your good self that the said sign on is genuine and in future if anything found fake/wrong
        regarding the sign on and attached document, we will be fully responsible and liable. We also assure you to
        obey any decision taken by the concerned authority regarding the matter in future.</p>

        <div>Thanking you.</div>
        <div class="sign">
            Yours faithfully,
            <div class="sign-gap"></div>
            <div class="nm">Maruf Muhammad Jahirul Islam</div>
            Proprietor<br>
            Golden Career Ship Management
        </div>
    </div>
</body>
</html>
