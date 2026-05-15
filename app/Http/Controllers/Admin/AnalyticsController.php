<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminAnalyticsReportExport;
use App\Http\Controllers\Controller;
use App\Support\AdminAnalyticsReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AdminAnalyticsReportService $reportService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $report = $this->buildReport($request);
        $analyticsNavigation = $this->analyticsNavigation();
        $selectedCategory = $this->validCategory($request->query('category'), $analyticsNavigation);
        $selectedReport = $this->validReport($request->query('report'), $analyticsNavigation);
        $selectedCategory = $selectedReport
            ? $analyticsNavigation['reports'][$selectedReport]['category']
            : $selectedCategory;
        $selectedSection = $selectedReport
            ? $analyticsNavigation['reports'][$selectedReport]['section']
            : $this->validSection($request->query('section'));

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('admin.analytics.partials.content', [
                    'report' => $report,
                    'sectionLabels' => $this->reportService->sectionLabels(),
                    'analyticsNavigation' => $analyticsNavigation,
                    'selectedCategory' => $selectedCategory,
                    'selectedReport' => $selectedReport,
                    'selectedSection' => $selectedSection,
                ])->render(),
            ]);
        }

        return view('admin.analytics.index', [
            'report' => $report,
            'sectionLabels' => $this->reportService->sectionLabels(),
            'analyticsNavigation' => $analyticsNavigation,
            'selectedCategory' => $selectedCategory,
            'selectedReport' => $selectedReport,
            'selectedSection' => $selectedSection,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $report = $this->buildReport($request);
        $section = $this->validSection($request->query('section'));

        return Pdf::loadView('admin.analytics.export-pdf', [
            'report' => $report,
            'section' => $section,
            'tables' => $this->reportService->exportTables($report, $section),
        ])->setPaper('a4', 'portrait')
            ->download('analytics-report-' . ($section ?: 'full') . '-' . $report['range'] . '.pdf');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $report = $this->buildReport($request);
        $section = $this->validSection($request->query('section'));

        return Excel::download(
            new AdminAnalyticsReportExport($report, $section, $this->reportService),
            'analytics-report-' . ($section ?: 'full') . '-' . $report['range'] . '.xlsx'
        );
    }

    private function buildReport(Request $request): array
    {
        return $this->reportService->build(
            $request->query('range'),
            $request->query('start_date'),
            $request->query('end_date')
        );
    }

    private function validSection(mixed $section): ?string
    {
        $section = is_string($section) ? $section : null;

        return $section && array_key_exists($section, $this->reportService->sectionLabels())
            ? $section
            : null;
    }

    /**
     * @return array{categories: array<string, array<string, mixed>>, reports: array<string, array<string, string>>}
     */
    private function analyticsNavigation(): array
    {
        $reports = [
            'executive-kpis' => ['category' => 'overview', 'section' => 'summary', 'title' => 'المؤشرات التنفيذية', 'copy' => 'الإيرادات، الطلبات، التحويل، الخصومات، والشحن.'],
            'revenue-trend' => ['category' => 'sales', 'section' => 'sales', 'title' => 'اتجاه الإيرادات', 'copy' => 'تحليل تطور الإيرادات والطلبات ومتوسط الطلب.'],
            'payment-statuses' => ['category' => 'sales', 'section' => 'sales', 'title' => 'حالات الدفع', 'copy' => 'توزيع الطلبات حسب حالة الدفع.'],
            'fulfillment-statuses' => ['category' => 'sales', 'section' => 'sales', 'title' => 'حالات التجهيز', 'copy' => 'قيمة وعدد الطلبات حسب التجهيز والشحن.'],
            'order-statuses' => ['category' => 'sales', 'section' => 'sales', 'title' => 'حالات الطلبات', 'copy' => 'توزيع الطلبات حسب حالتها التشغيلية.'],
            'currency-breakdown' => ['category' => 'sales', 'section' => 'sales', 'title' => 'العملات', 'copy' => 'عدد وقيمة الطلبات حسب العملة المخزنة في الطلب.'],
            'payment-providers' => ['category' => 'sales', 'section' => 'sales', 'title' => 'مزودات الدفع', 'copy' => 'تحليل الطلبات حسب مزود الدفع مثل Tap.'],
            'hourly-sales' => ['category' => 'sales', 'section' => 'sales', 'title' => 'ساعات البيع', 'copy' => 'أفضل ساعات اليوم من حيث الطلبات والإيرادات.'],
            'weekday-sales' => ['category' => 'sales', 'section' => 'sales', 'title' => 'أيام الأسبوع', 'copy' => 'أداء المبيعات حسب أيام الأسبوع.'],
            'top-products-quantity' => ['category' => 'catalog', 'section' => 'products', 'title' => 'أعلى المنتجات كمية', 'copy' => 'المنتجات الأكثر مبيعاً حسب عدد القطع.'],
            'top-products-revenue' => ['category' => 'catalog', 'section' => 'products', 'title' => 'أعلى المنتجات إيراداً', 'copy' => 'المنتجات التي تحقق أعلى قيمة مبيعات.'],
            'top-variants' => ['category' => 'catalog', 'section' => 'products', 'title' => 'أعلى النسخ مبيعاً', 'copy' => 'تحليل الأداء حسب المقاس واللون والنسخة.'],
            'category-performance' => ['category' => 'catalog', 'section' => 'products', 'title' => 'أداء الأقسام', 'copy' => 'ترتيب الأقسام حسب الإيراد والكمية.'],
            'inventory-value' => ['category' => 'inventory', 'section' => 'inventory', 'title' => 'قيمة المخزون', 'copy' => 'إجمالي الوحدات وقيمة المخزون بسعر البيع الحالي.'],
            'inventory-alerts' => ['category' => 'inventory', 'section' => 'inventory', 'title' => 'تنبيهات المخزون', 'copy' => 'النسخ قليلة المخزون والمنتهية.'],
            'sold-out' => ['category' => 'inventory', 'section' => 'inventory', 'title' => 'المنتجات المنتهية', 'copy' => 'النسخ التي وصل مخزونها إلى صفر.'],
            'highest-stock' => ['category' => 'inventory', 'section' => 'inventory', 'title' => 'أعلى مخزون', 'copy' => 'النسخ التي تحتوي على أكبر عدد وحدات.'],
            'restock-demand' => ['category' => 'inventory', 'section' => 'products', 'title' => 'طلبات التنبيه عند التوفر', 'copy' => 'الطلب المخفي على المنتجات غير المتوفرة.'],
            'customer-growth' => ['category' => 'customers', 'section' => 'customers', 'title' => 'نمو العملاء', 'copy' => 'تطور تسجيل العملاء خلال الفترة.'],
            'registered-vs-guest' => ['category' => 'customers', 'section' => 'customers', 'title' => 'مسجلون مقابل زوار', 'copy' => 'مقارنة الطلبات بين العملاء المسجلين والزوار.'],
            'top-customers' => ['category' => 'customers', 'section' => 'customers', 'title' => 'أعلى العملاء', 'copy' => 'العملاء الأعلى قيمة وعدد طلبات.'],
            'customer-countries' => ['category' => 'customers', 'section' => 'customers', 'title' => 'دول العملاء والشحن', 'copy' => 'توزيع العملاء والطلبات حسب الدول.'],
            'customer-cities' => ['category' => 'customers', 'section' => 'customers', 'title' => 'مدن الطلبات', 'copy' => 'أكثر مدن الفوترة والشحن طلباً.'],
            'email-domains' => ['category' => 'customers', 'section' => 'customers', 'title' => 'نطاقات البريد', 'copy' => 'أكثر نطاقات البريد استخداماً بين العملاء الجدد.'],
            'cart-summary' => ['category' => 'carts', 'section' => 'carts', 'title' => 'ملخص السلات', 'copy' => 'النشطة والمتروكة والمتوسطات.'],
            'active-cart-products' => ['category' => 'carts', 'section' => 'carts', 'title' => 'منتجات السلات النشطة', 'copy' => 'أكثر المنتجات الموجودة حالياً داخل السلات.'],
            'abandoned-cart-products' => ['category' => 'carts', 'section' => 'carts', 'title' => 'منتجات السلات المتروكة', 'copy' => 'منتجات يخسرها المتجر غالباً قبل الدفع.'],
            'active-cart-variants' => ['category' => 'carts', 'section' => 'carts', 'title' => 'نسخ السلات النشطة', 'copy' => 'المقاسات والألوان الموجودة حالياً في السلات النشطة.'],
            'abandoned-cart-variants' => ['category' => 'carts', 'section' => 'carts', 'title' => 'نسخ السلات المتروكة', 'copy' => 'المقاسات والألوان المتروكة قبل إتمام الشراء.'],
            'cart-age' => ['category' => 'carts', 'section' => 'carts', 'title' => 'أعمار السلات', 'copy' => 'توزيع السلات حسب عمرها وقيمتها.'],
            'coupon-summary' => ['category' => 'promotions', 'section' => 'coupons', 'title' => 'ملخص الكوبونات', 'copy' => 'الاستخدام، الإيرادات، وقيمة الخصومات.'],
            'top-coupons' => ['category' => 'promotions', 'section' => 'coupons', 'title' => 'أعلى الكوبونات', 'copy' => 'أكواد الخصم الأكثر استخداماً وتأثيراً.'],
            'welcome-coupons' => ['category' => 'promotions', 'section' => 'coupons', 'title' => 'كوبونات الترحيب', 'copy' => 'الإرسال والاستخدام ومعدل التحويل.'],
        ];

        return [
            'categories' => [
                'overview' => ['title' => 'نظرة عامة', 'copy' => 'ملخص سريع عن صحة المتجر.', 'icon' => 'bx bx-pulse'],
                'sales' => ['title' => 'المبيعات والطلبات', 'copy' => 'تقارير الإيرادات وحالات الطلبات.', 'icon' => 'bx bx-line-chart'],
                'catalog' => ['title' => 'المنتجات والأقسام', 'copy' => 'تحليل أداء المنتجات والنسخ والأقسام.', 'icon' => 'bx bx-package'],
                'inventory' => ['title' => 'المخزون والطلب', 'copy' => 'النفاد القريب والمنتهي وطلبات التنبيه.', 'icon' => 'bx bx-archive'],
                'customers' => ['title' => 'العملاء والدول', 'copy' => 'تحليل العملاء والقيمة والدول.', 'icon' => 'bx bx-group'],
                'carts' => ['title' => 'السلات والتحويل', 'copy' => 'السلات النشطة والمتروكة ومنتجاتها.', 'icon' => 'bx bx-cart'],
                'promotions' => ['title' => 'الكوبونات والعروض', 'copy' => 'الكوبونات وكوبونات الترحيب.', 'icon' => 'bx bx-purchase-tag-alt'],
            ],
            'reports' => $reports,
        ];
    }

    private function validCategory(mixed $category, array $navigation): ?string
    {
        $category = is_string($category) ? $category : null;

        return $category && array_key_exists($category, $navigation['categories'])
            ? $category
            : null;
    }

    private function validReport(mixed $report, array $navigation): ?string
    {
        $report = is_string($report) ? $report : null;

        return $report && array_key_exists($report, $navigation['reports'])
            ? $report
            : null;
    }
}
