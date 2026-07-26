<?php

namespace App\Http\Controllers;

use App\Models\StaffSalary;
use App\Models\User;
use Illuminate\Http\Request;

class StaffSalaryController extends Controller
{
    public function index(Request $request)
    {
        $month = strtoupper($request->get('month', now()->format('M-y')));
        $rows = StaffSalary::with('user')->where('month', $month)->get();
        return view('staff.payroll', ['rows' => $rows, 'month' => $month]);
    }

    public function generate(Request $request)
    {
        $month = strtoupper($request->validate(['month' => ['required', 'string', 'max:20']])['month']);
        $staff = User::where('user_type', 'staff')->where('status', 'active')->get();
        foreach ($staff as $u) {
            StaffSalary::firstOrCreate(['user_id' => $u->id, 'month' => $month], ['status' => 'pending']);
        }
        return redirect()->route('staff.payroll.index', ['month' => $month])->with('status', 'Payroll generated for '.$month.'.');
    }

    public function update(Request $request, StaffSalary $salary)
    {
        $data = $request->validate([
            'basic' => ['required', 'numeric', 'min:0'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
        ]);
        $salary->fill($data)->save();
        return back()->with('status', 'Saved.');
    }

    public function pay(StaffSalary $salary)
    {
        $salary->update(['status' => 'paid', 'paid_date' => now()]);
        return back()->with('status', 'Marked paid.');
    }
}
