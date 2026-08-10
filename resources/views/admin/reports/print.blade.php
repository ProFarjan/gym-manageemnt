<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #222; margin: 24px; position: relative; }

        .watermark {
            position: fixed;
            top: 40%;
            left: 0;
            width: 100%;
            text-align: center;
            transform: rotate(-35deg);
            font-size: 72px;
            font-weight: bold;
            color: #000;
            opacity: 0.06;
            z-index: 0;
            pointer-events: none;
        }

        .page { position: relative; z-index: 1; }

        .header { text-align: center; border-bottom: 2px solid #d6336c; padding-bottom: 12px; margin-bottom: 20px; }
        .header-table { width: auto; margin: 0 auto; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .header-logo-cell { width: 90px; }
        .header-logo-cell img { height: 50px; }
        .header-info-cell { text-align: left; padding-left: 14px; }
        .header-info-cell h1 { margin: 0; font-size: 20px; color: #d6336c; }
        .header-info-cell p { margin: 2px 0; font-size: 11px; color: #555; }

        h2.report-title { text-align: center; font-size: 16px; margin: 0 0 4px; }
        p.generated-at { text-align: center; font-size: 10px; color: #777; margin: 0 0 20px; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        table.data th { background: #f8f5f7; }

        .footer { margin-top: 16px; font-size: 10px; color: #999; text-align: right; }

        .print-bar { text-align: center; margin-bottom: 20px; }
        .print-bar button {
            background: #d6336c; color: #fff; border: none; padding: 8px 20px;
            border-radius: 4px; font-size: 13px; cursor: pointer;
        }

        @media print {
            .print-bar { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="watermark">{{ setting('business_name', config('app.name')) }}</div>

    <div class="page">
        <div class="print-bar">
            <button type="button" onclick="window.print()">Print</button>
        </div>

        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="header-logo-cell">
                        @if (setting('logo_path'))
                            <img src="{{ asset('storage/'.setting('logo_path')) }}">
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

        <h2 class="report-title">{{ $title }}</h2>
        <p class="generated-at">Generated {{ now()->format('d M Y, h:i A') }}</p>

        <table class="data">
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
    </div>

    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
