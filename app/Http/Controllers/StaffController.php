<?php

namespace App\Http\Controllers;

use App\Models\CrewProfile;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->get('user_type'), fn ($q, $v) => $q->where('user_type', $v))
            ->when($request->get('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->get('q'), fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('name')->paginate(20)->withQueryString();
        return view('staff.index', ['users' => $users, 'filters' => $request->only(['user_type', 'status', 'q'])]);
    }

    public function create()
    {
        return view('staff.form', ['staff' => new User(), 'roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateStaff($request, null);
        $data['password'] = Hash::make($request->input('password'));
        $user = User::create($data);
        if ($request->filled('role')) $user->syncRoles([$request->input('role')]);
        return redirect()->route('staff.show', $user)->with('status', ucfirst($user->user_type).' added.');
    }

    public function show(User $staff)
    {
        $payouts = $staff->isPartner()
            ? \App\Models\PartnerPayout::with('placement.principal')->where('partner_id', $staff->id)->latest()->get()
            : collect();

        $stats = [
            'crew_created' => CrewProfile::where('created_by', $staff->id)->count(),
            'placements' => Placement::where('arranged_by', $staff->id)->count(),
            'onboard' => Placement::where('arranged_by', $staff->id)->where('status', 'onboard')->count(),
        ];
        return view('staff.show', compact('staff', 'payouts', 'stats'));
    }

    public function edit(User $staff)
    {
        return view('staff.form', ['staff' => $staff, 'roles' => Role::orderBy('name')->get()]);
    }

    public function update(Request $request, User $staff)
    {
        $data = $this->validateStaff($request, $staff->id);
        if ($request->filled('password')) $data['password'] = Hash::make($request->input('password'));
        $staff->update($data);
        if ($request->filled('role')) $staff->syncRoles([$request->input('role')]);
        return redirect()->route('staff.show', $staff)->with('status', 'Updated.');
    }

    protected function validateStaff(Request $request, ?int $id): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:6'],
            'user_type' => ['required', Rule::in(['staff', 'partner'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'office' => ['nullable', Rule::in(['Dhaka', 'Chittagong'])],
            'role' => ['nullable', 'string'],
            // Per-partner fee-share (TM-04)
            'share_service_charge_pct' => ['nullable', 'numeric', 'between:0,100'],
            'share_agency_fee_pct' => ['nullable', 'numeric', 'between:0,100'],
            'share_net_profit_pct' => ['nullable', 'numeric', 'between:0,100'],
            'share_notes' => ['nullable', 'string'],
        ]);
    }
}
