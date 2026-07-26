<?php

namespace App\Http\Controllers;

use App\Exports\CrewCvExport;
use App\Models\CrewProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class CvExportController extends Controller
{
    public function pdf(CrewProfile $crew)
    {
        $crew->load(['currentRank', 'rankApplied', 'seaServices', 'courses', 'documents', 'maritimeEducations', 'academics']);
        $pdf = Pdf::loadView('pdf.cv', ['crew' => $crew])->setPaper('a4');
        return $pdf->download('CV-'.$crew->display_id.'.pdf');
    }

    public function excel(CrewProfile $crew)
    {
        return Excel::download(new CrewCvExport($crew), 'CV-'.$crew->display_id.'.xlsx');
    }
}
