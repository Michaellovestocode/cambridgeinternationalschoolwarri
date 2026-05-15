<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report Card - {{ $reportCard->student->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }
        
        .page {
            width: 210mm;
            padding: 9mm;
            position: relative;
            border: 2px solid {{ $selectedColor['primary'] }};
            background: #fff;
        }

        @media screen {
            body {
                background: #e5e7eb;
            }

            .page {
                margin: 18px auto;
                box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
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
            padding: 7px;
            margin-bottom: 8px;
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
            width: 82px;
            text-align: left;
        }

        .header-photo-cell {
            width: 88px;
            text-align: right;
        }

        .header-content-cell {
            text-align: center;
            padding: 0 10px;
        }
        
        .school-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        
        .student-photo {
            width: 74px;
            height: 86px;
            border: 2px solid {{ $selectedColor['primary'] }};
            object-fit: cover;
        }

        .student-photo-placeholder {
            width: 74px;
            height: 86px;
            border: 2px solid {{ $selectedColor['primary'] }};
            text-align: center;
            line-height: 86px;
            font-size: 10px;
            color: #666;
            display: inline-block;
        }
        
        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #0B2A4A;
            margin-bottom: 3px;
            letter-spacing: .5px;
        }
        
        .school-address {
            font-size: 9.5px;
            margin-bottom: 2px;
        }

        .school-contact {
            font-size: 9px;
            margin-bottom: 2px;
        }

        .contact-label,
        .contact-value {
            color: #B91C1C;
            font-weight: bold;
        }
        
        .report-title {
            font-size: 13px;
            font-weight: bold;
            margin-top: 6px;
            padding: 5px 4px;
            background: {{ $selectedColor['light'] }};
            border: 1px solid {{ $selectedColor['secondary'] }};
            letter-spacing: .4px;
        }

        .report-meta {
            margin-top: 4px;
            font-size: 8.5px;
            font-weight: bold;
            color: {{ $selectedColor['primary'] }};
        }
        
        /* Student Info */
        .student-info {
            margin-bottom: 8px;
            font-size: 10px;
            border: 1px solid {{ $selectedColor['primary'] }};
            background: #fff;
        }
        
        .info-row {
            display: table;
            width: 100%;
        }
        
        .info-cell {
            display: table-cell;
            padding: 4px 8px;
            border: 1px solid #000;
            vertical-align: middle;
        }
        
        .info-label {
            font-weight: bold;
            width: 18%;
            background: {{ $selectedColor['light'] }};
            color: #111827;
        }
        
        /* Scores Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 4px 3px;
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
            height: 22px;
        }
        
        .subject-name {
            text-align: left;
            padding-left: 5px;
            font-weight: bold;
        }

        .total-cell {
            background: {{ $selectedColor['light'] }} !important;
            font-weight: bold;
            color: {{ $selectedColor['primary'] }};
        }

        .position-cell {
            font-weight: bold;
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
            margin-bottom: 8px;
        }
        
        .summary-col {
            display: table-cell;
            width: 50%;
            padding: 5px;
            vertical-align: top;
        }
        
        .summary-box {
            border: 2px solid {{ $selectedColor['primary'] }};
            padding: 6px;
            margin-bottom: 6px;
            background: rgba(255, 255, 255, .88);
        }
        
        .summary-title {
            font-weight: bold;
            background: {{ $selectedColor['light'] }};
            padding: 3px;
            margin-bottom: 4px;
            text-align: center;
            font-size: 10px;
            color: #111827;
            border-bottom: 1px solid {{ $selectedColor['secondary'] }};
        }
        
        .summary-item {
            padding: 2px 0;
            font-size: 9px;
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
            margin: 2px 0 8px;
        }

        .traits-col {
            display: table-cell;
            width: 50%;
            padding: 4px;
            vertical-align: top;
        }

        .traits-table {
            margin-bottom: 0;
            font-size: 8px;
        }

        .traits-table td {
            padding: 2px;
        }

        .rating-cell {
            width: 18px;
            font-size: 8px;
        }

        .rating-index {
            font-size: 7.5px;
            line-height: 1.35;
            margin-top: 4px;
            color: #111827;
        }
        
        /* Comments */
        .comments-section {
            margin-top: 8px;
        }
        
        .comment-box {
            border: 1.5px solid {{ $selectedColor['primary'] }};
            padding: 7px;
            margin-bottom: 6px;
            min-height: 58px;
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
            font-size: 10px;
            font-style: italic;
            min-height: 18px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 180px;
            margin-top: 16px;
            padding-top: 3px;
            font-size: 9px;
        }

        .next-term-box {
            text-align: center;
            margin-top: 8px;
            padding: 5px;
            font-size: 10px;
            border-top: 1px solid {{ $selectedColor['primary'] }};
            border-bottom: 1px solid {{ $selectedColor['primary'] }};
            background: {{ $selectedColor['light'] }};
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
    </style>
</head>
<body>
    @php
        $renderMode = $renderMode ?? 'pdf';
        $logoPath = $schoolSettings->school_logo ? public_path('storage/' . $schoolSettings->school_logo) : null;
        $studentPhotoPath = $reportCard->student->photo ? public_path('storage/' . $reportCard->student->photo) : null;
        $clubSociety = $reportCard->student->club_society ?: 'N/A';
        $favouriteColour = $reportCard->student->favourite_colour ?: 'N/A';
        $schoolAddress = $schoolSettings->school_address ?: 'No. 2 Airport Road, By Kosini Junction, Warri, Delta State, Nigeria';
        $schoolEmail = $schoolSettings->school_email ?: 'info@cambridgeinternationalschoolwarri.com';
        $schoolWebsite = $schoolSettings->school_website ?: 'cambridgeinternationalschoolwarri.com';
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
        } else {
            $schoolLogoSrc = ($logoPath && file_exists($logoPath))
                ? $logoPath
                : public_path('images/schoollogo.jpg');
            $studentPhotoSrc = ($studentPhotoPath && file_exists($studentPhotoPath))
                ? $studentPhotoPath
                : null;
        }
    @endphp
    <div class="page">
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
                            <span class="contact-label">Tel:</span> {{ $schoolSettings->school_phone ?: '08032897744' }}
                            <span class="contact-label"> | Email:</span>
                            <span class="contact-value">{{ $schoolEmail }}</span>
                        </div>
                        <div class="school-contact">
                            <span class="contact-label">Website:</span>
                            <span class="contact-value">{{ $schoolWebsite }}</span>
                        </div>

                        <div class="report-title">
                            {{ strtoupper($reportCard->term->name) }} STUDENT'S PERFORMANCE REPORT
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
            <div class="info-row">
                <div class="info-cell info-label">NAME:</div>
                <div class="info-cell" style="width: 45%;">{{ strtoupper($reportCard->student->name) }}</div>
                <div class="info-cell info-label">GENDER:</div>
                <div class="info-cell">{{ strtoupper($reportCard->student->sex ?? 'N/A') }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">CLASS:</div>
                <div class="info-cell">{{ $reportCard->class->display_name }}</div>
                <div class="info-cell info-label">SESSION:</div>
                <div class="info-cell">{{ $reportCard->session->name }}</div>
                <div class="info-cell info-label">ADMISSION NO:</div>
                <div class="info-cell">{{ $reportCard->student->registration_number }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">D.O.B:</div>
                <div class="info-cell">{{ $reportCard->student->date_of_birth ? $reportCard->student->date_of_birth->format('d-M-Y') : 'N/A' }}</div>
                <div class="info-cell info-label">AGE:</div>
                <div class="info-cell">{{ $reportCard->student->age ?? 'N/A' }}yrs</div>
                <div class="info-cell info-label">ATTENDANCE:</div>
                <div class="info-cell">{{ $reportCard->days_present }}/{{ $reportCard->days_school_opened }} ({{ number_format($reportCard->attendance_percentage, 1) }}%)</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">CLUB/SOCIETY:</div>
                <div class="info-cell" style="width: 45%;">{{ strtoupper($clubSociety) }}</div>
                <div class="info-cell info-label">FAV. COLOUR:</div>
                <div class="info-cell">{{ strtoupper($favouriteColour) }}</div>
            </div>
        </div>
        
        <!-- Scores Table -->
        <table class="scores-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 25%;">SUBJECTS</th>
                    <th colspan="2">TESTS</th>
                    <th rowspan="2">EXAM<br>(60)</th>
                    <th rowspan="2">TOTAL<br>(100)</th>
                    <th rowspan="2">GRADE</th>
                    <th rowspan="2">CLASS<br>AVG</th>
                    <th rowspan="2">POSITION</th>
                    <th rowspan="2">REMARK</th>
                </tr>
                <tr>
                    <th>1ST TEST<br>(30)</th>
                    <th>2ND TEST<br>(10)</th>
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
                    <td class="position-cell">{{ $score->position }}/{{ $score->total_students }}</td>
                    <td>{{ $score->remark }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Summary Section -->
        <div class="summary-section">
            <!-- Left Column -->
            <div class="summary-col">
                <!-- Attendance Summary -->
                <div class="summary-box">
                    <div class="summary-title">ATTENDANCE SUMMARY</div>
                    <div class="summary-item">No of Times School Opened: <strong>{{ $reportCard->days_school_opened }}</strong></div>
                    <div class="summary-item">No of Times Present: <strong>{{ $reportCard->days_present }}</strong></div>
                    <div class="summary-item">No of Times Absent: <strong>{{ $reportCard->days_absent }}</strong></div>
                </div>
                
                <!-- Grade Analysis -->
                <div class="summary-box">
                    <div class="summary-title">GRADE ANALYSIS</div>
                    @php
                        $gradeCount = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];
                        foreach($scores as $score) {
                            $gradeLetter = substr($score->grade, 0, 1);
                            if(isset($gradeCount[$gradeLetter])) {
                                $gradeCount[$gradeLetter]++;
                            }
                        }
                    @endphp
                    <table style="width: 100%; font-size: 9px;">
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
                
                <!-- Performance Summary -->
                <div class="summary-box">
                    <div class="summary-title">PERFORMANCE SUMMARY</div>
                    <div class="summary-item">Total Score: <strong>{{ number_format($reportCard->total_score, 1) }}</strong></div>
                    <div class="summary-item">Average: <strong>{{ number_format($reportCard->average_score, 1) }}%</strong></div>
                    <div class="summary-item">Position: <strong>{{ $reportCard->position }}/{{ $reportCard->total_students }}</strong></div>
                    <div class="summary-item">Grade: <strong>{{ $reportCard->overall_grade }}</strong></div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="summary-col">
                <!-- Grade Scale -->
                <div class="summary-box grade-scale">
                    <div class="summary-title">GRADE SCALE</div>
                    <table>
                        <tr>
                            <th>Score</th>
                            <th>Grade</th>
                            <th>Remark</th>
                        </tr>
                        <tr><td>75-100</td><td>A1</td><td>EXCELLENT</td></tr>
                        <tr><td>70-74</td><td>B2</td><td>VERY GOOD</td></tr>
                        <tr><td>65-69</td><td>B3</td><td>GOOD</td></tr>
                        <tr><td>60-64</td><td>C4</td><td>CREDIT</td></tr>
                        <tr><td>55-59</td><td>C5</td><td>CREDIT</td></tr>
                        <tr><td>50-54</td><td>C6</td><td>CREDIT</td></tr>
                        <tr><td>45-49</td><td>D7</td><td>PASS</td></tr>
                        <tr><td>40-44</td><td>E8</td><td>PASS</td></tr>
                        <tr><td>0-39</td><td>F9</td><td>FAIL</td></tr>
                    </table>
                </div>
                <!-- Grade Box -->
            <div style="border: 2px solid {{ $selectedColor['primary'] }}; text-align: center; background: rgba(255, 255, 255, .92);">
                <div style="background: {{ $selectedColor['primary'] }}; color: #fff; font-weight: bold; font-size: 10px; padding: 4px;">OVERALL GRADE</div>
                <div style="font-size: 16px; font-weight: bold; color: #cc0000; padding: 10px 0;">
                    {{ \App\Models\Subject::getRemark($reportCard->overall_grade) }}, {{ $reportCard->overall_grade }}
                </div>
            </div>
            </div>
        </div>

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
                    <div class="rating-index">
                        <strong>Rating Index:</strong> 5 - Excellent, 4 - Good, 3 - Average, 2 - Fair, 1 - Needs Improvement
                    </div>
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
                    <div class="rating-index">
                        Use the rating boxes for form teacher assessment before final issue.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Comments -->
        <div class="comments-section">
            <div class="comment-box">
                <div class="comment-label">Class Teacher's Remark:</div>
                <div class="comment-text">
                    {{ $reportCard->class_teacher_comment ?: '............................................................' }}
                </div>
                <div class="signature-line">
                    <strong>{{ $reportCard->class_teacher_name ?: '................................' }}</strong><br>
                    Signature: {{ $reportCard->class_teacher_signature ?: '___________________' }}
                    Date: {{ $reportCard->class_teacher_signature_date ? $reportCard->class_teacher_signature_date->format('d-M-Y') : '__________' }}
                </div>
            </div>
            
            <div class="comment-box">
                <div class="comment-label">Head Teacher's Remark:</div>
                <div class="comment-text">
                    {{ $reportCard->head_teacher_comment ?: '............................................................' }}
                </div>
                <div class="signature-line">
                    <strong>{{ $reportCard->head_teacher_name ?: '................................' }}</strong><br>
                    Signature: {{ $reportCard->head_teacher_signature ?: '___________________' }}
                    Date: {{ $reportCard->head_teacher_signature_date ? $reportCard->head_teacher_signature_date->format('d-M-Y') : '__________' }}
                </div>
            </div>
            
            <div class="next-term-box">
                <strong>Next Term Begins:</strong> {{ $reportCard->next_term_begins ? $reportCard->next_term_begins->format('l, d-M-Y') : 'TBA' }}
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
