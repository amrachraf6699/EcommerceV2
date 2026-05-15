<?php

namespace App\Exports;

use App\Support\AdminAnalyticsReportService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AdminAnalyticsReportExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly array $report,
        private readonly ?string $section = null,
        private readonly ?AdminAnalyticsReportService $reportService = null
    ) {
    }

    public function sheets(): array
    {
        $service = $this->reportService ?? app(AdminAnalyticsReportService::class);

        return collect($service->exportTables($this->report, $this->section))
            ->map(fn (array $table) => new AnalyticsArraySheet($table['title'], $table['rows']))
            ->values()
            ->all();
    }
}
