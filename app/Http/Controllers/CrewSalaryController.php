<?php

namespace App\Http\Controllers;

use App\Models\CrewProfile;

class CrewSalaryController extends Controller
{
    /** Lifetime salary history for a crew (SM-06): paid vs pending months. */
    public function index(CrewProfile $crew)
    {
        $lines = $crew->salaryLines()
            ->with('sheet.principal')
            ->join('salary_sheets', 'salary_sheets.id', '=', 'salary_lines.salary_sheet_id')
            ->orderByDesc('salary_sheets.created_at')
            ->select('salary_lines.*')
            ->paginate(50);

        $totalNet = $crew->salaryLines()->sum('net_bdt');
        $paidNet = $crew->salaryLines()->where('is_paid', true)->sum('net_bdt');

        return view('salary.crew', [
            'crew' => $crew,
            'lines' => $lines,
            'totalNet' => $totalNet,
            'paidNet' => $paidNet,
        ]);
    }
}
