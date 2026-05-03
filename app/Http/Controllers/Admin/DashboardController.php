<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminDashboardReportExport;
use App\Http\Controllers\Controller;
use App\Support\AdminDashboardReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardController extends Controller
{
    public function __construct(private readonly AdminDashboardReportService $reportService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $report = $this->reportService->build($request->query('range'));

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('admin.partials.dashboard-content', [
                    'report' => $report,
                ])->render(),
            ]);
        }

        return view('admin.dashboard', [
            'report' => $report,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $report = $this->reportService->build($request->query('range'));

        return Pdf::loadView('admin.dashboard-export-pdf', [
            'report' => $report,
        ])->setPaper('a4', 'portrait')
            ->download('dashboard-report-'.$report['range'].'.pdf');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $report = $this->reportService->build($request->query('range'));

        return Excel::download(
            new AdminDashboardReportExport($report),
            'dashboard-report-'.$report['range'].'.xlsx'
        );
    }
}
