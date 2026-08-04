<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report Card - {{ $reportCard->student->name }}</title>
    @php
        $summarySubjectCount = isset($scores) ? $scores->count() : 0;
        $summaryTotalScore = isset($scores) ? (float) $scores->sum('total') : (float) ($reportCard->total_score ?? 0);
        $summaryAverage = $summarySubjectCount > 0 ? round($summaryTotalScore / $summarySubjectCount, 2) : 0;
        $displayOverallGrade = $summarySubjectCount > 0 ? \App\Models\Subject::getGrade($summaryAverage) : ($reportCard->overall_grade ?? 'F9');
        $formatOrdinal = function ($value) {
            if ($value === null || $value === '') {
                return new \Illuminate\Support\HtmlString('');
            }

            $position = (int) $value;
            $suffix = 'th';

            if ($position % 100 < 11 || $position % 100 > 13) {
                $suffix = match ($position % 10) {
                    1 => 'st',
                    2 => 'nd',
                    3 => 'rd',
                    default => 'th',
                };
            }

            return new \Illuminate\Support\HtmlString($position . '<sup class="ordinal-suffix">' . $suffix . '</sup>');
        };
    @endphp

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 0;
            size: A4 portrait;
        }
        
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            line-height: 1.25;
            color: #000;
            background: #fff;
        }
        
        .page {
            width: {{ ($renderMode ?? 'pdf') === 'browser' ? '210mm' : '204mm' }};
            padding: 9mm;
            position: relative;
            border: 2px solid {{ $selectedColor['primary'] }};
            background: #fff;
            overflow: hidden;
            transform: translateY(0);
        }

        @media screen {
            body {
                background: #e5e7eb;
            }

            .page {
                margin: 18px auto;
                box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
            }

            .portal-report-toolbar + .page {
                margin-top: 96px;
            }
        }

        .portal-report-toolbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 22px;
            background: #fff;
            border-bottom: 1px solid #d1d5db;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .12);
            font-family: Arial, Helvetica, sans-serif;
        }

        .portal-report-toolbar-title {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
        }

        .portal-report-toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .portal-report-toolbar a {
            display: inline-block;
            padding: 9px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }

        .portal-report-back {
            background: #f3f4f6;
            color: #374151;
        }

        .portal-report-download {
            background: {{ $selectedColor['primary'] }};
            color: #fff;
        }

        @media print {
            .portal-report-toolbar {
                display: none;
            }
        }

        .inner-frame {
            position: absolute;
            top: 4mm;
            right: 4mm;
            bottom: 4mm;
            left: 4mm;
            border: 1px solid {{ $selectedColor['secondary'] }};
            z-index: -2;
        }

        .watermark {
            position: absolute;
            top: 118mm;
            left: 50%;
            width: 155mm;
            height: 155mm;
            margin-left: -77.5mm;
            margin-top: -77.5mm;
            opacity: 0.045;
            z-index: -1;
            text-align: center;
        }

        .watermark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        /* Header */
        .header {
            border: 2px solid {{ $selectedColor['primary'] }};
            padding: 7px 8px;
            margin-bottom: 7px;
            background: #fff;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .header-table td {
            border: 0;
            vertical-align: middle;
            padding: 0;
        }

        .header-logo-cell {
            width: 90px;
            text-align: left;
        }

        .header-photo-cell {
            width: 90px;
            text-align: right;
        }

        .header-content-cell {
            text-align: center;
            padding: 0 12px;
            vertical-align: middle;
        }
        
        .school-logo {
            width: 78px;
            height: 84px;
            border: 2px solid {{ $selectedColor['primary'] }};
            object-fit: contain;
        }
        
        .student-photo {
            width: 78px;
            height: 84px;
            border: 2px solid {{ $selectedColor['primary'] }};
            object-fit: cover;
        }

        .student-photo-placeholder {
            width: 78px;
            height: 84px;
            border: 2px solid {{ $selectedColor['primary'] }};
            text-align: center;
            line-height: 84px;
            font-size: 10px;
            color: #666;
            display: inline-block;
        }

        @if(($renderMode ?? 'pdf') === 'pdf')
        /* Use millimeter units for PDF rendering to improve scaling on mobile viewers */
        .school-logo, .student-photo {
            width: 21mm;
            height: 24mm;
        }

        .student-photo-placeholder {
            width: 21mm;
            height: 24mm;
            line-height: 24mm;
            font-size: 9px;
        }
        @endif
        
        .school-name {
            font-size: 17px;
            font-weight: 900;
            color: #0B2A4A;
            margin-bottom: 4px;
            letter-spacing: .6px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        
        .school-address {
            font-size: 8.8px;
            margin-bottom: 2px;
            color: #374151;
            font-weight: 600;
        }

        .school-contact {
            font-size: 8.4px;
            margin-bottom: 2px;
            color: #4B5563;
            font-weight: 500;
        }

        .contact-label,
        .contact-value {
            color: {{ $selectedColor['primary'] }};
            font-weight: 700;
        }
        
        .report-title {
            font-size: 12px;
            font-weight: 800;
            margin-top: 6px;
            padding: 5px 8px;
            background: {{ $selectedColor['light'] }};
            border: 1px solid {{ $selectedColor['secondary'] }};
            letter-spacing: .45px;
            color: #111827;
        }

        .report-meta {
            margin-top: 4px;
            font-size: 8.7px;
            font-weight: 800;
            color: {{ $selectedColor['primary'] }};
            letter-spacing: .4px;
        }
        
        /* Student Info */
        .student-info {
            margin-bottom: 7px;
            font-size: 10px;
            border: 1px solid {{ $selectedColor['primary'] }};
            background: #fff;
        }

        .student-info-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 0;
        }

        .student-info-table td {
            padding: 4px 8px;
            border: 1px solid #000;
            vertical-align: middle;
            text-align: left;
        }

        .info-label {
            font-weight: bold;
            background: {{ $selectedColor['light'] }};
            color: #111827;
        }
        
        /* Scores Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9px;
            page-break-inside: auto;
        }

        .scores-table tr,
        .summary-box,
        .comment-box,
        .next-term-box {
            page-break-inside: avoid;
        }
        
        th, td {
            border: 1px solid #1F2937;
            padding: 3px 2px;
            text-align: center;
        }
        
        th {
            background: {{ $selectedColor['primary'] }};
            color: #fff;
            font-weight: bold;
            font-size: 8px;
            letter-spacing: .2px;
        }

        .scores-table tbody tr:nth-child(even) td {
            background: #F8FAFC;
        }

        .scores-table tbody tr td {
            height: 18px;
        }
        
        .subject-name {
            text-align: left;
            padding-left: 4px;
            font-weight: bold;
        }

        .official-layout {
            display: block;
            width: 100%;
            margin-bottom: 7px;
        }

        .official-main {
            display: block;
            width: 100%;
            padding-right: 0;
        }

        .official-sidebar {
            display: block;
            width: 100%;
        }

        .official-main .scores-table {
            font-size: 8.3px;
        }

        .official-main .scores-table th {
            font-size: 7.4px;
        }

        .official-main .scores-table td {
            font-size: 8.2px;
        }

        .total-cell {
            background: {{ $selectedColor['light'] }} !important;
            font-weight: bold;
            color: {{ $selectedColor['primary'] }};
        }

        .position-cell {
            font-weight: bold;
        }

        .ordinal-suffix {
            font-size: 65%;
            line-height: 0;
            vertical-align: super;
        }
        
        .grade-a {
            background: #D1FAE5;
            font-weight: bold;
        }
        
        .grade-b {
            background: #DBEAFE;
        }
        
        .grade-c {
            background: #FEF3C7;
        }
        
        .grade-f {
            background: #FEE2E2;
            font-weight: bold;
        }
        
        /* Summary Section */
        .summary-section {
            display: table;
            width: 100%;
            margin-bottom: 7px;
        }
        
        .summary-col {
            display: table-cell;
            width: 33.333%;
            padding: 3px;
            vertical-align: top;
        }
        
        .summary-box {
            border: 1.5px solid {{ $selectedColor['primary'] }};
            padding: 4px;
            margin-bottom: 4px;
            background: rgba(255, 255, 255, .88);
        }
        
        .summary-title {
            font-weight: bold;
            background: {{ $selectedColor['primary'] }};
            color: #fff;
            padding: 3px;
            margin-bottom: 3px;
            text-align: center;
            font-size: 9px;
            border-bottom: 1px solid {{ $selectedColor['secondary'] }};
        }
        
        .summary-item {
            padding: 1px 0;
            font-size: 8px;
        }
        
        /* Grade Scale */
        .grade-scale {
            font-size: 8px;
        }
        
        .grade-scale table {
            font-size: 8px;
        }
        
        .grade-scale th, .grade-scale td {
            padding: 2px;
        }

        .traits-section {
            display: table;
            width: 100%;
            margin: 1px 0 6px;
        }

        .traits-col {
            display: table-cell;
            width: 50%;
            padding: 3px;
            vertical-align: top;
        }

        .traits-table {
            margin-bottom: 0;
            font-size: 7.4px;
        }

        .traits-table td {
            padding: 1.5px;
        }

        .rating-cell {
            width: 15px;
            font-size: 7px;
        }

        .rating-index {
            font-size: 7px;
            line-height: 1.35;
            margin-top: 3px;
            color: #111827;
        }

        .scale-grade-section {
            display: table;
            width: 100%;
            margin-bottom: 7px;
        }

        .scale-col {
            display: table-cell;
            width: 68%;
            padding: 3px;
            vertical-align: top;
        }

        .overall-col {
            display: table-cell;
            width: 32%;
            padding: 3px;
            vertical-align: top;
        }

        .overall-grade-box {
            border: 1.5px solid {{ $selectedColor['primary'] }};
            text-align: center;
            background: rgba(255, 255, 255, .92);
        }

        .overall-grade-value {
            font-size: 14px;
            font-weight: bold;
            padding: 12px 4px;
            color: {{ $displayOverallGrade === 'F9' ? '#B91C1C' : $selectedColor['primary'] }};
        }
        
        /* Comments */
        .comments-section {
            margin-top: 4px;
        }
        
        .comment-box {
            border: 1.5px solid {{ $selectedColor['primary'] }};
            padding: 5px;
            margin-bottom: 5px;
            min-height: 40px;
            background: rgba(255, 255, 255, .9);
        }
        
        .comment-label {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 3px;
            color: {{ $selectedColor['primary'] }};
            text-transform: uppercase;
        }
        
        .comment-text {
            font-size: 9.5px;
            font-style: italic;
            min-height: 10px;
            line-height: 1.05;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 100%;
            max-width: 180px;
            margin-top: 8px;
            padding-top: 1px;
            font-size: 9px;
            line-height: 1.2;
        }

        .signature-line.has-image {
            margin-top: 6px;
            padding-top: 0;
        }

        .signature-line.no-image {
            margin-top: 8px;
            padding-top: 1px;
        }

        .signature-image {
            display: block;
            width: 120px;
            height: 34px;
            object-fit: contain;
            margin-bottom: 1px;
        }

        .next-term-box {
            text-align: center;
            margin-top: 8px;
            padding: 5px;
            font-size: 10px;
            border-top: 1px solid {{ $selectedColor['primary'] }};
            border-bottom: 1px solid {{ $selectedColor['primary'] }};
            background: {{ $selectedColor['light'] }};
            width: 100%;
            clear: both;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 2px solid {{ $selectedColor['primary'] }};
            font-size: 9px;
        }
        
        .confidential {
            color: red;
            font-weight: bold;
            margin-top: 5px;
        }

        .official-note {
            margin-top: 3px;
            color: #374151;
            font-size: 8px;
        }

        .lower-school .official-main .scores-table th {
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .lower-school .official-main .scores-table tbody tr td {
            height: 26px;
            font-size: 8.5px;
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .lower-school .summary-section {
            margin-top: 7px;
        }

        .lower-school .summary-box {
            padding: 6px;
            margin-bottom: 7px;
        }

        .lower-school .summary-title {
            padding: 4px;
        }

        .lower-school .summary-item {
            padding: 2px 0;
            font-size: 8.5px;
        }

        .lower-school .comment-box {
            min-height: 48px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    @php
        $renderMode = $renderMode ?? 'pdf';
        $portal = $portal ?? [];
        $logoPath = $schoolSettings->school_logo ? public_path('storage/' . $schoolSettings->school_logo) : null;
        $studentPhotoPath = $reportCard->student->photo ? public_path('storage/' . $reportCard->student->photo) : null;
        $formTeacher = $reportCard->class?->activeFormTeacher?->teacher ?: $reportCard->class?->formTeacher?->teacher;
        $formTeacherSignaturePath = $formTeacher?->signature ? public_path('storage/' . $formTeacher->signature) : null;
        $authorityRole = $reportCard->class?->reportAuthorityRole() ?? 'head_teacher';
        $authorityTitle = $reportCard->class?->reportAuthorityTitle() ?? 'Head Teacher';
        $seniorAuthority = \App\Models\User::where('report_authority_role', $authorityRole)
            ->whereIn('role', ['admin', 'teacher'])
            ->orderBy('name')
            ->first();
        $seniorSignaturePath = $seniorAuthority?->signature ? public_path('storage/' . $seniorAuthority->signature) : null;
        $schoolAddress = 'Delta, Nigeria';
        $schoolPhone = '+234 803 289 7744';
        $schoolEmail = 'info@cambridgeinternationalschoolwarri.com';
        $schoolWebsite = 'cambridgeinternationalschoolwarri.com';
        $termName = trim((string) ($reportCard->term->name ?? ''));
        $termName = str($termName)
            ->replaceMatches('/\b1st\b/i', 'First')
            ->replaceMatches('/\b2nd\b/i', 'Second')
            ->replaceMatches('/\b3rd\b/i', 'Third')
            ->toString();
        $termReportName = $termName;

        if (! preg_match('/\bterm\b/i', $termReportName)) {
            $termReportName .= ' Term';
        }

        $termReportName = strtoupper($termReportName);
        $affectiveTraits = [
            'punctuality' => 'Punctuality',
            'neatness' => 'Neatness',
            'politeness' => 'Politeness',
            'attentiveness' => 'Attentiveness',
            'self_control' => 'Self Control',
            'sense_of_responsibility' => 'Sense of Responsibility',
        ];
        $psychomotorTraits = [
            'handwriting' => 'Handwriting',
            'drawing_painting' => 'Drawing/Painting',
            'craft_work' => 'Craft Work',
            'speech_fluency' => 'Speech Fluency',
            'sports_games' => 'Sports & Games',
            'music' => 'Music',
        ];

        if ($renderMode === 'browser') {
            $schoolLogoSrc = $schoolSettings->school_logo
                ? asset('storage/' . $schoolSettings->school_logo)
                : asset('images/schoollogo.jpg');
            $studentPhotoSrc = $reportCard->student->photo
                ? asset('storage/' . $reportCard->student->photo)
                : null;
            $formTeacherSignatureSrc = $formTeacher?->signature
                ? asset('storage/' . $formTeacher->signature)
                : null;
            $principalSignatureSrc = $seniorAuthority?->signature
                ? asset('storage/' . $seniorAuthority->signature)
                : null;
        } else {
            // For PDF rendering, Dompdf prefers file:/// URLs and forward slashes on Windows
            $toFileUrl = function ($path) {
                if (! $path) return null;
                $p = str_replace('\\', '/', $path);
                // already a file URL with triple slash
                if (stripos($p, 'file:///') === 0) return $p;
                // convert file:// to file:/// if present
                if (stripos($p, 'file://') === 0) {
                    return preg_replace('#^file:/+#', 'file:///', $p);
                }
                // Windows absolute path like C:/path -> file:///C:/path
                if (preg_match('#^[A-Za-z]:/#', $p)) {
                    return 'file:///' . ltrim($p, '/');
                }
                // fallback
                return 'file:///' . ltrim($p, '/');
            };

            $schoolLogoSrc = ($logoPath && file_exists($logoPath))
                ? $toFileUrl($logoPath)
                : $toFileUrl(public_path('images/schoollogo.jpg'));

            $studentPhotoSrc = ($studentPhotoPath && file_exists($studentPhotoPath))
                ? $toFileUrl($studentPhotoPath)
                : null;

            $formTeacherSignatureSrc = ($formTeacherSignaturePath && file_exists($formTeacherSignaturePath))
                ? $toFileUrl($formTeacherSignaturePath)
                : null;

            $principalSignatureSrc = ($seniorSignaturePath && file_exists($seniorSignaturePath))
                ? $toFileUrl($seniorSignaturePath)
                : null;
        }

        $isLowerSchool = $authorityRole !== 'principal';
        $seniorRemarkLabel = $authorityTitle . "'s Remark:";
    @endphp
    @php
        $autoPrint = request('print') && $renderMode === 'browser';
    @endphp

    @if($renderMode === 'browser' && (!empty($portal['back_url']) || !empty($portal['print_url']) || !empty($portal['download_url'])))
        <div class="portal-report-toolbar">
            <div class="portal-report-toolbar-title">{{ $portal['title'] ?? 'Report Card' }}</div>
            <div class="portal-report-toolbar-actions">
                @if(!empty($portal['back_url']))
                    <a href="{{ $portal['back_url'] }}" class="portal-report-back">Back</a>
                @endif
                <button type="button" onclick="window.print()" class="portal-report-download">Print</button>
            </div>
        </div>
    @endif

    @if($autoPrint)
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    window.print();
                }, 300);
            });
        </script>
    @endif
    <div class="page {{ $isLowerSchool ? 'lower-school' : '' }}">
        <div class="inner-frame"></div>
        <div class="watermark">
            <img src="{{ $schoolLogoSrc }}" alt="School Watermark">
        </div>

        <!-- Header -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="header-logo-cell">
                        <img src="{{ $schoolLogoSrc }}" class="school-logo" alt="School Logo">
                    </td>
                    <td class="header-content-cell">
                        <div class="school-name">{{ strtoupper($schoolSettings->school_name ?: 'Cambridge International School') }}</div>
                        <div class="school-address">{{ $schoolAddress }}</div>
                        <div class="school-contact">
                            <span class="contact-label">Tel:</span> {{ $schoolPhone }}
                            <span class="contact-label"> | Email:</span>
                            <span class="contact-value">{{ $schoolEmail }}</span>
                        </div>
                        <div class="school-contact">
                            <span class="contact-label">Website:</span>
                            <span class="contact-value">{{ $schoolWebsite }}</span>
                        </div>

                        <div class="report-title">
                            {{ $termReportName }} STUDENT'S PERFORMANCE REPORT
                        </div>
                        <div class="report-meta">
                            {{ strtoupper($reportCard->session->name) }} ACADEMIC SESSION
                        </div>
                    </td>
                    <td class="header-photo-cell">
                        @if($studentPhotoSrc)
                            <img src="{{ $studentPhotoSrc }}" class="student-photo" alt="Student Photo">
                        @else
                            <div class="student-photo-placeholder">Photo</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Student Info -->
        <div class="student-info">
            <table class="student-info-table">
                <colgroup>
                    <col style="width: 15%;">
                    <col style="width: 27%;">
                    <col style="width: 13%;">
                    <col style="width: 16%;">
                    <col style="width: 15%;">
                    <col style="width: 14%;">
                </colgroup>
                <tr>
                    <td class="info-label">NAME:</td>
                    <td colspan="3">{{ strtoupper($reportCard->student->name) }}</td>
                    <td class="info-label">GENDER:</td>
                    <td>{{ strtoupper($reportCard->student->sex ?? 'N/A') }}</td>
                </tr>
                <tr>
                    <td class="info-label">CLASS:</td>
                    <td>{{ $reportCard->class->display_name }}</td>
                    <td class="info-label">SESSION:</td>
                    <td>{{ $reportCard->session->name }}</td>
                    <td class="info-label">ADMISSION NO:</td>
                    <td>{{ $reportCard->student->registration_number }}</td>
                </tr>
                <tr>
                    <td class="info-label">D.O.B:</td>
                    <td>{{ $reportCard->student->date_of_birth ? $reportCard->student->date_of_birth->format('d-M-Y') : 'N/A' }}</td>
                    <td class="info-label">AGE:</td>
                    <td>{{ $reportCard->student->age ?? 'N/A' }}yrs</td>
                    <td class="info-label">ATTENDANCE:</td>
                    <td>{{ $reportCard->days_present }}/{{ $reportCard->days_school_opened }} ({{ number_format($reportCard->attendance_percentage, 1) }}%)</td>
                </tr>
            </table>
        </div>
        
        @php
            $gradeCount = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];
            foreach($scores as $score) {
                $gradeLetter = substr($score->grade, 0, 1);
                if(isset($gradeCount[$gradeLetter])) {
                    $gradeCount[$gradeLetter]++;
                }
            }
        @endphp

        <div class="official-layout">
            <div class="official-main">
                <!-- Scores Table -->
                <table class="scores-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 29%;">SUBJECTS</th>
                            <th colspan="2">TESTS</th>
                            <th rowspan="2">EXAM<br>(60)</th>
                            <th rowspan="2">TOTAL<br>(100)</th>
                            <th rowspan="2">GRADE</th>
                            <th rowspan="2">CLASS<br>AVG</th>
                            <th rowspan="2">POSITION</th>
                            <th rowspan="2">REMARK</th>
                        </tr>
                        <tr>
                            <th>CA1<br>(30)</th>
                            <th>CA2<br>(10)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scores as $score)
                        <tr>
                            <td class="subject-name">{{ strtoupper($score->subject->name) }}</td>
                            <td>{{ number_format($score->ca1, 1) }}</td>
                            <td>{{ number_format($score->ca2, 1) }}</td>
                            <td>{{ number_format($score->exam, 1) }}</td>
                            <td class="total-cell">{{ number_format($score->total, 1) }}</td>
                            <td class="
                                @if(substr($score->grade, 0, 1) == 'A') grade-a
                                @elseif(substr($score->grade, 0, 1) == 'B') grade-b
                                @elseif(substr($score->grade, 0, 1) == 'C') grade-c
                                @elseif($score->grade == 'F9') grade-f
                                @endif
                            "><strong>{{ $score->grade }}</strong></td>
                            <td>{{ number_format($score->class_average, 1) }}</td>
                            <td class="position-cell">{!! $formatOrdinal($score->position) !!}</td>
                            <td>{{ strtoupper($score->remark) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="summary-section">
                    <div class="summary-col">
                        <div class="summary-box">
                            <div class="summary-title">GRADE ANALYSIS</div>
                            <table style="width: 100%; font-size: 8px; margin-bottom: 0;">
                                <tr>
                                    <td><strong>A:</strong> {{ $gradeCount['A'] }}</td>
                                    <td><strong>B:</strong> {{ $gradeCount['B'] }}</td>
                                    <td><strong>C:</strong> {{ $gradeCount['C'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>D:</strong> {{ $gradeCount['D'] }}</td>
                                    <td><strong>E:</strong> {{ $gradeCount['E'] }}</td>
                                    <td><strong>F:</strong> {{ $gradeCount['F'] }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="summary-col">
                        <div class="summary-box">
                            <div class="summary-title">PERFORMANCE SUMMARY</div>
                            @php
                                $summarySubjectCount = isset($scores) ? $scores->count() : 0;
                                $summaryTotalScore = isset($scores) ? (float) $scores->sum('total') : (float) ($reportCard->total_score ?? 0);
                                $summaryAverage = $summarySubjectCount > 0 ? round($summaryTotalScore / $summarySubjectCount, 2) : 0;
                            @endphp
                            @php
                                $summaryGrade = 
                                    $summarySubjectCount > 0
                                        ? \App\Models\Subject::getGrade($summaryAverage)
                                        : ($reportCard->overall_grade ?? 'F9');
                            @endphp
                            <div class="summary-item">Total Score: <strong>{{ number_format($reportCard->total_score, 1) }}</strong></div>
                            <div class="summary-item">Average: <strong>{{ number_format($summaryAverage, 1) }}%</strong></div>
                            @if($reportCard->position)
                                <div class="summary-item">Position: <strong>{!! $formatOrdinal($reportCard->position) !!}</strong></div>
                            @endif
                            <div class="summary-item">Grade: <strong>{{ $summaryGrade }}</strong></div>
                        </div>
                    </div>
                    <div class="summary-col">
                        <div class="summary-box">
                            <div class="summary-title">ATTENDANCE SUMMARY</div>
                            <div class="summary-item">No of Times School Opened: <strong>{{ $reportCard->days_school_opened }}</strong></div>
                            <div class="summary-item">No of Times Present: <strong>{{ $reportCard->days_present }}</strong></div>
                            <div class="summary-item">No of Times Absent: <strong>{{ $reportCard->days_absent }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="official-sidebar">
                <div class="traits-section">
                    <div class="traits-col">
                        <div class="summary-box">
                            <div class="summary-title">AFFECTIVE DOMAIN</div>
                            <table class="traits-table">
                                <tr>
                                    <th>Trait</th>
                                    <th class="rating-cell">5</th>
                                    <th class="rating-cell">4</th>
                                    <th class="rating-cell">3</th>
                                    <th class="rating-cell">2</th>
                                    <th class="rating-cell">1</th>
                                </tr>
                                @foreach($affectiveTraits as $key => $trait)
                                    <tr>
                                        <td style="text-align: left;">{{ $trait }}</td>
                                        @for($rating = 5; $rating >= 1; $rating--)
                                            <td>{!! (int) data_get($reportCard->affective_domain, $key) === $rating ? '&#10003;' : '' !!}</td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>

                    <div class="traits-col">
                        <div class="summary-box">
                            <div class="summary-title">PSYCHOMOTOR SKILLS</div>
                            <table class="traits-table">
                                <tr>
                                    <th>Skill</th>
                                    <th class="rating-cell">5</th>
                                    <th class="rating-cell">4</th>
                                    <th class="rating-cell">3</th>
                                    <th class="rating-cell">2</th>
                                    <th class="rating-cell">1</th>
                                </tr>
                                @foreach($psychomotorTraits as $key => $skill)
                                    <tr>
                                        <td style="text-align: left;">{{ $skill }}</td>
                                        @for($rating = 5; $rating >= 1; $rating--)
                                            <td>{!! (int) data_get($reportCard->psychomotor_skills, $key) === $rating ? '&#10003;' : '' !!}</td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </table>
                            <div class="rating-index"><strong>Rating:</strong> 5 Excellent, 4 Good, 3 Average, 2 Fair, 1 Needs Improvement</div>
                        </div>
                    </div>
                </div>

                <div class="scale-grade-section">
                    <div class="scale-col">
                        <div class="summary-box grade-scale">
                            <div class="summary-title">GRADE SCALE</div>
                            <table>
                                <tr><th>Score</th><th>Grade</th><th>Remark</th></tr>
                                @foreach(\App\Models\Subject::gradeScale() as $scaleRow)
                                    <tr>
                                        <td>{{ $scaleRow['min'] }}-{{ $scaleRow['max'] }}</td>
                                        <td>{{ $scaleRow['grade'] }}</td>
                                        <td>{{ $scaleRow['remark'] }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>

                    <div class="overall-col">
                        <div class="overall-grade-box">
                            <div class="summary-title">OVERALL GRADE</div>
                            <div class="overall-grade-value">
                                @php
                                    $displayOverallGrade = $summarySubjectCount > 0 ? $summaryGrade : ($reportCard->overall_grade ?? 'F9');
                                @endphp
                                {{ \App\Models\Subject::getRemark($displayOverallGrade) }}, {{ $displayOverallGrade }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Comments -->
        <div class="comments-section">
            <div class="comment-box">
                <div class="comment-label">Form Teacher's Remark:</div>
                <div class="comment-text">
                    {{ $reportCard->class_teacher_comment ?: '............................................................' }}
                </div>
                <div class="signature-line {{ $formTeacherSignatureSrc ? 'has-image' : 'no-image' }}">
                    @if($formTeacherSignatureSrc)
                        <img src="{{ $formTeacherSignatureSrc }}" class="signature-image" alt="Form Teacher Signature">
                    @endif
                    <strong>{{ $reportCard->class_teacher_name ?: $formTeacher?->name ?: '................................' }}</strong><br>
                    @if(! $formTeacherSignatureSrc)
                        Signature: {{ $reportCard->class_teacher_signature ?: '___________________' }}
                    @endif
                </div>
            </div>
            
            <div class="comment-box">
                <div class="comment-label">{{ $seniorRemarkLabel }}</div>
                <div class="comment-text">
                    {{ $reportCard->head_teacher_comment ?: '............................................................' }}
                </div>
                <div class="signature-line {{ $principalSignatureSrc ? 'has-image' : 'no-image' }}">
                    @if($principalSignatureSrc)
                        <img src="{{ $principalSignatureSrc }}" class="signature-image" alt="{{ $authorityTitle }} Signature">
                    @endif
                    <strong>{{ $reportCard->head_teacher_name ?: $seniorAuthority?->name ?: '................................' }}</strong><br>
                    @if(! $principalSignatureSrc)
                        Signature: {{ $reportCard->head_teacher_signature ?: '___________________' }}
                    @endif
                </div>
            </div>
            
<div class="next-term-box">
                <strong>Next Term Begins:</strong> {{ $reportCard->term->next_term_begins ? $reportCard->term->next_term_begins->format('l, d-M-Y') : 'TBA' }}
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="confidential">[CONFIDENTIAL]</div>
            <div class="official-note">This report is issued by {{ $schoolSettings->school_name ?: 'Cambridge International School' }} and is valid with authorised school remarks.</div>
            <div style="margin-top: 3px;">&copy; {{ date('Y') }} {{ $schoolSettings->school_name ?: 'Cambridge International School' }}</div>
        </div>
    </div>
</body>
</html>
