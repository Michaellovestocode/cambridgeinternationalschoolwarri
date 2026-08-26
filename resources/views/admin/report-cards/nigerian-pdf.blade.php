<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report Card - {{ $reportCard->student->name }}</title>
    @php
        $summarySubjectCount = isset($scores) ? $scores->count() : 0;
        $scoreCount = $summarySubjectCount;
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

        /* If there are many subjects, prefer landscape to get more vertical space */
        @if($scoreCount >= 16)
        @page { size: A4 landscape; margin: 3mm; }
        @endif
        
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
            @if($scoreCount >= 16)
                width: {{ ($renderMode ?? 'pdf') === 'browser' ? '287mm' : '282mm' }};
                padding: 6mm;
            @else
                width: {{ ($renderMode ?? 'pdf') === 'browser' ? '210mm' : '204mm' }};
                padding: 7mm;
            @endif
            position: relative;
            border: 2px solid {{ $selectedColor['primary'] }};
            background: #fff;
            overflow: hidden;
            transform: translateY(0);
            page-break-inside: avoid;
            page-break-after: avoid;
            page-break-before: avoid;
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
            /* Center the watermark to avoid pushing content vertically and
               reduce its size so it doesn't extend past the A4 page height. */
            position: absolute;
            top: 50%;
            left: 50%;
            width: 110mm;
            height: 110mm;
            transform: translate(-50%, -50%);
            opacity: 0.045;
            z-index: -1;
            text-align: center;
            pointer-events: none;
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
            height: 16px;
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
            padding: 4px;
            margin-bottom: 4px;
            min-height: 32px;
            background: rgba(255, 255, 255, .9);
        }

        .comment-row {
            display: flex;
            align-items: flex-start;
            gap: 4px;
        }
        
        .comment-label {
            font-weight: bold;
            font-size: 9px;
            color: {{ $selectedColor['primary'] }};
            text-transform: uppercase;
            white-space: nowrap;
        }
        
        .comment-text {
            font-size: 8.8px;
            font-style: italic;
            min-height: 9px;
            line-height: 1.02;
            flex: 1;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 100%;
            max-width: 170px;
            margin-top: 6px;
            padding-top: 1px;
            font-size: 8.5px;
            line-height: 1.15;
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
            width: 104px;
            height: 28px;
            object-fit: contain;
            margin-bottom: 1px;
        }

        .next-term-box {
            text-align: center;
            margin-top: 4px;
            padding: 3px;
            font-size: 8.6px;
            border-top: 1px solid {{ $selectedColor['primary'] }};
            border-bottom: 1px solid {{ $selectedColor['primary'] }};
            background: {{ $selectedColor['light'] }};
            width: 100%;
            clear: both;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            margin-top: 2px;
            padding-top: 1px;
            border-top: 2px solid {{ $selectedColor['primary'] }};
            font-size: 7.6px;
            line-height: 1.0;
        }
        
        .confidential {
            color: red;
            font-weight: bold;
            margin-top: 0;
            line-height: 1.0;
        }

        .official-note {
            margin-top: 0;
            color: #374151;
            font-size: 7.1px;
            line-height: 1.0;
        }

        .lower-school .official-main .scores-table th {
            padding-top: 3px;
            padding-bottom: 3px;
            font-size: 6.8px;
        }

        .lower-school .official-main .scores-table tbody tr td {
            height: 14px;
            font-size: 7.2px;
            padding-top: 2px;
            padding-bottom: 2px;
        }

        .lower-school .summary-section {
            margin-top: 4px;
        }

        .lower-school .summary-box {
            padding: 3px;
            margin-bottom: 3px;
        }

        .lower-school .summary-title {
            padding: 2px;
            font-size: 8px;
        }

        .lower-school .summary-item {
            padding: 1px 0;
            font-size: 7.2px;
        }

        .lower-school .comment-box {
            min-height: 28px;
            padding: 3px;
            margin-bottom: 3px;
        }

        .lower-school .comment-label {
            font-size: 8px;
        }

        .lower-school .comment-text {
            font-size: 7.8px;
            line-height: 1.0;
        }

        .lower-school .signature-line {
            max-width: 150px;
            margin-top: 4px;
            font-size: 7.5px;
        }

        .lower-school .signature-image {
            width: 88px;
            height: 22px;
        }

        .lower-school .next-term-box {
            margin-top: 3px;
            padding: 2px;
            font-size: 7.8px;
        }

        .lower-school .footer {
            margin-top: 2px;
            padding-top: 1px;
            font-size: 6.8px;
        }
        /* Print-only fallback: tighten margins, hide watermark and apply slight scaling */
        @media print {
            @page { size: A4 portrait; margin: 4mm; }
            html, body { margin: 0; padding: 0; }
            .portal-report-toolbar { display: none !important; }
            .watermark { display: none !important; }
            .page {
                padding: 4mm !important;
                /* Use transform/zoom as a fallback to force content to fit
                   in browser print previews. Dompdf may ignore zoom but
                   browsers will respect it. */
                transform: none !important;
                -webkit-transform: scale(0.96);
                -webkit-transform-origin: top left;
                transform-origin: top left;
                zoom: 0.96;
                page-break-inside: avoid !important;
            }
            .page.compact-md { -webkit-transform: scale(0.94); zoom: 0.94; }
            .page.compact-lg { -webkit-transform: scale(0.92); zoom: 0.92; }
            .page.compact-xl { -webkit-transform: scale(0.88); zoom: 0.88; }

            /* Squeeze table paddings for print */
            .scores-table th, .scores-table td { padding: 1px 3px !important; }
            .summary-box, .comment-box { padding: 2px !important; }
        }
        /* Compact variants to reduce spacing and font-size when there are many subjects */
        .page.compact-sm {
            font-size: 9px;
            padding: 6mm;
        }
        .page.compact-sm .scores-table td { height: 14px; padding: 2px 2px; }
        .page.compact-sm .scores-table th { padding: 2px 2px; font-size: 7px; }
        .page.compact-sm .summary-box, .page.compact-sm .comment-box { padding: 3px; }

        .page.compact-md {
            font-size: 8.6px;
            padding: 5.5mm;
        }
        .page.compact-md .scores-table td { height: 13px; padding: 1.5px 2px; }
        .page.compact-md .scores-table th { padding: 1.5px 2px; font-size: 6.8px; }
        .page.compact-md .summary-box, .page.compact-md .comment-box { padding: 2.5px; }

        .page.compact-lg {
            font-size: 8.2px;
            padding: 5mm;
        }
        .page.compact-lg .scores-table td { height: 12px; padding: 1px 1.5px; }
        .page.compact-lg .scores-table th { padding: 1px 1.5px; font-size: 6.4px; }
        .page.compact-lg .summary-box, .page.compact-lg .comment-box { padding: 2px; }

        .page.compact-xl {
            font-size: 7.8px;
            padding: 4.5mm;
        }
        .page.compact-xl .scores-table td { height: 11px; padding: 0.8px 1px; }
        .page.compact-xl .scores-table th { padding: 0.8px 1px; font-size: 6px; }
        .page.compact-xl .summary-box, .page.compact-xl .comment-box { padding: 1.5px; }
        @if(($renderMode ?? 'pdf') === 'pdf' && ($scoreCount ?? 0) >= 16)
        /* PDF-only tighter rules for many subjects */
        body { font-size: 9px; }
        .scores-table th, .scores-table td { font-size: 7px; padding: 1px; }
        .scores-table td { height: 11px; }
        .school-logo, .student-photo { width: 18mm; height: 20mm; }
        .header-photo-cell, .header-logo-cell { width: 82px; }
        @endif
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
            // For PDF rendering embed local images as base64 data URIs so Dompdf will include them reliably
            $fileToDataUri = function ($path) {
                if (! $path || ! file_exists($path)) return null;
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mime = match ($ext) {
                    'png' => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'svg' => 'image/svg+xml',
                    default => 'application/octet-stream',
                };
                $data = @file_get_contents($path);
                if ($data === false) return null;
                return 'data:' . $mime . ';base64,' . base64_encode($data);
            };

            $schoolLogoSrc = ($logoPath && file_exists($logoPath))
                ? $fileToDataUri($logoPath)
                : $fileToDataUri(public_path('images/schoollogo.jpg'));

            $studentPhotoSrc = ($studentPhotoPath && file_exists($studentPhotoPath))
                ? $fileToDataUri($studentPhotoPath)
                : null;

            $formTeacherSignatureSrc = ($formTeacherSignaturePath && file_exists($formTeacherSignaturePath))
                ? $fileToDataUri($formTeacherSignaturePath)
                : null;

            $principalSignatureSrc = ($seniorSignaturePath && file_exists($seniorSignaturePath))
                ? $fileToDataUri($seniorSignaturePath)
                : null;
        }

        $isLowerSchool = $authorityRole !== 'principal';
        $seniorRemarkLabel = $authorityTitle . "'s Remark:";
    @endphp
    @php
        // Adjust layout when there are many subjects so the report fits on one A4 page.
        $scoreCount = isset($scores) ? $scores->count() : 0;
        $compactClass = '';
        if ($scoreCount >= 18) {
            $compactClass = 'compact-xl';
        } elseif ($scoreCount >= 16) {
            $compactClass = 'compact-lg';
        } elseif ($scoreCount >= 14) {
            $compactClass = 'compact-md';
        } elseif ($scoreCount >= 12) {
            $compactClass = 'compact-sm';
        }
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
    <div class="page {{ $isLowerSchool ? 'lower-school' : '' }} {{ $compactClass }}">
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
                <div class="comment-row">
                    <div class="comment-label">Form Teacher's Remark:</div>
                    <div class="comment-text">
                        {{ $reportCard->class_teacher_comment ?: '............................................................' }}
                    </div>
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
                <div class="comment-row">
                    <div class="comment-label">{{ $seniorRemarkLabel }}</div>
                    <div class="comment-text">
                        {{ $reportCard->head_teacher_comment ?: '............................................................' }}
                    </div>
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
        </div>
    </div>
</body>
</html>
