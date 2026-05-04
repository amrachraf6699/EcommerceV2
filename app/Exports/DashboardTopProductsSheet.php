<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class DashboardTopProductsSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly array $report)
    {
    }

    public function array(): array
    {
        $rows = [['???????', '??????', '?????? ???????', '???????']];

        foreach ($this->report['top_products_by_quantity'] as $row) {
            $rows[] = ['?????? ??? ??????', $row['product_name'], $row['quantity_sold'], $row['revenue']];
        }

        foreach ($this->report['top_products_by_revenue'] as $row) {
            $rows[] = ['?????? ??? ???????', $row['product_name'], $row['quantity_sold'], $row['revenue']];
        }

        return $rows;
    }

    public function title(): string
    {
        return '???? ????????';
    }
}
