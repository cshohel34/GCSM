@extends('layouts.app')
@section('title', 'Review CV submission')
@section('actions')<a href="{{ route('intake.index') }}" class="border px-3 py-1.5 rounded text-sm">Back</a>@endsection
@section('content')
<div class="card p-6 max-w-2xl">
    <div class="text-lg font-semibold text-[#1F3864] mb-2">{{ $submission->name }}</div>
    <div class="grid grid-cols-2 gap-2 text-sm mb-4">
        @foreach ([['Rank',$submission->rank_text],['Phone',$submission->mobile],['Email',$submission->email],['DOB',optional($submission->date_of_birth)->toDateString()],['CDC',$submission->cdc_no],['Passport',$submission->passport_no],['SID',$submission->sid_no],['NID',$submission->nid_no],['Father',$submission->father_name],['Mother',$submission->mother_name],['Source',$submission->source]] as [$k,$v])
            <div><span class="text-slate-400">{{ $k }}:</span> {{ $v ?: '—' }}</div>
        @endforeach
        @if($submission->cv_path)<div class="col-span-2"><a href="{{ asset('storage/'.$submission->cv_path) }}" target="_blank" class="text-[#2E74B5] underline">Open uploaded CV</a></div>@endif
    </div>

    @if ($dupes->isNotEmpty())
        <div class="rounded bg-red-50 border border-red-300 text-red-800 px-4 py-3 text-sm mb-4">
            <b>⚠ Duplicate match — do NOT approve as new.</b>
            @foreach ($dupes as $d)
                <div class="mt-1">{{ $d['crew']->name }} ({{ $d['crew']->display_id }}) — matched on {{ $d['reason'] }}
                    <a href="{{ route('crew.show', $d['crew']->id) }}" class="underline">open existing</a></div>
            @endforeach
        </div>
        <form method="POST" action="{{ route('intake.reject', $submission) }}">@csrf<button class="border px-4 py-2 rounded">Reject submission</button></form>
    @else
        <div class="text-sm text-emerald-700 mb-3">✓ No existing profile matched — safe to approve.</div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('intake.approve', $submission) }}">@csrf<button class="bg-[#1F3864] text-white px-4 py-2 rounded">Approve &amp; create profile</button></form>
            <form method="POST" action="{{ route('intake.reject', $submission) }}">@csrf<button class="border px-4 py-2 rounded">Reject</button></form>
        </div>
    @endif
</div>
@endsection
