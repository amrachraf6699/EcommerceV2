<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Analytics Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; direction: ltr; }
        h1, h2 { margin: 0 0 10px; }
        .muted { color: #6b7280; }
        .section { margin-top: 24px; page-break-inside: avoid; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 7px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    @php
        $pdfText = static fn (mixed $value): string => \App\Support\PdfArabic::text($value);
    @endphp

    <h1>Analytics Report</h1>
    <p class="muted">Range: {{ $report['range'] }}</p>

    @foreach ($tables as $table)
        <div class="section">
            <h2>{{ $table['title'] }}</h2>
            <table>
                <tbody>
                    @foreach ($table['rows'] as $rowIndex => $row)
                        <tr>
                            @foreach ($row as $cell)
                                @if ($rowIndex === 0)
                                    <th>{{ $pdfText($cell) }}</th>
                                @else
                                    <td>{{ is_numeric($cell) ? number_format((float) $cell, str_contains((string) $cell, '.') ? 2 : 0) : $pdfText($cell) }}</td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
