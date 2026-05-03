@php
    $locale = app()->getLocale();
    $subjectLine = $subjectLine ?? $contactMessage->subject;
    $brandName = (string) setting('brand.name', config('app.name'));
    $logoPath = setting('brand.logo');
    $brandLogoUrl = $logoPath ? asset('storage/' . $logoPath) : null;
    $brandHomeUrl = route('storefront.home', ['locale' => $locale]);
    $mailLocale = $locale;
    $mailDirection = storefront_direction($locale);
    $eyebrow = $brandName;
    $title = $subjectLine;
    $intro = __('storefront.contact.email_reply_intro');
    $footer = $brandName;
@endphp

@extends('emails.layouts.branded')

@section('content')
    <div style="padding:18px 20px;border:1px solid #e5e7eb;background:#f9fafb;">
        <p style="margin:0 0 16px;font-size:14px;line-height:1.8;color:#111827;">{{ __('storefront.contact.email_reply_intro') }}</p>
        <h2 style="margin:0 0 16px;font-size:22px;line-height:1.3;color:#111827;">{{ $contactMessage->subject }}</h2>
        <div style="font-size:15px;line-height:1.8;color:#111827;">
            {!! $replyBody !!}
        </div>
    </div>
@endsection
