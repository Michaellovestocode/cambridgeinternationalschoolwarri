<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Exam Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #16a34a;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #16a34a;
            margin: 0;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-section td {
            padding: 5px;
        }
        .score-box {
            background: <?php echo e($attempt->total_score >= $attempt->exam->pass_mark ? '#dcfce7' : '#fee2e2'); ?>;
            border: 2px solid <?php echo e($attempt->total_score >= $attempt->exam->pass_mark ? '#16a34a' : '#dc2626'); ?>;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .score-box .score {
            font-size: 36px;
            font-weight: bold;
            color: <?php echo e($attempt->total_score >= $attempt->exam->pass_mark ? '#16a34a' : '#dc2626'); ?>;
        }
        .question {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .question-header {
            background: #f3f4f6;
            padding: 10px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .answer-box {
            background: #f9fafb;
            padding: 10px;
            margin: 10px 0;
            border-left: 3px solid #3b82f6;
        }
        .correct-answer {
            background: #dcfce7;
            border-left-color: #16a34a;
        }
        .feedback {
            background: #dbeafe;
            padding: 10px;
            margin-top: 10px;
            border-left: 3px solid #3b82f6;
        }
        .marks {
            font-weight: bold;
            color: #16a34a;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎓 Nigerian CBT System</h1>
        <h2><?php echo e($attempt->exam->title); ?></h2>
        <p><?php echo e($attempt->exam->subject); ?></p>
    </div>

    <div class="info-section">
        <table>
            <tr>
                <td><strong>Student Name:</strong></td>
                <td><?php echo e($attempt->user->name); ?></td>
                <td><strong>Registration:</strong></td>
                <td><?php echo e($attempt->user->registration_number); ?></td>
            </tr>
            <tr>
                <td><strong>Date Submitted:</strong></td>
                <td><?php echo e($attempt->submitted_at->format('d M Y, h:i A')); ?></td>
                <td><strong>Duration:</strong></td>
                <td><?php echo e($attempt->exam->duration_minutes); ?> minutes</td>
            </tr>
        </table>
    </div>

    <div class="score-box">
        <div class="score"><?php echo e($attempt->total_score); ?>/<?php echo e($attempt->exam->total_marks); ?></div>
        <div style="font-size: 18px; margin-top: 10px;">
            <?php if($attempt->total_score >= $attempt->exam->pass_mark): ?>
                ✓ PASSED (Pass mark: <?php echo e($attempt->exam->pass_mark); ?>)
            <?php else: ?>
                ✗ FAILED (Pass mark: <?php echo e($attempt->exam->pass_mark); ?>)
            <?php endif; ?>
        </div>
        <div style="margin-top: 10px;">
            Percentage: <?php echo e(round(($attempt->total_score / $attempt->exam->total_marks) * 100, 1)); ?>%
        </div>
    </div>

    <h3>Score Breakdown</h3>
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td><strong>Objective Score:</strong></td>
            <td><?php echo e($attempt->objective_score ?? 0); ?></td>
            <td><strong>Subjective Score:</strong></td>
            <td><?php echo e($attempt->subjective_score ?? 0); ?></td>
        </tr>
    </table>

    <h3>Detailed Review</h3>
    <?php $__currentLoopData = $attempt->answers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $question = $answer->question;
    ?>
    <div class="question">
        <div class="question-header">
            Question <?php echo e($index + 1); ?> - <?php echo e(ucwords(str_replace('_', ' ', $question->question_type))); ?> (<?php echo e($question->marks); ?> marks)
        </div>
        <p><strong><?php echo $question->formattedQuestionText(); ?></strong></p>

        <?php if($answer->answer_text): ?>
        <div class="answer-box">
            <strong>Your Answer:</strong><br>
            <?php echo e($answer->answer_text); ?>

        </div>
        <?php else: ?>
        <div class="answer-box" style="border-left-color: #dc2626; background: #fee2e2;">
            <strong>Not Answered</strong>
        </div>
        <?php endif; ?>

        <?php if($question->question_type === 'multiple_choice' || $question->question_type === 'fill_blank'): ?>
        <div class="answer-box correct-answer">
            <strong>Correct Answer:</strong> <?php echo e($question->correct_answer); ?>

        </div>
        <?php endif; ?>

        <p class="marks">Marks Obtained: <?php echo e($answer->marks_obtained ?? 0); ?>/<?php echo e($question->marks); ?></p>

        <?php if($answer->feedback): ?>
        <div class="feedback">
            <strong>Teacher's Feedback:</strong><br>
            <?php echo e($answer->feedback); ?>

        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <div class="footer">
        Generated on <?php echo e(now()->format('d M Y, h:i A')); ?><br>
        Nigerian CBT System - Exam Results
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\student\exports\result-pdf.blade.php ENDPATH**/ ?>