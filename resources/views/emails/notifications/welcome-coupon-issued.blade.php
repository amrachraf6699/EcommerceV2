@extends('emails.layouts.branded')

@section('content')
    <div style="padding:18px 20px;border:1px solid #e5e7eb;background:#f9fafb;">
        <p style="margin:0 0 14px;font-size:14px;line-height:1.8;color:#111827;">{{ $summaryLine }}</p>

        <div style="margin:0 0 14px;padding:16px;border:1px dashed #111827;background:#ffffff;">
            <p style="margin:0 0 6px;font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#6b7280;">{{ $codeLabel }}</p>
            <p style="margin:0;font-size:26px;font-weight:800;letter-spacing:.08em;color:#111827;">{{ $couponCode }}</p>
        </div>

        <p style="margin:0;font-size:14px;line-height:1.8;color:#111827;">{{ $accountOnlyLine }}</p>
    </div>
@endsection
