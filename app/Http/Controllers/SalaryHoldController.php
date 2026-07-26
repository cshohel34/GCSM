<?php

namespace App\Http\Controllers;

use App\Models\SalaryHold;
use App\Models\SalaryLine;
use App\Models\SalarySheet;
use Illuminate\Http\Request;

class SalaryHoldController extends Controller
{
    public function hold(Request $request, SalarySheet $salary, SalaryLine $line)
    {
        abort_unless($line->salary_sheet_id === $salary->id, 404);
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $line->update(['is_held' => true]);
        SalaryHold::create([
            'crew_profile_id' => $line->crew_profile_id,
            'salary_line_id' => $line->id,
            'month' => $line->month,
            'reason' => $data['reason'] ?? null,
            'status' => 'held',
            'held_by' => $request->user()->id,
        ]);
        return back()->with('status', 'Salary held for this crew/month.');
    }

    public function release(SalarySheet $salary, SalaryLine $line)
    {
        abort_unless($line->salary_sheet_id === $salary->id, 404);
        $line->update(['is_held' => false]);
        SalaryHold::where('salary_line_id', $line->id)->where('status', 'held')
            ->update(['status' => 'released', 'released_at' => now()]);
        return back()->with('status', 'Salary hold released.');
    }
}
