<?php $__env->startSection('title', $learningSession->title); ?>

<?php $__env->startSection('content'); ?>
<form action="<?php echo e(route('student.learning.submit', $learningSession)); ?>" method="POST" class="space-y-6">
    <?php echo csrf_field(); ?>

    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-cyan-600 to-emerald-600 text-white p-8">
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <p class="text-cyan-50 font-semibold"><?php echo e($learningSession->subject->name ?? 'Subject'); ?></p>
                    <h1 class="text-3xl font-bold mt-2"><?php echo e($learningSession->title); ?></h1>
                    <p class="text-cyan-50 mt-2"><?php echo e($learningSession->schoolClass->display_name ?? 'Your class'); ?> • <?php echo e($learningSession->topic); ?> • <?php echo e($learningSession->estimated_minutes); ?> mins</p>
                </div>
                <a href="<?php echo e(route('student.learning.index')); ?>" class="bg-white/15 hover:bg-white/25 px-4 py-2 rounded-lg font-semibold">Back</a>
            </div>
        </div>

        <div class="p-6 md:p-8 space-y-6">
            <?php if($learningSession->description): ?>
                <p class="text-gray-700 leading-7"><?php echo e($learningSession->description); ?></p>
            <?php endif; ?>

            <?php if($learningSession->learning_goals): ?>
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-5">
                <h2 class="font-bold text-emerald-900 mb-3">Learning Goals</h2>
                <ul class="list-disc list-inside space-y-1 text-emerald-900">
                    <?php $__currentLoopData = preg_split('/\r\n|\r|\n/', $learningSession->learning_goals); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $goal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(trim($goal) !== ''): ?>
                            <li><?php echo e(trim($goal)); ?></li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <?php endif; ?>

            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-3">Lesson</h2>
                <div class="prose max-w-none text-gray-700 leading-8 whitespace-pre-line"><?php echo e($learningSession->lesson_content ?: 'No lesson content has been added yet.'); ?></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Practice Questions</h2>
                <p class="text-gray-600 text-sm">Choose the best answer for each question.</p>
            </div>
            <span class="bg-cyan-100 text-cyan-800 px-4 py-2 rounded-full text-sm font-bold"><?php echo e($learningSession->questions->count()); ?> questions</span>
        </div>

        <div class="space-y-6">
            <?php $__empty_1 = true; $__currentLoopData = $learningSession->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="border border-gray-200 rounded-xl p-5">
                <p class="font-bold text-gray-900 mb-4"><?php echo e($loop->iteration); ?>. <?php echo e($question->question_text); ?></p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-start gap-3 border rounded-lg px-4 py-3 cursor-pointer hover:bg-cyan-50">
                        <input type="radio" name="answers[<?php echo e($question->id); ?>]" value="<?php echo e($key); ?>" class="mt-1">
                        <span><strong><?php echo e($key); ?>.</strong> <?php echo e($option); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-10 text-gray-500">No questions have been added to this session yet.</div>
            <?php endif; ?>
        </div>

        <?php if($learningSession->questions->count() > 0): ?>
        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg">
                Submit Practice
            </button>
        </div>
        <?php endif; ?>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\student\learning-sessions\show.blade.php ENDPATH**/ ?>