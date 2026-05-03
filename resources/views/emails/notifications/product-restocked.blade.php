@extends('emails.layouts.branded')

@section('content')
    <div style="padding:18px 20px;border:1px solid #e5e7eb;background:#f9fafb;">
        <p style="margin:0 0 10px;font-size:14px;line-height:1.8;color:#111827;">{{ $summaryLine }}</p>
        <p style="margin:0;font-size:14px;line-height:1.8;color:#111827;">{{ $productLine }}</p>
    </div>
@endsection
