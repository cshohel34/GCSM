<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryLine extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'joining_date' => 'date',
        'paid_date' => 'date',
        'is_paid' => 'boolean',
        'is_held' => 'boolean',
        'usd_rate' => 'decimal:4',
        'company_amount' => 'decimal:2',
    ];

    public function sheet() { return $this->belongsTo(SalarySheet::class, 'salary_sheet_id'); }
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
    public function placement() { return $this->belongsTo(Placement::class); }

    /**
     * Recalculate the computed columns from inputs (PRD Appendix B formulas).
     * Called on saving via AppServiceProvider.
     */
    public function recalculate(): void
    {
        $total = max(1, (int) $this->total_days);
        $working = (int) $this->working_days;
        if ($this->deduct_days > 0 && $working === $total) {
            $working = max(0, $total - (int) $this->deduct_days);
        }

        $gross = round(((float) $this->salary_usd) * ($working / $total), 2) + (float) $this->bonus_usd;
        $net = round($gross - (float) $this->transfer_charge_usd, 2);

        $agentGross = (float) $this->agent_fee_usd;
        $agentNet = round($agentGross - (float) $this->agent_fee_charge_usd, 2);

        $this->gross_usd = $gross;
        $this->net_usd = $net;
        $this->net_bdt = round($net * (float) $this->usd_rate, 2);
        $this->agent_gross_usd = $agentGross;
        $this->agent_net_usd = $agentNet;
        $this->agent_net_bdt = round($agentNet * (float) $this->usd_rate, 2);
    }
}
