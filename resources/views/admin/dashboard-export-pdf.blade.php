<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Dashboard Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; direction: ltr; }
        h1, h2 { margin: 0 0 10px; }
        .section { margin-top: 24px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid th, .grid td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        .cards { width: 100%; border-collapse: separate; border-spacing: 8px; }
        .cards td { border: 1px solid #e5e7eb; padding: 12px; vertical-align: top; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    @php
        $pdfText = static fn (mixed $value): string => \App\Support\PdfArabic::text($value);
    @endphp

    <h1>Dashboard Report</h1>
    <p class="muted">Range: {{ $report['range'] }}</p>

    <div class="section">
        <h2>KPI Summary</h2>
        <table class="cards">
            <tr>
                <td><strong>Revenue</strong><br>{{ number_format($report['kpis']['revenue'], 2) }}</td>
                <td><strong>Paid Orders</strong><br>{{ number_format($report['kpis']['paid_orders']) }}</td>
                <td><strong>Total Orders</strong><br>{{ number_format($report['kpis']['total_orders']) }}</td>
                <td><strong>Average Order Value</strong><br>{{ number_format($report['kpis']['average_order_value'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>New Customers</strong><br>{{ number_format($report['kpis']['new_customers']) }}</td>
                <td><strong>Active Carts</strong><br>{{ number_format($report['kpis']['active_carts']) }}</td>
                <td><strong>Conversion Rate</strong><br>{{ number_format($report['kpis']['cart_to_order_conversion_rate'], 2) }}%</td>
                <td><strong>Low Stock Variants</strong><br>{{ number_format($report['kpis']['low_stock_variants']) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Sales Trend</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Orders</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['sales_trend']['labels'] as $index => $label)
                    <tr>
                        <td>Period {{ $index + 1 }}</td>
                        <td>{{ $report['sales_trend']['orders'][$index] ?? 0 }}</td>
                        <td>{{ number_format($report['sales_trend']['revenue'][$index] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Sales Funnel</h2>
        <table class="grid">
            <tbody>
                <tr><td>Carts Created</td><td>{{ number_format($report['funnel']['carts_created']) }}</td></tr>
                <tr><td>Orders Created</td><td>{{ number_format($report['funnel']['orders_created']) }}</td></tr>
                <tr><td>Conversion Rate</td><td>{{ number_format($report['funnel']['conversion_rate'], 2) }}%</td></tr>
                <tr><td>Abandoned Carts</td><td>{{ number_format($report['funnel']['abandoned_carts']) }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Top Products</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['top_products_by_quantity'] as $row)
                    <tr>
                        <td>Top by Quantity</td>
                        <td>{{ $pdfText($row['product_name']) }}</td>
                        <td>{{ $row['quantity_sold'] }}</td>
                        <td>{{ number_format($row['revenue'], 2) }}</td>
                    </tr>
                @endforeach
                @foreach ($report['top_products_by_revenue'] as $row)
                    <tr>
                        <td>Top by Revenue</td>
                        <td>{{ $pdfText($row['product_name']) }}</td>
                        <td>{{ $row['quantity_sold'] }}</td>
                        <td>{{ number_format($row['revenue'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Order Status Breakdown</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['order_status_breakdown'] as $row)
                    <tr>
                        <td>{{ $pdfText($row['status']) }}</td>
                        <td>{{ $row['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Inventory Alerts</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Stock</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['low_stock_variants'] as $row)
                    <tr>
                        <td>{{ $pdfText($row['product_name']) }}</td>
                        <td>{{ $pdfText($row['name']) }}</td>
                        <td>{{ $row['stock_quantity'] }}</td>
                        <td>{{ number_format($row['price'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
