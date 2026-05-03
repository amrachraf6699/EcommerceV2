<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AdminDashboardReportExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(private readonly array $report)
    {
    }

    public function sheets(): array
    {
        return [
            new DashboardSummarySheet($this->report),
            new DashboardSalesTrendSheet($this->report),
            new DashboardTopProductsSheet($this->report),
            new DashboardOrderStatusSheet($this->report),
            new DashboardLowStockSheet($this->report),
        ];
    }
}
