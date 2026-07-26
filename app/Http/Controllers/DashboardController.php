<?php

namespace App\Http\Controllers;

use App\Models\CrewDocument;
use App\Models\CrewProfile;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'crew_total' => CrewProfile::count(),
            'crew_available' => CrewProfile::where('availability', 'available')->count(),
            'docs_expiring' => CrewDocument::where('status', 'expiring')->count(),
            'docs_expired' => CrewDocument::where('status', 'expired')->count(),
        ];
        $expiring = CrewDocument::with('crewProfile')
            ->whereIn('status', ['expiring', 'expired'])
            ->orderBy('expiry_date')->limit(15)->get();

        return view('dashboard.index', compact('stats', 'expiring'));
    }
}
