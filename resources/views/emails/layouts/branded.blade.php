<!DOCTYPE html>
<html lang="{{ $mailLocale ?? app()->getLocale() }}" dir="{{ $mailDirection ?? storefront_direction($mailLocale ?? app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine ?? $brandName }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;color:#111827;font-family:Arial,'Cairo',sans-serif;">
    <div style="padding:32px 16px;background:linear-gradient(180deg,#050505 0%,#141414 100%);">
        <div style="max-width:720px;margin:0 auto;background:#ffffff;border:1px solid #d1d5db;overflow:hidden;">
            <div style="padding:28px 32px;background:#050505;color:#ffffff;">
                <table role="presentation" style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td style="vertical-align:middle;">
                            @if (! empty($brandLogoUrl))
                                <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}" style="display:block;max-height:52px;max-width:160px;">
                            @endif
                        </td>
                        <td style="vertical-align:middle;text-align:end;">
                            <div style="font-size:11px;letter-spacing:.22em;text-transform:uppercase;color:#9ca3af;">{{ $brandName }}</div>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="padding:36px 32px 30px;">
                @if (! empty($eyebrow))
                    <p style="margin:0 0 12px;font-size:11px;font-weight:700;letter-spacing:.24em;text-transform:uppercase;color:#6b7280;">{{ $eyebrow }}</p>
                @endif

                <h1 style="margin:0 0 14px;font-size:28px;line-height:1.2;color:#111827;">{{ $title }}</h1>

                @if (! empty($intro))
                    <p style="margin:0 0 24px;font-size:15px;line-height:1.8;color:#4b5563;">{{ $intro }}</p>
                @endif

                @yield('content')

                @if (! empty($actionUrl) && ! empty($actionLabel))
                    <div style="margin-top:28px;">
                        <a href="{{ $actionUrl }}" style="display:inline-block;padding:14px 24px;background:#050505;color:#ffffff;text-decoration:none;font-weight:700;border:1px solid #050505;">
                            {{ $actionLabel }}
                        </a>
                    </div>
                @endif
            </div>

            <div style="padding:22px 32px;background:#f9fafb;border-top:1px solid #e5e7eb;color:#6b7280;">
                @if (! empty($footer))
                    <p style="margin:0 0 10px;font-size:13px;line-height:1.8;">{{ $footer }}</p>
                @endif

                <a href="{{ $brandHomeUrl }}" style="font-size:12px;color:#111827;text-decoration:none;font-weight:700;">
                    {{ $brandName }}
                </a>
            </div>
        </div>
    </div>
</body>
</html>
