<?php

namespace App\Exports;

use App\Models\SalarySheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalarySheetExport implements FromArray, WithTitle
{
    public function __construct(protected SalarySheet $sheet) {}

    public function title(): string { return $this->sheet->month; }

    public function array(): array
    {
        $s = $this->sheet;
        $rows = [
            ['GOLDEN CAREER SHIP MANAGEMENT — Crew Salary Sheet ('.optional($s->principal)->name.')'],
            ['Ref: '.$s->reference, 'Month: '.$s->month, 'USD Rate: '.$s->usd_rate, 'Status: '.ucfirst($s->status)],
            [],
            ['SL','Crew Name','Ship','Rank','Month','Salary(USD)','USD Rate','Bonus','Joining',
             'Total Days','Working','Deduct','Gross(USD)','Net(USD)','Net(BDT)',
             'Agent Fee(USD)','Agent Gross','Agent Net(USD)','Agent Net(BDT)','Remarks'],
        ];
        foreach ($s->lines as $l) {
            $rows[] = [
                $l->sl_no, $l->crew_name, $l->ship_name, $l->rank, $l->month,
                $l->salary_usd, $l->usd_rate, $l->bonus_usd, optional($l->joining_date)->toDateString(),
                $l->total_days, $l->working_days, $l->deduct_days,
                $l->gross_usd, $l->net_usd, $l->net_bdt,
                $l->agent_fee_usd, $l->agent_gross_usd, $l->agent_net_usd, $l->agent_net_bdt,
                $l->remarks,
            ];
        }
        $rows[] = [];
        $rows[] = ['', '', '', '', '', '', '', '', '', '', '', 'TOTAL',
            $s->lines->sum('gross_usd'), $s->lines->sum('net_usd'), $s->lines->sum('net_bdt'),
            '', '', $s->lines->sum('agent_net_usd'), $s->lines->sum('agent_net_bdt'), ''];
        return $rows;
    }
}
