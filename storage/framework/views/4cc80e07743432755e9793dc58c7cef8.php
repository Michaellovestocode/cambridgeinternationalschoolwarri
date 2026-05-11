<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report Card - <?php echo e($reportCard->student->name); ?></title>
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
        }
        
        .page {
            width: 210mm;
            padding: 10mm;
        }
        
        /* Header */
        .header {
            border: 2px solid <?php echo e($selectedColor['primary']); ?>;
            padding: 8px;
            margin-bottom: 8px;
            position: relative;
        }
        
        .header-content {
            text-align: center;
        }
        
        .school-logo {
            position: absolute;
            left: 10px;
            top: 10px;
            width: 60px;
            height: 60px;
        }
        
        .student-photo {
            position: absolute;
            right: 10px;
            top: 10px;
            width: 80px;
            height: 100px;
            border: 2px solid <?php echo e($selectedColor['primary']); ?>;
        }
        
        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: <?php echo e($selectedColor['primary']); ?>;
            margin-bottom: 3px;
        }
        
        .school-address {
            font-size: 9px;
            margin-bottom: 2px;
        }
        
        .report-title {
            font-size: 13px;
            font-weight: bold;
            margin-top: 8px;
            padding: 4px;
            background: <?php echo e($selectedColor['light']); ?>;
            border: 1px solid <?php echo e($selectedColor['secondary']); ?>;
        }
        
        /* Student Info */
        .student-info {
            margin-bottom: 8px;
            font-size: 10px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }
        
        .info-cell {
            display: table-cell;
            padding: 3px 8px;
            border: 1px solid #000;
        }
        
        .info-label {
            font-weight: bold;
            width: 25%;
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
            background: <?php echo e($selectedColor['primary']); ?>;
            color: #fff;
            font-weight: bold;
            font-size: 8px;
        }
        
        .subject-name {
            text-align: left;
            padding-left: 5px;
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
            border: 2px solid <?php echo e($selectedColor['primary']); ?>;
            padding: 6px;
            margin-bottom: 6px;
        }
        
        .summary-title {
            font-weight: bold;
            background: <?php echo e($selectedColor['light']); ?>;
            padding: 3px;
            margin-bottom: 4px;
            text-align: center;
            font-size: 10px;
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
        
        /* Comments */
        .comments-section {
            margin-top: 8px;
        }
        
        .comment-box {
            border: 1px solid #000;
            padding: 6px;
            margin-bottom: 6px;
            min-height: 40px;
        }
        
        .comment-label {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 3px;
        }
        
        .comment-text {
            font-size: 10px;
            font-style: italic;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 150px;
            margin-top: 20px;
            padding-top: 3px;
            font-size: 9px;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 2px solid <?php echo e($selectedColor['primary']); ?>;
            font-size: 9px;
        }
        
        .confidential {
            color: red;
            font-weight: bold;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <!-- School Logo -->
            <img src="<?php echo e($schoolSettings->getLogoUrl()); ?>" class="school-logo" alt="School Logo">
            
            <!-- Student Photo -->
            <?php if($reportCard->student->photo): ?>
                <img src="<?php echo e(Storage::url($reportCard->student->photo)); ?>" class="student-photo" alt="Student Photo">
            <?php else: ?>
                <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode($reportCard->student->name)); ?>&size=160&background=<?php echo e(str_replace('#', '', $selectedColor['primary'])); ?>&color=fff" class="student-photo" alt="Student">
            <?php endif; ?>
            
            <div class="header-content">
                <div class="school-name"><?php echo e($schoolSettings->school_name); ?></div>
                <div class="school-address"><?php echo e($schoolSettings->school_address); ?></div>
                <div class="school-address">Tel: <?php echo e($schoolSettings->school_phone); ?> | Email: <?php echo e($schoolSettings->school_email); ?></div>
                <div class="school-address">Website: <?php echo e($schoolSettings->school_website); ?></div>
                
                <div class="report-title">
                    <?php echo e(strtoupper($reportCard->term->name)); ?> STUDENT'S PERFORMANCE REPORT
                </div>
            </div>
        </div>
        
        <!-- Student Info -->
        <div class="student-info">
            <div class="info-row">
                <div class="info-cell info-label">NAME:</div>
                <div class="info-cell" style="width: 45%;"><?php echo e(strtoupper($reportCard->student->name)); ?></div>
                <div class="info-cell info-label">GENDER:</div>
                <div class="info-cell"><?php echo e(strtoupper($reportCard->student->sex ?? 'N/A')); ?></div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">CLASS:</div>
                <div class="info-cell"><?php echo e($reportCard->class->display_name); ?></div>
                <div class="info-cell info-label">SESSION:</div>
                <div class="info-cell"><?php echo e($reportCard->session->name); ?></div>
                <div class="info-cell info-label">ADMISSION NO:</div>
                <div class="info-cell"><?php echo e($reportCard->student->registration_number); ?></div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">D.O.B:</div>
                <div class="info-cell"><?php echo e($reportCard->student->date_of_birth ? $reportCard->student->date_of_birth->format('d-M-Y') : 'N/A'); ?></div>
                <div class="info-cell info-label">AGE:</div>
                <div class="info-cell"><?php echo e($reportCard->student->age ?? 'N/A'); ?>yrs</div>
                <div class="info-cell info-label">ATTENDANCE:</div>
                <div class="info-cell"><?php echo e($reportCard->days_present); ?>/<?php echo e($reportCard->days_school_opened); ?> (<?php echo e(number_format($reportCard->attendance_percentage, 1)); ?>%)</div>
            </div>
        </div>
        
        <!-- Scores Table -->
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 25%;">SUBJECTS</th>
                    <th colspan="3">CONTINUOUS ASSESSMENT</th>
                    <th rowspan="2">EXAM<br>(70)</th>
                    <th rowspan="2">TOTAL<br>(100)</th>
                    <th rowspan="2">GRADE</th>
                    <th rowspan="2">CLASS<br>AVG</th>
                    <th rowspan="2">POSITION</th>
                    <th rowspan="2">REMARK</th>
                </tr>
                <tr>
                    <th>CA1<br>(10)</th>
                    <th>CA2<br>(10)</th>
                    <th>CA3<br>(10)</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $scores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="subject-name"><?php echo e(strtoupper($score->subject->name)); ?></td>
                    <td><?php echo e(number_format($score->ca1, 1)); ?></td>
                    <td><?php echo e(number_format($score->ca2, 1)); ?></td>
                    <td><?php echo e(number_format($score->ca3, 1)); ?></td>
                    <td><?php echo e(number_format($score->exam, 1)); ?></td>
                    <td><strong><?php echo e(number_format($score->total, 1)); ?></strong></td>
                    <td class="
                        <?php if(substr($score->grade, 0, 1) == 'A'): ?> grade-a
                        <?php elseif(substr($score->grade, 0, 1) == 'B'): ?> grade-b
                        <?php elseif(substr($score->grade, 0, 1) == 'C'): ?> grade-c
                        <?php elseif($score->grade == 'F9'): ?> grade-f
                        <?php endif; ?>
                    "><strong><?php echo e($score->grade); ?></strong></td>
                    <td><?php echo e(number_format($score->class_average, 1)); ?></td>
                    <td><?php echo e($score->position); ?>/<?php echo e($score->total_students); ?></td>
                    <td><?php echo e($score->remark); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        
        <!-- Summary Section -->
        <div class="summary-section">
            <!-- Left Column -->
            <div class="summary-col">
                <!-- Attendance Summary -->
                <div class="summary-box">
                    <div class="summary-title">ATTENDANCE SUMMARY</div>
                    <div class="summary-item">No of Times School Opened: <strong><?php echo e($reportCard->days_school_opened); ?></strong></div>
                    <div class="summary-item">No of Times Present: <strong><?php echo e($reportCard->days_present); ?></strong></div>
                    <div class="summary-item">No of Times Absent: <strong><?php echo e($reportCard->days_absent); ?></strong></div>
                </div>
                
                <!-- Grade Analysis -->
                <div class="summary-box">
                    <div class="summary-title">GRADE ANALYSIS</div>
                    <?php
                        $gradeCount = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];
                        foreach($scores as $score) {
                            $gradeLetter = substr($score->grade, 0, 1);
                            if(isset($gradeCount[$gradeLetter])) {
                                $gradeCount[$gradeLetter]++;
                            }
                        }
                    ?>
                    <table style="width: 100%; font-size: 9px;">
                        <tr>
                            <td><strong>A:</strong> <?php echo e($gradeCount['A']); ?></td>
                            <td><strong>B:</strong> <?php echo e($gradeCount['B']); ?></td>
                            <td><strong>C:</strong> <?php echo e($gradeCount['C']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>D:</strong> <?php echo e($gradeCount['D']); ?></td>
                            <td><strong>E:</strong> <?php echo e($gradeCount['E']); ?></td>
                            <td><strong>F:</strong> <?php echo e($gradeCount['F']); ?></td>
                        </tr>
                    </table>
                </div>
                
                <!-- Performance Summary -->
                <div class="summary-box">
                    <div class="summary-title">PERFORMANCE SUMMARY</div>
                    <div class="summary-item">Total Score: <strong><?php echo e(number_format($reportCard->total_score, 1)); ?></strong></div>
                    <div class="summary-item">Average: <strong><?php echo e(number_format($reportCard->average_score, 1)); ?>%</strong></div>
                    <div class="summary-item">Position: <strong><?php echo e($reportCard->position); ?>/<?php echo e($reportCard->total_students); ?></strong></div>
                    <div class="summary-item">Grade: <strong><?php echo e($reportCard->overall_grade); ?></strong></div>
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
            <div style="border: 2px solid #003399; text-align: center;">
                <div style="background: #003399; color: #fff; font-weight: bold; font-size: 10px; padding: 4px;">GRADE</div>
                <div style="font-size: 16px; font-weight: bold; color: #cc0000; padding: 10px 0;">
                    <?php echo e(\App\Models\Subject::getRemark($reportCard->overall_grade)); ?>, <?php echo e($reportCard->overall_grade); ?>

                </div>
            </div>
            </div>
        </div>
        
        <!-- Comments -->
        <div class="comments-section">
            <div class="comment-box">
                <div class="comment-label">Class Teacher's Remark:</div>
                <div class="comment-text">
                    <?php echo e($reportCard->class_teacher_comment ?: '............................................................'); ?>

                </div>
                <div class="signature-line">
                    <strong><?php echo e($reportCard->class_teacher_name ?: '................................'); ?></strong><br>
                    Signature: <?php echo e($reportCard->class_teacher_signature ?: '___________________'); ?>

                    Date: <?php echo e($reportCard->class_teacher_signature_date ? $reportCard->class_teacher_signature_date->format('d-M-Y') : '__________'); ?>

                </div>
            </div>
            
            <div class="comment-box">
                <div class="comment-label">Head Teacher's Remark:</div>
                <div class="comment-text">
                    <?php echo e($reportCard->head_teacher_comment ?: '............................................................'); ?>

                </div>
                <div class="signature-line">
                    <strong><?php echo e($reportCard->head_teacher_name ?: '................................'); ?></strong><br>
                    Signature: <?php echo e($reportCard->head_teacher_signature ?: '___________________'); ?>

                    Date: <?php echo e($reportCard->head_teacher_signature_date ? $reportCard->head_teacher_signature_date->format('d-M-Y') : '__________'); ?>

                </div>
            </div>
            
            <div style="text-align: center; margin-top: 8px; font-size: 10px;">
                <strong>Next Term Begins:</strong> <?php echo e($reportCard->next_term_begins ? $reportCard->next_term_begins->format('l, d-M-Y') : 'TBA'); ?>

            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="confidential">[CONFIDENTIAL]</div>
            <div style="margin-top: 3px;">© <?php echo e(date('Y')); ?> <?php echo e($schoolSettings->school_name); ?></div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\report-cards\nigerian-pdf.blade.php ENDPATH**/ ?>