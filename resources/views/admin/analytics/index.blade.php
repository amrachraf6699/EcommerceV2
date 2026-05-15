@php
    $title = 'تحليلات المتجر';
    $pageTitle = 'التحليلات';
    $pageDescription = 'تقارير تفصيلية للمبيعات، المنتجات، العملاء، السلات، الكوبونات، والمخزون.';
    $breadcrumbs = ['الإدارة', 'التحليلات'];
@endphp

@extends('layouts.admin')

@section('content')
    <div id="adminAnalyticsContent">
        @include('admin.analytics.partials.content', [
            'report' => $report,
            'sectionLabels' => $sectionLabels,
            'analyticsNavigation' => $analyticsNavigation,
            'selectedCategory' => $selectedCategory,
            'selectedReport' => $selectedReport,
            'selectedSection' => $selectedSection,
        ])
    </div>
@endsection

@push('styles')
<style>
    .admin-analytics .analytics-card,
    .admin-analytics .analytics-panel {
        min-width: 0;
    }

    .admin-analytics-table-wrap {
        overflow-x: auto;
        border: 1px solid rgb(255 255 255 / .1);
        background: rgb(2 6 23 / .28);
    }

    .admin-analytics-table {
        width: 100%;
        min-width: 42rem;
        border-collapse: collapse;
        font-size: .85rem;
        text-align: right;
    }

    .admin-analytics-table th,
    .admin-analytics-table td {
        border-bottom: 1px solid rgb(255 255 255 / .08);
        padding: .85rem 1rem;
        vertical-align: top;
    }

    .admin-analytics-table th {
        color: #cbd5e1;
        font-weight: 800;
        white-space: nowrap;
    }

    .admin-analytics-table td {
        color: #f8fafc;
    }

    .admin-analytics-table tr:last-child td {
        border-bottom: 0;
    }

    .admin-analytics-chart {
        min-height: 20rem;
    }

    @media (max-width: 639px) {
        .admin-analytics .analytics-action,
        .admin-analytics .analytics-filter-input {
            width: 100%;
            justify-content: center;
        }

        .admin-analytics-chart {
            min-height: 16rem;
        }
    }
</style>
@endpush
