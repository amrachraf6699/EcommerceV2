<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class DashboardLowStockSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly array $report)
    {
    }

    public function array(): array
    {
        $rows = [['??????', '??????', '???? ???????', '?????']];

        foreach ($this->report['low_stock_variants'] as $row) {
            $rows[] = [$row['product_name'], $row['name'], $row['stock_quantity'], $row['price']];
        }

        return $rows;
    }

    public function title(): string
    {
        return '??????? ???????';
    }
}
