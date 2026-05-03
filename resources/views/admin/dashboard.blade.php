@php
    $title = 'لوحة الإدارة';
    $pageTitle = 'تحليلات وملخص الأداء';
    $pageDescription = 'مؤشرات الأداء، اتجاهات المبيعات، الفَنَل، وأدوات التصدير من شاشة واحدة.';
    $breadcrumbs = ['الإدارة', 'لوحة البداية'];
@endphp

@extends('layouts.admin')

@section('content')
    <div id="adminDashboardContent">
        @include('admin.partials.dashboard-content', ['report' => $report])
    </div>
@endsection
