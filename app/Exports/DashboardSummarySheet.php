<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class DashboardSummarySheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly array $report)
    {
    }

    public function array(): array
    {
        $kpis = $this->report['kpis'];
        $funnel = $this->report['funnel'];
        $config = $this->report['analytics_config_status'];

        return [
            ['Range', $this->report['range_label']],
            ['Revenue', $kpis['revenue']],
            ['Paid Orders', $kpis['paid_orders']],
            ['Total Orders', $kpis['total_orders']],
            ['Average Order Value', $kpis['average_order_value']],
            ['New Customers', $kpis['new_customers']],
            ['Active Carts', $kpis['active_carts']],
            ['Cart to Order Conversion Rate', $kpis['cart_to_order_conversion_rate']],
            ['Low Stock Variants', $kpis['low_stock_variants']],
            ['Carts Created', $funnel['carts_created']],
            ['Orders Created', $funnel['orders_created']],
            ['Abandoned Carts', $funnel['abandoned_carts']],
            ['Analytics Settings Configured', $config['configured_count'].' / '.$config['total_count']],
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }
}
