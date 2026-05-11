<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #1e40af;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .statistics {
            margin: 20px 0;
            padding: 15px;
            background-color: #f0f9ff;
            border-radius: 5px;
        }
        .statistics h3 {
            margin-top: 0;
            color: #1e40af;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 15px;
            margin: 15px 0;
        }
        .stat-box {
            padding: 10px;
            background-color: white;
            border-left: 4px solid #2563eb;
            border-radius: 3px;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table thead {
            background-color: #1e40af;
            color: white;
        }
        table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .page-break {
            page-break-after: always;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-pass {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-fail {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📊 Examination Results Report</h1>
        <p>Generated on <?php echo e(now()->format('M d, Y \a\t H:i')); ?></p>
    </div>

    <!-- Overall Statistics -->
    <div class="statistics">
        <h3>Overall Statistics</h3>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-label">Total Attempts</div>
                <div class="stat-value"><?php echo e($statistics['total_attempts']); ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Graded</div>
                <div class="stat-value"><?php echo e($statistics['graded']); ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Average Score</div>
                <div class="stat-value"><?php echo e($statistics['average']); ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Pass Rate</div>
                <div class="stat-value"><?php echo e($statistics['pass_rate']); ?>%</div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <h2>Student Results</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 20%">Student Name</th>
                <th style="width: 15%">Registration</th>
                <th style="width: 20%">Exam</th>
                <th style="width: 15%">Class</th>
                <th style="width: 10%">Score</th>
                <th style="width: 10%">Percentage</th>
                <th style="width: 10%">Result</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $attempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($attempt->user->name); ?></td>
                <td><?php echo e($attempt->user->registration_number); ?></td>
                <td><?php echo e($attempt->exam->title); ?></td>
                <td><?php echo e($attempt->user->class->display_name ?? 'N/A'); ?></td>
                <td><?php echo e($attempt->total_score ?? '—'); ?>/<?php echo e($attempt->exam->total_marks); ?></td>
                <td>
                    <?php if($attempt->total_score !== null): ?>
                        <?php echo e($attempt->exam->total_marks > 0 ? round(($attempt->total_score / $attempt->exam->total_marks) * 100, 1) : 0); ?>%
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($attempt->status === 'graded'): ?>
                        <span class="status-badge <?php echo e($attempt->total_score >= $attempt->exam->pass_mark ? 'status-pass' : 'status-fail'); ?>">
                            <?php echo e($attempt->total_score >= $attempt->exam->pass_mark ? 'PASS' : 'FAIL'); ?>

                        </span>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>This is an automatically generated report. For more details, please access the Results Portal.</p>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\results\export-pdf.blade.php ENDPATH**/ ?>