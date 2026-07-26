@extends('layouts.app')
@section('title', 'Add Crew')
@section('content')
<div class="max-w-3xl">
    <h1 class="text-xl font-semibold text-slate-800 mb-1">How do you want to add this crew?</h1>
    <p class="text-sm text-slate-500 mb-6">Choose one. You can still edit every field before saving.</p>

    <div class="grid gap-5 md:grid-cols-2">
        <a href="{{ route('crew.create', ['mode' => 'cv']) }}"
           class="block rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-[#2E74B5] hover:shadow transition">
            <div class="text-3xl mb-3">📄</div>
            <div class="font-semibold text-slate-800 mb-1">Upload CV &amp; auto-fill</div>
            <p class="text-sm text-slate-500">Upload a <b>text-based PDF</b> CV. The form fills in automatically —
                name, parents, DOB, CDC, passport, COC, SID, NID, mobile, email and more. Review, then save.</p>
            <span class="inline-block mt-4 text-sm text-white bg-[#2E74B5] px-4 py-1.5 rounded">Upload a CV →</span>
        </a>

        <a href="{{ route('crew.create', ['mode' => 'manual']) }}"
           class="block rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-[#1F3864] hover:shadow transition">
            <div class="text-3xl mb-3">✍️</div>
            <div class="font-semibold text-slate-800 mb-1">Fill the form manually</div>
            <p class="text-sm text-slate-500">No CV, or a scanned/image PDF? Type the details into the form yourself.
                A GCSM ID is generated and the system checks for duplicates before saving.</p>
            <span class="inline-block mt-4 text-sm text-white bg-[#1F3864] px-4 py-1.5 rounded">Manual Fill Up →</span>
        </a>
    </div>

    <a href="{{ route('crew.index') }}" class="inline-block mt-6 text-sm text-slate-500 hover:underline">← Back to crew list</a>
</div>
@endsection
