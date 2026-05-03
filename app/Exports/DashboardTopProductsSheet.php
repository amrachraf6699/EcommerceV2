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
        $rows = [['Metric', 'Product', 'Quantity Sold', 'Revenue']];

        foreach ($this->report['top_products_by_quantity'] as $row) {
            $rows[] = ['Top by Quantity', $row['product_name'], $row['quantity_sold'], $row['revenue']];
        }

        foreach ($this->report['top_products_by_revenue'] as $row) {
            $rows[] = ['Top by Revenue', $row['product_name'], $row['quantity_sold'], $row['revenue']];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Top Products';
    }
}
