<?php

namespace App\Http\Controllers;

use App\Models\ChecklistTemplate;
use App\Models\Designation;
use App\Models\MarineAcademy;
use App\Models\MarineDepartment;
use App\Models\Rank;
use App\Models\SignOffReason;
use App\Models\VesselType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Settings → customisable lists (ranks, office-staff designations). */
class ListsController extends Controller
{
    public function index()
    {
        return view('settings.lists', [
            'ranks' => Rank::orderBy('active', 'desc')->orderBy('sort_order')->orderBy('rank_name')->get(),
            'designations' => Designation::orderBy('name')->get(),
            'academies' => MarineAcademy::orderBy('category')->orderBy('name')->get(),
            'departments' => MarineDepartment::orderBy('category')->orderBy('name')->get(),
            'vesselTypes' => VesselType::orderBy('active', 'desc')->orderBy('type_name')->get(),
            'checklistItems' => ChecklistTemplate::orderBy('active', 'desc')->orderBy('sort_order')->orderBy('id')->get(),
            'signOffReasons' => SignOffReason::orderBy('active', 'desc')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    // ---- Sign-off reasons ----
    public function storeSignOffReason(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:191', Rule::unique('sign_off_reasons', 'label')],
            'note_required' => ['nullable', 'boolean'],
        ]);
        $next = (int) SignOffReason::max('sort_order') + 1;
        SignOffReason::create([
            'label' => $data['label'],
            'note_required' => (bool) ($data['note_required'] ?? false),
            'sort_order' => $next,
            'active' => true,
        ]);
        return back()->with('status', 'Sign-off reason added.');
    }

    public function updateSignOffReason(Request $request, SignOffReason $signOffReason)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:191', Rule::unique('sign_off_reasons', 'label')->ignore($signOffReason->id)],
            'note_required' => ['nullable', 'boolean'],
        ]);
        $signOffReason->update([
            'label' => $data['label'],
            'note_required' => (bool) ($data['note_required'] ?? false),
        ]);
        return back()->with('status', 'Sign-off reason updated.');
    }

    public function toggleSignOffReason(SignOffReason $signOffReason)
    {
        $signOffReason->update(['active' => ! $signOffReason->active]);
        return back()->with('status', 'Sign-off reason '.($signOffReason->active ? 'activated' : 'deactivated').'.');
    }

    public function destroySignOffReason(SignOffReason $signOffReason)
    {
        $signOffReason->delete();
        return back()->with('status', 'Sign-off reason removed.');
    }

    // ---- Crew document checklist template ----
    public function storeChecklistItem(Request $request)
    {
        $data = $request->validate(['label' => ['required', 'string', 'max:191']]);
        // A stable, unique code derived from the label (user-added items are manual).
        $base = Str::slug($data['label'], '_') ?: 'item';
        $code = $base;
        $n = 1;
        while (ChecklistTemplate::where('code', $code)->exists()) {
            $code = $base.'_'.(++$n);
        }
        $next = (int) ChecklistTemplate::max('sort_order') + 1;
        ChecklistTemplate::create([
            'code' => $code,
            'label' => $data['label'],
            'match_rule' => null,
            'sort_order' => $next,
            'active' => true,
        ]);
        return back()->with('status', 'Checklist item added — it now appears for every crew.');
    }

    public function updateChecklistItem(Request $request, ChecklistTemplate $checklistItem)
    {
        $data = $request->validate(['label' => ['required', 'string', 'max:191']]);
        $checklistItem->update(['label' => $data['label']]);
        return back()->with('status', 'Checklist item updated.');
    }

    public function toggleChecklistItem(ChecklistTemplate $checklistItem)
    {
        $checklistItem->update(['active' => ! $checklistItem->active]);
        return back()->with('status', 'Checklist item '.($checklistItem->active ? 'activated' : 'deactivated').'.');
    }

    public function destroyChecklistItem(ChecklistTemplate $checklistItem)
    {
        $checklistItem->delete();
        return back()->with('status', 'Checklist item removed from every crew.');
    }

    // ---- Vessel types ----
    public function storeVesselType(Request $request)
    {
        $data = $request->validate(['type_name' => ['required', 'string', 'max:120', Rule::unique('vessel_types', 'type_name')]]);
        VesselType::create($data + ['active' => true]);
        return back()->with('status', 'Vessel type added.');
    }

    public function updateVesselType(Request $request, VesselType $vesselType)
    {
        $data = $request->validate(['type_name' => ['required', 'string', 'max:120', Rule::unique('vessel_types', 'type_name')->ignore($vesselType->id)]]);
        $vesselType->update($data);
        return back()->with('status', 'Vessel type updated.');
    }

    public function toggleVesselType(VesselType $vesselType)
    {
        $vesselType->update(['active' => ! $vesselType->active]);
        return back()->with('status', 'Vessel type '.($vesselType->active ? 'activated' : 'deactivated').'.');
    }

    // ---- Marine academies / institutes ----
    public function storeAcademy(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('marine_academies', 'name')],
            'category' => ['nullable', Rule::in(['Govt.', 'Private'])],
        ]);
        MarineAcademy::create($data + ['active' => true]);
        return back()->with('status', 'Marine academy added.');
    }

    public function updateAcademy(Request $request, MarineAcademy $academy)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('marine_academies', 'name')->ignore($academy->id)],
            'category' => ['nullable', Rule::in(['Govt.', 'Private'])],
        ]);
        $academy->update($data);
        return back()->with('status', 'Marine academy updated.');
    }

    public function toggleAcademy(MarineAcademy $academy)
    {
        $academy->update(['active' => ! $academy->active]);
        return back()->with('status', 'Marine academy '.($academy->active ? 'activated' : 'deactivated').'.');
    }

    // ---- Marine education departments ----
    public function storeDepartment(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('marine_departments', 'name')],
            'category' => ['nullable', Rule::in(['Cadet Course', 'Rating Course'])],
        ]);
        MarineDepartment::create($data + ['active' => true]);
        return back()->with('status', 'Department added.');
    }

    public function updateDepartment(Request $request, MarineDepartment $department)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('marine_departments', 'name')->ignore($department->id)],
            'category' => ['nullable', Rule::in(['Cadet Course', 'Rating Course'])],
        ]);
        $department->update($data);
        return back()->with('status', 'Department updated.');
    }

    public function toggleDepartment(MarineDepartment $department)
    {
        $department->update(['active' => ! $department->active]);
        return back()->with('status', 'Department '.($department->active ? 'activated' : 'deactivated').'.');
    }

    // ---- Ranks ----
    public function storeRank(Request $request)
    {
        $data = $request->validate([
            'rank_name' => ['required', 'string', 'max:120', Rule::unique('ranks', 'rank_name')],
            'department' => ['nullable', 'string', 'max:60'],
        ]);
        Rank::create($data + ['active' => true]);
        return back()->with('status', 'Rank added.');
    }

    public function updateRank(Request $request, Rank $rank)
    {
        $data = $request->validate([
            'rank_name' => ['required', 'string', 'max:120', Rule::unique('ranks', 'rank_name')->ignore($rank->id)],
            'department' => ['nullable', 'string', 'max:60'],
        ]);
        $rank->update($data);
        return back()->with('status', 'Rank updated.');
    }

    public function toggleRank(Rank $rank)
    {
        $rank->update(['active' => ! $rank->active]);
        return back()->with('status', 'Rank '.($rank->active ? 'activated' : 'deactivated').'.');
    }

    // ---- Designations ----
    public function storeDesignation(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120', Rule::unique('designations', 'name')]]);
        Designation::create($data + ['active' => true]);
        return back()->with('status', 'Designation added.');
    }

    public function updateDesignation(Request $request, Designation $designation)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120', Rule::unique('designations', 'name')->ignore($designation->id)]]);
        $designation->update($data);
        return back()->with('status', 'Designation updated.');
    }

    public function toggleDesignation(Designation $designation)
    {
        $designation->update(['active' => ! $designation->active]);
        return back()->with('status', 'Designation '.($designation->active ? 'activated' : 'deactivated').'.');
    }
}
