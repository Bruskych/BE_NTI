<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .app { margin-bottom: 20px; page-break-inside: avoid; }
        h2 { margin: 0; padding: 0; font-size: 16px; }
        .meta { color: #666; font-size: 12px; }
        .section { margin-top: 8px; }
    </style>
</head>
<body>
    <h1>Applications export</h1>
    <p>Generated at {{ now()->toIso8601String() }}</p>

    @foreach($applications as $app)
        <div class="app">
            <h2>Application #{{ $app->id }} — Status: {{ $app->status }}</h2>
            <div class="meta">Team: {{ optional($app->team)->name }} | Submitted: {{ $app->submitted_at }}</div>
            <div class="section">
                <strong>Answers / Data:</strong>
                <ul>
                    @foreach($app->answers ?? [] as $answer)
                        <li>{{ $answer->field_key ?? $answer->id }}: {{ $answer->value ?? $answer->file_path ?? '' }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="section">
                <strong>Decision / Notes:</strong>
                <p>{{ $app->decision_comment ?? '—' }}</p>
            </div>
        </div>
        <hr />
    @endforeach
</body>
</html>
