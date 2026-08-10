<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #222; }

        .watermark {
            position: fixed;
            top: 300px;
            left: 0;
            width: 100%;
            text-align: center;
            transform: rotate(-35deg);
            font-size: 60px;
            font-weight: bold;
            color: #000;
            opacity: 0.06;
        }

        .header { text-align: center; border-bottom: 2px solid #d6336c; padding-bottom: 10px; margin-bottom: 16px; }
        .header-table { width: auto; margin: 0 auto; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .header-logo-cell { width: 70px; }
        .header-logo-cell img { height: 40px; }
        .header-info-cell { text-align: left; padding-left: 12px; }
        .header-info-cell h1 { margin: 0; font-size: 16px; color: #d6336c; }
        .header-info-cell p { margin: 2px 0; font-size: 10px; color: #555; }

        .report-title { text-align: center; font-size: 13px; margin: 0 0 2px; }
        .generated-at { text-align: center; font-size: 9px; color: #777; margin: 0 0 14px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: left; }
        th { background: #f8f5f7; }
        .footer { margin-top: 16px; font-size: 9px; color: #999; text-align: right; }
    </style>
</head>
<body>
    <div class="watermark">{{ setting('business_name', config('app.name')) }}</div>

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo-cell">
                    @if (setting('logo_path'))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->path(setting('logo_path')) }}">
                    @endif
                </td>
                <td class="header-info-cell">
                    <h1>{{ setting('business_name', config('app.name')) }}</h1>
                    @if (setting('business_address'))
                        <p>{{ setting('business_address') }}</p>
                    @endif
                    @if (setting('business_phone'))
                        <p>Phone: {{ setting('business_phone') }}</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <p class="report-title">{{ $title }}</p>
    <p class="generated-at">Generated {{ now()->format('d M Y, h:i A') }}</p>

    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rowsArray as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headings) }}">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">{{ count($rowsArray) }} record(s)</div>
</body>
</html>
