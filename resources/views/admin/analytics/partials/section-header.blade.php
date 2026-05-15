<div class="flex flex-col gap-3 border-t border-white/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
    <h2 class="text-xl font-bold text-white">{{ $title }}</h2>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <a href="{{ route('admin.analytics.export.pdf', $exportQuery($section)) }}" class="admin-btn-secondary analytics-action">PDF</a>
        <a href="{{ route('admin.analytics.export.excel', $exportQuery($section)) }}" class="admin-btn-primary analytics-action">Excel</a>
    </div>
</div>
