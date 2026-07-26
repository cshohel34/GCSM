<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class AccountingReportExport implements FromArray, WithTitle
{
    public function __construct(
        protected string $title,
        protected array $meta,      // ['label' => 'value', ...]
        protected array $columns,   // ['Code','Account',...]
        protected array $rows,      // [[...],[...]]
    ) {}

    public function title(): string
    {
        return substr(preg_replace('/[^A-Za-z0-9 ]/', '', $this->title), 0, 28) ?: 'Report';
    }

    public function array(): array
    {
        $out = [['GOLDEN CAREER SHIP MANAGEMENT'], [$this->title]];
        foreach ($this->meta as $k => $v) {
            $out[] = [$k.': '.$v];
        }
        $out[] = [];
        $out[] = $this->columns;
        foreach ($this->rows as $r) {
            $out[] = array_values($r);
        }
        return $out;
    }
}
