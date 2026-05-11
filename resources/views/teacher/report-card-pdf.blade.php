<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card - {{ $reportCard->student->name }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .school-name {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 24px;
            font-weight: bold;
            margin: 15px 0;
        }
        .student-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 8px 15px 8px 0;
            width: 150px;
            background-color: #f8fafc;
        }
        .info-value {
            display: table-cell;
            padding: 8px 0;
        }
        .subjects-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .subjects-table th,
        .subjects-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .subjects-table th {
            background-color: #f8fafc;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .remarks-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .remark-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f8fafc;
        }
        .remark-label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #2563eb;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signature-left {
            display: table-cell;
            width: 50%;
            text-align: left;
        }
        .signature-right {
            display: table-cell;
            width: 50%;
            text-align: right;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            margin-top: 40px;
            padding-bottom: 5px;
        }
        @media print {
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="school-name">Modern CBT Platform</div>
        <div class="report-title">Student Report Card</div>
        <div style="font-size: 16px; color: #666;">
            Academic Session: {{ $reportCard->session->name }} | Term: {{ $reportCard->term->name }}
        </div>
    </div>

    <!-- Student Information -->
    <div class="student-info">
        <div class="info-row">
            <div class="info-label">Student Name:</div>
            <div class="info-value">{{ $reportCard->student->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Student Email:</div>
            <div class="info-value">{{ $reportCard->student->email }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Class:</div>
            <div class="info-value">{{ $reportCard->class->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Date Generated:</div>
            <div class="info-value">{{ $reportCard->created_at->format('F d, Y') }}</div>
        </div>
    </div>

    <!-- Subjects Table -->
    <table class="subjects-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Score (%)</th>
                <th>Grade</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportCard->subjects as $subjectData)
            <tr>
                <td>{{ \App\Models\Subject::find($subjectData['subject_id'])->name ?? 'Unknown Subject' }}</td>
                <td>{{ number_format($subjectData['score'], 2) }}%</td>
                <td>{{ $subjectData['grade'] }}</td>
                <td>{{ $subjectData['remark'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Remarks Section -->
    <div class="remarks-section">
        @if($reportCard->overall_remark)
        <div class="remark-box">
            <div class="remark-label">Overall Performance Remark:</div>
            <div>{{ $reportCard->overall_remark }}</div>
        </div>
        @endif

        @if($reportCard->teacher_comment)
        <div class="remark-box">
            <div class="remark-label">Teacher's Comment:</div>
            <div>{{ $reportCard->teacher_comment }}</div>
        </div>
        @endif
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-left">
            <div class="signature-line">Form Teacher's Signature</div>
            <div style="margin-top: 10px; font-size: 12px; color: #666;">
                {{ auth()->user()->name }}
            </div>
        </div>
        <div class="signature-right">
            <div class="signature-line">Principal's Signature</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This report card was generated electronically on {{ now()->format('F d, Y \a\t H:i') }}</p>
        <p>Modern CBT Platform - Academic Management System</p>
    </div>
</body>
</html>