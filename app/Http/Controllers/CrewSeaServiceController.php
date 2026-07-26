<?php

namespace App\Http\Controllers;

use App\Models\CrewProfile;
use App\Models\CrewSeaService;
use Illuminate\Http\Request;

class CrewSeaServiceController extends Controller
{

    public function store(Request $request, CrewProfile $crew)
    {
        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:191'],
            'vessel_name' => ['nullable', 'string', 'max:191'],
            'imo_no' => ['nullable', 'string', 'max:60'],
            'vessel_type' => ['nullable', 'string', 'max:120'],
            'grt' => ['nullable', 'string', 'max:60'],
            'dwt' => ['nullable', 'string', 'max:60'],
            'engine_type' => ['nullable', 'string', 'max:120'],
            'bhp' => ['nullable', 'string', 'max:60'],
            'flag' => ['nullable', 'string', 'max:120'],
            'trading_area' => ['nullable', 'string', 'max:120'],
            'rank' => ['nullable', 'string', 'max:120'],
            'owner' => ['nullable', 'string', 'max:191'],
            'sign_on' => ['nullable', 'date'],
            'sign_off' => ['nullable', 'date', 'after_or_equal:sign_on'],
            'reason_sign_off' => ['nullable', 'string', 'max:191'],
        ]);
        if (! empty($data['sign_on']) && ! empty($data['sign_off'])) {
            $data['duration_days'] = \Carbon\Carbon::parse($data['sign_on'])
                ->diffInDays(\Carbon\Carbon::parse($data['sign_off']));
        }
        $data['source'] = 'manual';
        $row = $crew->seaServices()->create($data);
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'html' => view('crew.partials.sea_row', ['crew' => $crew, 'row' => $row])->render()]);
        }
        return back()->with('status', 'Sea service added.');
    }

    public function destroy(Request $request, CrewProfile $crew, CrewSeaService $seaService)
    {
        abort_unless($seaService->crew_profile_id === $crew->id, 404);
        $seaService->delete();
        if ($request->wantsJson()) return response()->json(['ok' => true]);
        return back()->with('status', 'Sea service removed.');
    }
}
