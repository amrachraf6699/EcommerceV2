<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class DashboardOrderStatusSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly array $report)
    {
    }

    public function array(): array
    {
        $rows = [['??????', '?????']];

        foreach ($this->report['order_status_breakdown'] as $row) {
            $rows[] = [$row['status'], $row['count']];
        }

        return $rows;
    }

    public function title(): string
    {
        return '????? ???????';
    }
}
