<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #222; }
        .header { text-align: center; margin-bottom: 16px; }
        .header h1 { margin: 0; font-size: 16px; color: #d6336c; }
        .header p { margin: 2px 0; font-size: 10px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: left; }
        th { background: #f8f5f7; }
        .footer { margin-top: 16px; font-size: 9px; color: #999; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>GirliGirl Gym &amp; Fitness</h1>
        <p>{{ $title }}</p>
        <p>Generated {{ now()->format('d M Y, h:i A') }}</p>
    </div>

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
