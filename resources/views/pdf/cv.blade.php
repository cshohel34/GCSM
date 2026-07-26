<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans, sans-serif;font-size:11px;color:#111}
.title{text-align:center;font-size:18px;font-weight:bold;color:#1F3864;padding:6px}
h2{background:#1F3864;color:#fff;font-size:12px;padding:4px 8px;margin:10px 0 0}
table{width:100%;border-collapse:collapse}
td,th{border:1px solid #999;padding:4px 6px;vertical-align:top;font-size:10px}
th{background:#1F3864;color:#fff;font-size:9px}
td.lbl{background:#eef2f7;font-weight:bold;width:22%}
</style></head><body>
@php
  $r = optional($crew->rankApplied)->rank_name ?: optional($crew->currentRank)->rank_name;
@endphp
<div class="title">GOLDEN CAREER SHIP MANAGEMENT</div>

<h2>Personal Details</h2>
<table>
  <tr><td class="lbl">Name</td><td>{{ $crew->name }}</td><td class="lbl">Date of Birth</td><td>{{ optional($crew->date_of_birth)->toDateString() }}</td><td class="lbl">Rank Applied for</td><td>{{ $r }}</td></tr>
  <tr><td class="lbl">Contact Number</td><td>{{ $crew->mobile }}</td><td class="lbl">Place of Birth</td><td>{{ $crew->place_of_birth }}</td><td class="lbl">Marital Status</td><td>{{ $crew->marital_status }}</td></tr>
  <tr><td class="lbl">Address</td><td colspan="3">{{ $crew->present_address ?: $crew->permanent_address }}</td><td class="lbl">Shoe Size</td><td>{{ $crew->shoe_size }}</td></tr>
  <tr><td class="lbl">Father's Name</td><td>{{ $crew->father_name }}</td><td class="lbl">Height (CM)</td><td>{{ $crew->height_cm }}</td><td class="lbl">Blood Group</td><td>{{ $crew->blood_group }}</td></tr>
  <tr><td class="lbl">Mother's Name</td><td>{{ $crew->mother_name }}</td><td class="lbl">Weight (KG)</td><td>{{ $crew->weight_kg }}</td><td class="lbl">Emergency Contact</td><td>{{ $crew->emergency_contact }}</td></tr>
  <tr><td class="lbl">CDC No</td><td>{{ $crew->cdc_no }}</td><td class="lbl">Passport No</td><td>{{ $crew->passport_no }}</td><td class="lbl">SID No</td><td>{{ $crew->sid_no }}</td></tr>
</table>

<h2>Maritime Education Details</h2>
<table>
  <thead><tr><th>Name of Maritime Institute</th><th>Department</th><th>Year of Graduation</th></tr></thead>
  <tbody>
  @forelse ($crew->maritimeEducations as $e)
    <tr><td>{{ $e->institute }}</td><td>{{ $e->department }}</td><td>{{ $e->year_of_graduation }}</td></tr>
  @empty <tr><td colspan="3" style="text-align:center;color:#999">None recorded</td></tr> @endforelse
  </tbody>
</table>

<h2>Educational Qualification</h2>
<table>
  <thead><tr><th>Description</th><th>Board</th><th>Group</th><th>Passing Year</th><th>GPA</th></tr></thead>
  <tbody>
  @forelse ($crew->academics as $a)
    <tr><td>{{ $a->description }}</td><td>{{ $a->board }}</td><td>{{ $a->group }}</td><td>{{ $a->passing_year }}</td><td>{{ $a->gpa }}</td></tr>
  @empty <tr><td colspan="5" style="text-align:center;color:#999">None recorded</td></tr> @endforelse
  </tbody>
</table>

<h2>English Level (Excellent, Very Good, Good, Fair, Poor)</h2>
<table><thead><tr><th>Listening</th><th>Spoken</th><th>Reading</th><th>Writing</th></tr></thead>
<tbody><tr><td>{{ $crew->english_listening }}</td><td>{{ $crew->english_speaking }}</td><td>{{ $crew->english_reading }}</td><td>{{ $crew->english_writing }}</td></tr></tbody></table>

<h2>Certificates &amp; Travelling Documents Details</h2>
<table>
  <thead><tr><th>Category</th><th>Capacity</th><th>Certificate Number</th><th>Date of Issue</th><th>Date of Expiry</th><th>Issuer</th><th>Issuing Authority</th></tr></thead>
  <tbody>
  @forelse ($crew->documents as $d)
    <tr><td>{{ $d->doc_type }}</td><td>{{ $d->grade }}</td><td>{{ $d->number }}</td><td>{{ optional($d->issue_date)->toDateString() }}</td><td>{{ optional($d->expiry_date)->toDateString() }}</td><td>{{ $d->place_of_issue }}</td><td>{{ $d->issuing_authority }}</td></tr>
  @empty @endforelse
  @foreach ($crew->courses as $c)
    <tr><td>{{ $c->category ?: $c->course_name }}</td><td>{{ $c->capacity }}</td><td>{{ $c->certificate_no }}</td><td>{{ optional($c->issue_date)->toDateString() }}</td><td>{{ optional($c->expiry_date)->toDateString() }}</td><td>{{ $c->issuer ?: $c->issuing_authority }}</td><td>{{ $c->issuing_authority }}</td></tr>
  @endforeach
  @if ($crew->documents->isEmpty() && $crew->courses->isEmpty())<tr><td colspan="7" style="text-align:center;color:#999">None recorded</td></tr>@endif
  </tbody>
</table>

<h2>Sea Service Details</h2>
<table>
  <thead><tr><th>Company</th><th>Vessel</th><th>VSL.Type</th><th>GRT</th><th>Engine</th><th>BHP</th><th>Flag</th><th>Trading Area</th><th>Rank</th><th>Sign-On</th><th>Sign-Off</th><th>Days</th><th>Reason</th></tr></thead>
  <tbody>
  @forelse ($crew->seaServices as $s)
    <tr><td>{{ $s->company_name }}</td><td>{{ $s->vessel_name }}</td><td>{{ $s->vessel_type }}</td><td>{{ $s->grt }}</td><td>{{ $s->engine_type }}</td><td>{{ $s->bhp }}</td><td>{{ $s->flag }}</td><td>{{ $s->trading_area }}</td><td>{{ $s->rank }}</td><td>{{ optional($s->sign_on)->toDateString() }}</td><td>{{ optional($s->sign_off)->toDateString() }}</td><td>{{ $s->duration_days }}</td><td>{{ $s->reason_sign_off }}</td></tr>
  @empty <tr><td colspan="13" style="text-align:center;color:#999">No sea service recorded</td></tr> @endforelse
  </tbody>
</table>
</body></html>
