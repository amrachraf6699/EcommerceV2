<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class DashboardSalesTrendSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly array $report)
    {
    }

    public function array(): array
    {
        $rows = [['??????', '???????', '?????????']];

        foreach ($this->report['sales_trend']['labels'] as $index => $label) {
            $rows[] = [
                $label,
                $this->report['sales_trend']['orders'][$index] ?? 0,
                $this->report['sales_trend']['revenue'][$index] ?? 0,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return '????? ????????';
    }
}
