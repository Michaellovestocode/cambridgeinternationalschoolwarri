<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Exam Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #16a34a; padding-bottom: 20px; }
        .header h1 { color: #16a34a; margin: 0; }
        .stats { margin: 20px 0; }
        .stats table { width: 100%; margin-bottom: 20px; }
        .stats td { padding: 8px; background: #f3f4f6; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        .pass { color: #16a34a; font-weight: bold; }
        .fail { color: #dc2626; font-weight: bold; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CAMBRIDGE INTERNATIONAL SCHOOL</h1>
        <h2>{{ $exam->title }} - Results Report</h2>
        <p>{{ $exam->subject }}</p>
    </div>

    <div class="stats">
        <h3>Statistics</h3>
        <table>
            <tr>
                <td><strong>Total Students:</strong></td>
                <td>{{ $attempts->count() }}</td>
                <td><strong>Average Score:</strong></td>
                <td>{{ $statistics['average'] }}/{{ $exam->total_marks }}</td>
            </tr>
            <tr>
                <td><strong>Highest Score:</strong></td>
                <td>{{ $statistics['highest'] }}/{{ $exam->total_marks }}</td>
                <td><strong>Lowest Score:</strong></td>
                <td>{{ $statistics['lowest'] }}/{{ $exam->total_marks }}</td>
            </tr>
        </table>
    </div>

    <h3>Student Results</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Registration No.</th>
                <th>Class</th>
                <th>Score</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attempts as $index => $attempt)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $attempt->user->name }}</td>
                <td>{{ $attempt->user->registration_number }}</td>
                <td>{{ $attempt->user->class->display_name ?? 'N/A' }}</td>
                <td>{{ $attempt->total_score }}/{{ $exam->total_marks }}</td>
                <td class="{{ $attempt->total_score >= $exam->pass_mark ? 'pass' : 'fail' }}">
                    {{ $attempt->total_score >= $exam->pass_mark ? 'PASS' : 'FAIL' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }}<br>
        Cambridge International School - Exam Results Report
    </div>
</body>
</html>
