@php
    $pageTitle = $title;
    $pageDescription = $description;
    $breadcrumbs = ['الإدارة', $title];
@endphp

@extends('layouts.admin')

@section('content')
    <x-admin.empty-state
        :title="$title"
        :description="$description"
    />
@endsection
