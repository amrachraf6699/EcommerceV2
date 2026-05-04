@php
    $title = 'لوحة الإدارة';
    $pageTitle = 'تحليلات وملخص الأداء';
    $pageDescription = 'مؤشرات الأداء، اتجاهات المبيعات، القمع البيعي، وأدوات التصدير من شاشة واحدة.';
    $breadcrumbs = ['الإدارة', 'لوحة البداية'];
@endphp

@extends('layouts.admin')

@section('content')
    <div id="adminDashboardContent">
        @include('admin.partials.dashboard-content', ['report' => $report])
    </div>
@endsection

@push('styles')
<style>
    @media (max-width: 639px) {
        .admin-dashboard {
            gap: 1rem;
        }

        .admin-dashboard section,
        .admin-dashboard article {
            min-width: 0;
        }

        .admin-dashboard .dashboard-range-select,
        .admin-dashboard .dashboard-range-action,
        .admin-dashboard .dashboard-quick-action {
            width: 100%;
        }

        .admin-dashboard .dashboard-range-action,
        .admin-dashboard .dashboard-quick-action {
            justify-content: center;
            text-align: center;
        }

        .admin-dashboard [data-dashboard-chart-canvas] {
            height: 16rem;
        }

        .admin-dashboard .text-3xl {
            font-size: 1.65rem;
            line-height: 2rem;
        }

        .admin-dashboard .text-2xl {
            font-size: 1.35rem;
            line-height: 1.8rem;
        }
    }
</style>
@endpush
