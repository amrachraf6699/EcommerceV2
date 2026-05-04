<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تقرير لوحة التحكم</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        h1, h2 { margin: 0 0 10px; }
        .section { margin-top: 24px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid th, .grid td { border: 1px solid #d1d5db; padding: 8px; text-align: right; }
        .cards { width: 100%; border-collapse: separate; border-spacing: 8px; }
        .cards td { border: 1px solid #e5e7eb; padding: 12px; vertical-align: top; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h1>تقرير لوحة التحكم</h1>
    <p class="muted">الفترة: {{ $report['range_label'] }}</p>

    <div class="section">
        <h2>ملخص المؤشرات</h2>
        <table class="cards">
            <tr>
                <td><strong>الإيرادات</strong><br>{{ number_format($report['kpis']['revenue'], 2) }}</td>
                <td><strong>الطلبات المدفوعة</strong><br>{{ number_format($report['kpis']['paid_orders']) }}</td>
                <td><strong>إجمالي الطلبات</strong><br>{{ number_format($report['kpis']['total_orders']) }}</td>
                <td><strong>متوسط قيمة الطلب</strong><br>{{ number_format($report['kpis']['average_order_value'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>العملاء الجدد</strong><br>{{ number_format($report['kpis']['new_customers']) }}</td>
                <td><strong>السلات النشطة</strong><br>{{ number_format($report['kpis']['active_carts']) }}</td>
                <td><strong>معدل التحويل</strong><br>{{ number_format($report['kpis']['cart_to_order_conversion_rate'], 2) }}%</td>
                <td><strong>نسخ منخفضة المخزون</strong><br>{{ number_format($report['kpis']['low_stock_variants']) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>اتجاه المبيعات</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>الفترة</th>
                    <th>الطلبات</th>
                    <th>الإيرادات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['sales_trend']['labels'] as $index => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ $report['sales_trend']['orders'][$index] ?? 0 }}</td>
                        <td>{{ number_format($report['sales_trend']['revenue'][$index] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>ملخص القمع البيعي</h2>
        <table class="grid">
            <tbody>
                <tr><td>السلات المنشأة</td><td>{{ number_format($report['funnel']['carts_created']) }}</td></tr>
                <tr><td>الطلبات المنشأة</td><td>{{ number_format($report['funnel']['orders_created']) }}</td></tr>
                <tr><td>معدل التحويل</td><td>{{ number_format($report['funnel']['conversion_rate'], 2) }}%</td></tr>
                <tr><td>السلات المتروكة</td><td>{{ number_format($report['funnel']['abandoned_carts']) }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>أعلى المنتجات</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>المعيار</th>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>الإيراد</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['top_products_by_quantity'] as $row)
                    <tr>
                        <td>الأعلى حسب الكمية</td>
                        <td>{{ $row['product_name'] }}</td>
                        <td>{{ $row['quantity_sold'] }}</td>
                        <td>{{ number_format($row['revenue'], 2) }}</td>
                    </tr>
                @endforeach
                @foreach ($report['top_products_by_revenue'] as $row)
                    <tr>
                        <td>الأعلى حسب الإيراد</td>
                        <td>{{ $row['product_name'] }}</td>
                        <td>{{ $row['quantity_sold'] }}</td>
                        <td>{{ number_format($row['revenue'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>توزيع حالات الطلبات</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>الحالة</th>
                    <th>العدد</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['order_status_breakdown'] as $row)
                    <tr>
                        <td>{{ $row['status'] }}</td>
                        <td>{{ $row['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>تنبيهات المخزون</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>النسخة</th>
                    <th>المخزون</th>
                    <th>السعر</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['low_stock_variants'] as $row)
                    <tr>
                        <td>{{ $row['product_name'] }}</td>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ $row['stock_quantity'] }}</td>
                        <td>{{ number_format($row['price'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
