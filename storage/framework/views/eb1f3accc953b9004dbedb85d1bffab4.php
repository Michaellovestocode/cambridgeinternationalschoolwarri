

<?php $__env->startSection('title', 'Exam Result'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <?php if(!$canViewReleasedResults): ?>
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-green-100 text-green-700 flex items-center justify-center mb-4">
            <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Exam Submitted Successfully</h2>
        <p class="text-gray-600"><?php echo e($attempt->exam->title); ?> has been submitted.</p>
        <p class="text-sm text-gray-500 mt-2">Your score and script will appear here only after your teacher releases the result.</p>
        <div class="mt-6">
            <a href="<?php echo e(route('student.dashboard')); ?>"
               class="inline-block bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition">
                Back to Dashboard
            </a>
        </div>
    </div>
    <?php else: ?>
    <!-- Result Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-2"><?php echo e($attempt->exam->title); ?></h2>
            <p class="text-gray-600"><?php echo e($attempt->exam->subject); ?></p>







            <?php if($attempt->isGraded() || ($attempt->objective_score !== null && $attempt->objective_score > 0)): ?>
    <!-- Score Display -->
    <div class="mt-6 mb-4">
        <div class="inline-block <?php echo e($attempt->total_score >= $attempt->exam->pass_mark ? 'bg-green-100' : 'bg-red-100'); ?> rounded-full px-8 py-4">
            <div class="text-5xl font-bold <?php echo e($attempt->total_score >= $attempt->exam->pass_mark ? 'text-green-600' : 'text-red-600'); ?>">
                <?php echo e($attempt->total_score); ?><span class="text-2xl">/<?php echo e($attempt->exam->total_marks); ?></span>
            </div>
            <?php if(!$attempt->isGraded()): ?>
                <div class="text-sm mt-2 text-orange-800">
                    ⏳ Partial Result (Coding questions pending manual grading)
                </div>
            <?php else: ?>
                <div class="text-sm mt-2 <?php echo e($attempt->total_score >= $attempt->exam->pass_mark ? 'text-green-800' : 'text-red-800'); ?>">
                    <?php if($attempt->total_score >= $attempt->exam->pass_mark): ?>
                        ✓ Passed (Pass mark: <?php echo e($attempt->exam->pass_mark); ?>)
                    <?php else: ?>
                        ✗ Failed (Pass mark: <?php echo e($attempt->exam->pass_mark); ?>)
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Score Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div class="bg-blue-50 p-4 rounded-lg <?php echo e($attempt->isGraded() ? '' : 'border-2 border-green-500'); ?>">
            <div class="text-sm text-gray-600">Objective Score</div>
            <div class="text-2xl font-bold text-blue-600">
                <?php echo e($attempt->objective_score ?? 0); ?><span class="text-lg text-blue-400">/<?php echo e($objectiveTotal); ?></span>
            </div>
            <?php if(!$attempt->isGraded()): ?>
                <div class="text-xs text-green-600 mt-1 font-semibold">✅ Auto-graded</div>
            <?php endif; ?>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg <?php echo e($attempt->isGraded() ? '' : 'border-2 border-orange-500'); ?>">
            <div class="text-sm text-gray-600">Subjective Score</div>
            <div class="text-2xl font-bold text-purple-600">
                <?php echo e($attempt->subjective_score ?? 0); ?><span class="text-lg text-purple-400">/<?php echo e($subjectiveTotal); ?></span>
            </div>
            <?php if(!$attempt->isGraded()): ?>
                <div class="text-xs text-orange-600 mt-1 font-semibold">⏳ Awaiting teacher grading</div>
            <?php endif; ?>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600">Percentage</div>
            <div class="text-2xl font-bold text-gray-800">
                <?php
                    $percentage = $attempt->exam->total_marks > 0 
                        ? round(($attempt->total_score / $attempt->exam->total_marks) * 100, 1) 
                        : 0;
                ?>
                <?php echo e($percentage); ?>%
            </div>
            <?php if(!$attempt->isGraded()): ?>
                <div class="text-xs text-gray-600 mt-1">Partial percentage</div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- No Scores Yet (All Subjective) -->
    <div class="mt-6 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-lg">
        <p class="font-semibold">⏳ Grading in Progress</p>
        <p class="text-sm mt-1">Your exam has been submitted and is awaiting manual grading by your teacher.</p>
    </div>
<?php endif; ?>

            
           






        </div>
    </div>

    <!-- Exam Details -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Exam Details</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-600">Submitted:</span>
                <p class="font-semibold"><?php echo e($attempt->submitted_at->format('d M Y, h:i A')); ?></p>
            </div>
            <div>
                <span class="text-gray-600">Duration:</span>
                <p class="font-semibold"><?php echo e($attempt->exam->duration_minutes); ?> minutes</p>
            </div>
            <div>
                <span class="text-gray-600">Total Questions:</span>
                <p class="font-semibold"><?php echo e($attempt->exam->questions->count()); ?></p>
            </div>
           <div>
    <span class="text-gray-600">Status:</span>
    <p class="font-semibold">
        <?php if($attempt->isGraded()): ?>
            <span class="text-green-600">Graded</span>
        <?php else: ?>
            <span class="text-yellow-600">Pending</span>
        <?php endif; ?>
    </p>
</div>
        </div>
    </div>

    <?php if($attempt->isGraded()): ?>
    <!-- Answer Review -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Answer Review</h3>
        
        <?php $__currentLoopData = $attempt->answers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $question = $answer->question;
        ?>
        <div class="border-b pb-6 mb-6 last:border-b-0 last:pb-0 last:mb-0">
            <div class="flex items-start mb-3">
                <span class="bg-gray-200 text-gray-700 rounded-full w-8 h-8 flex items-center justify-center font-semibold mr-3 flex-shrink-0">
                    <?php echo e($index + 1); ?>

                </span>
                <div class="flex-1">
                    <div class="text-gray-800 font-medium"><?php echo $question->formattedQuestionText(); ?></div>

<!-- Show reference image in results too -->
<?php if($question->image_path): ?>
<div class="mt-3 border rounded-lg overflow-hidden">
    <div class="bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Reference Image</div>
    <div class="p-2 bg-white">
        <img src="<?php echo e($question->getImageUrl()); ?>" 
             alt="Reference" 
             class="max-h-40 object-contain border border-gray-200 rounded">
    </div>
</div>
<?php endif; ?>
                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">
                            <?php echo e(ucwords(str_replace('_', ' ', $question->question_type))); ?>

                        </span>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                            <?php echo e($question->marks); ?> <?php echo e($question->marks == 1 ? 'mark' : 'marks'); ?>

                        </span>
                        <?php if($answer->marks_obtained !== null): ?>
                            <span class="text-xs <?php echo e($answer->is_correct || $answer->marks_obtained > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?> px-2 py-1 rounded font-semibold">
                                Scored: <?php echo e($answer->marks_obtained); ?>/<?php echo e($question->marks); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="ml-11">
                <!-- Show Question Options (for MCQ) -->
                <?php if($question->question_type === 'multiple_choice' && $question->options): ?>
                    <div class="mb-3 space-y-1">
                        <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p class="text-sm p-2 rounded <?php echo e($key === $question->correct_answer ? 'bg-green-50 text-green-800 font-semibold' : ($key === $answer->answer_text ? 'bg-red-50 text-red-800' : 'text-gray-600')); ?>">
                            <?php echo e($key); ?>. <?php echo e($option); ?>

                            <?php if($key === $question->correct_answer): ?>
                                <span class="text-xs ml-2">✓ Correct Answer</span>
                            <?php endif; ?>
                            <?php if($key === $answer->answer_text && $key !== $question->correct_answer): ?>
                                <span class="text-xs ml-2">✗ Your Answer</span>
                            <?php endif; ?>
                        </p>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                <!-- Student's Answer -->
                <?php if($answer->answer_text): ?>
                    <div class="bg-gray-50 p-4 rounded-lg mb-3">
                        <p class="text-sm font-semibold text-gray-700 mb-1">Your Answer:</p>
                        <?php if($question->question_type === 'multiple_choice'): ?>
                            <p class="text-gray-800">
                                <strong><?php echo e($answer->answer_text); ?>.</strong> 
                                <?php echo e($question->options[$answer->answer_text] ?? 'N/A'); ?>

                            </p>
                        <?php elseif($question->question_type === 'coding'): ?>
                            <pre class="bg-gray-800 text-gray-200 p-3 rounded font-mono text-sm overflow-x-auto"><?php echo e($answer->answer_text); ?></pre>
                        <?php else: ?>
                            <p class="text-gray-800 whitespace-pre-wrap"><?php echo e($answer->answer_text); ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-red-50 p-4 rounded-lg mb-3">
                        <p class="text-sm text-red-800">Not Answered</p>
                    </div>
                <?php endif; ?>

                <!-- Correct Answer (for objective questions) -->
                <?php if($question->question_type === 'fill_blank' && $question->correct_answer): ?>
                    <div class="bg-green-50 p-4 rounded-lg mb-3">
                        <p class="text-sm font-semibold text-green-700 mb-1">Correct Answer:</p>
                        <p class="text-green-800"><?php echo e($question->correct_answer); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Teacher's Feedback -->
                <?php if($answer->feedback): ?>
                    <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                        <p class="text-sm font-semibold text-blue-700 mb-1">Teacher's Feedback:</p>
                        <p class="text-blue-800"><?php echo e($answer->feedback); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <!-- Action Buttons -->
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex flex-wrap gap-4 justify-center">
        <a href="<?php echo e(route('student.dashboard')); ?>" 
           class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition">
            ← Back to Dashboard
        </a>
    </div>
</div>
    <?php endif; ?>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    @media print {
        nav, .no-print, button {
            display: none !important;
        }
        body {
            background: white;
        }
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/student/result.blade.php ENDPATH**/ ?>