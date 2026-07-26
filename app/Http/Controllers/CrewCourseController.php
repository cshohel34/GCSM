<?php

namespace App\Http\Controllers;

use App\Models\CourseCatalogue;
use App\Models\CrewProfile;
use Illuminate\Http\Request;

class CrewCourseController extends Controller
{

    public function store(Request $request, CrewProfile $crew)
    {
        $data = $request->validate([
            'course_catalogue_id' => ['nullable', 'exists:course_catalogue,id'],
            'course_name' => ['required', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:191'],
            'capacity' => ['nullable', 'string', 'max:191'],
            'completion_date' => ['nullable', 'date'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'issuer' => ['nullable', 'string', 'max:191'],
            'issuing_authority' => ['nullable', 'string', 'max:191'],
            'dos_registration_no' => ['nullable', 'string', 'max:120'],
            'certificate_no' => ['nullable', 'string', 'max:120'],
            'scan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
        ]);
        if (empty($data['category'])) {
            $data['category'] = $data['course_name'];
        }
        // Match a typed/searched certificate name back to the catalogue when possible.
        if (empty($data['course_catalogue_id'])) {
            $match = CourseCatalogue::where('course_name', $data['course_name'])->first();
            if ($match) $data['course_catalogue_id'] = $match->id;
        }
        if ($cat = ($data['course_catalogue_id'] ?? null)) {
            $data['course_code'] = optional(CourseCatalogue::find($cat))->code;
        }
        if ($request->hasFile('scan')) {
            $data['scan_path'] = $request->file('scan')->store('crew/certificates', 'public');
        }
        unset($data['scan']);
        $data['source'] = 'manual'; // OMA rows are created by the sync service
        $row = $crew->courses()->create($data);
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'html' => view('crew.partials.course_row', ['crew' => $crew, 'row' => $row])->render()]);
        }
        return back()->with('status', 'Certificate added.');
    }

    public function destroy(Request $request, CrewProfile $crew, \App\Models\CrewCourse $course)
    {
        abort_unless($course->crew_profile_id === $crew->id, 404);
        $course->delete();
        if ($request->wantsJson()) return response()->json(['ok' => true]);
        return back()->with('status', 'Certificate removed.');
    }
}
