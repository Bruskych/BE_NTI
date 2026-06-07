<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Projects export</h1>
    <p>Generated at {{ now()->toIso8601String() }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Status</th>
                <th>Final score</th>
                <th>Started at</th>
                <th>Finished at</th>
                <th>Team</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
                <tr>
                    <td>{{ $project->id }}</td>
                    <td>{{ $project->title }}</td>
                    <td>{{ $project->status }}</td>
                    <td>{{ $project->final_score }}</td>
                    <td>{{ optional($project->started_at)->toIso8601String() }}</td>
                    <td>{{ optional($project->finished_at)->toIso8601String() }}</td>
                    <td>{{ optional($project->application->team)->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
