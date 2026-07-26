<?php

namespace App\Providers;

use App\Models\BusinessDocument;
use App\Models\CompanyLicense;
use App\Models\CrewDocument;
use App\Models\SalaryLine;
use App\Models\StaffSalary;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Auto-derive document status from expiry date (DM-04).
        CrewDocument::saving(function (CrewDocument $doc) {
            $doc->status = $doc->computeStatus();
        });

        // Recalculate salary-line computed columns from inputs (Appendix B).
        SalaryLine::saving(function (SalaryLine $line) {
            $line->recalculate();
        });

        // Auto-derive company-licence status from expiry date (Module 7).
        CompanyLicense::saving(function (CompanyLicense $license) {
            $license->status = $license->computeStatus();
        });

        BusinessDocument::saving(function (BusinessDocument $doc) {
            $doc->status = $doc->computeStatus();
        });

        StaffSalary::saving(function (StaffSalary $s) {
            $s->recompute();
        });
    }
}
