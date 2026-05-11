<?php $__env->startSection('title', 'Learning Result'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="flex flex-wrap justify-between items-center gap-6">
            <div>
                <p class="text-cyan-700 font-semibold"><?php echo e($attempt->learningSession->subject->name ?? 'Subject'); ?></p>
                <h1 class="text-3xl font-bold text-gray-900 mt-1"><?php echo e($attempt->learningSession->title); ?></h1>
                <p class="text-gray-600 mt-2">Completed <?php echo e($attempt->completed_at?->format('d M Y, h:i A')); ?></p>
            </div>
            <div class="text-center bg-cyan-50 rounded-2xl px-8 py-5">
                <div class="text-4xl font-black text-cyan-700"><?php echo e($attempt->percentage()); ?>%</div>
                <div class="text-sm text-gray-600 mt-1"><?php echo e($attempt->score); ?>/<?php echo e($attempt->total_questions); ?> correct</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Corrections</h2>
        <div class="space-y-5">
            <?php $__currentLoopData = $attempt->answers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php ($question = $answer->question); ?>
            <div class="border rounded-xl p-5 <?php echo e($answer->is_correct ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'); ?>">
                <div class="flex flex-wrap justify-between gap-3 mb-4">
                    <p class="font-bold text-gray-900"><?php echo e($loop->iteration); ?>. <?php echo e($question->question_text); ?></p>
                    <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo e($answer->is_correct ? 'bg-green-600 text-white' : 'bg-red-600 text-white'); ?>">
                        <?php echo e($answer->is_correct ? 'Correct' : 'Review'); ?>

                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-lg px-4 py-3 <?php echo e($key === $question->correct_option ? 'bg-white border-2 border-green-500 text-green-900' : ($key === $answer->selected_option ? 'bg-white border-2 border-red-400 text-red-900' : 'bg-white border border-gray-100 text-gray-700')); ?>">
                        <strong><?php echo e($key); ?>.</strong> <?php echo e($option); ?>

                        <?php if($key === $question->correct_option): ?>
                            <span class="font-bold"> - Correct answer</span>
                        <?php elseif($key === $answer->selected_option): ?>
                            <span class="font-bold"> - Your answer</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if(!$answer->selected_option): ?>
                    <p class="mt-3 text-sm text-red-700 font-semibold">You did not answer this question.</p>
                <?php endif; ?>
                <?php if($question->explanation): ?>
                    <p class="mt-4 text-sm text-gray-700"><strong>Explanation:</strong> <?php echo e($question->explanation); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="<?php echo e(route('student.learning.show', $attempt->learningSession)); ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-bold">Retake Session</a>
            <a href="<?php echo e(route('student.learning.index')); ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-3 rounded-xl font-bold">More Sessions</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\student\learning-sessions\result.blade.php ENDPATH**/ ?>