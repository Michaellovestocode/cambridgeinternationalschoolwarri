<?php $__env->startSection('title', 'Edit Learning Session'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Learning Session</h1>
                <p class="text-gray-600 text-sm"><?php echo e($learningSession->schoolClass->display_name ?? 'No class'); ?> • <?php echo e($learningSession->subject->name ?? 'N/A'); ?> • <?php echo e($learningSession->topic); ?></p>
            </div>
            <a href="<?php echo e(route('admin.learning-sessions.index')); ?>" class="text-blue-600 hover:underline">Back to sessions</a>
        </div>

        <?php echo $__env->make('admin.learning-sessions.partials.form', [
            'action' => route('admin.learning-sessions.update', $learningSession),
            'method' => 'PUT',
            'learningSession' => $learningSession,
            'subjects' => $subjects,
            'classes' => $classes,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Add Practice Question</h2>
            <form action="<?php echo e(route('admin.learning-sessions.questions.store', $learningSession)); ?>" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Question</label>
                    <textarea name="question_text" rows="4" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500"><?php echo e(old('question_text')); ?></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php $__currentLoopData = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Option <?php echo e($label); ?><?php echo e(in_array($label, ['A', 'B']) ? '' : ' (optional)'); ?></label>
                        <input type="text" name="option_<?php echo e($key); ?>" value="<?php echo e(old('option_' . $key)); ?>" <?php echo e(in_array($label, ['A', 'B']) ? 'required' : ''); ?> class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Correct Option</label>
                        <select name="correct_option" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                            <?php $__currentLoopData = ['A', 'B', 'C', 'D']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($option); ?>" <?php if(old('correct_option') === $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Order</label>
                        <input type="number" min="0" name="order" value="<?php echo e(old('order', $learningSession->questions->count() + 1)); ?>" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Explanation</label>
                    <textarea name="explanation" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500"><?php echo e(old('explanation')); ?></textarea>
                </div>
                <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-3 rounded-lg font-bold">Add Question</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Question Bank</h2>
            <div class="space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $learningSession->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between gap-4">
                        <p class="font-semibold text-gray-900"><?php echo e($loop->iteration); ?>. <?php echo e($question->question_text); ?></p>
                        <form action="<?php echo e(route('admin.learning-sessions.questions.destroy', $question)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 hover:underline text-sm" onclick="return confirm('Delete this question?')">Delete</button>
                        </form>
                    </div>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                        <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="<?php echo e($key === $question->correct_option ? 'bg-green-50 text-green-800' : 'bg-gray-50 text-gray-700'); ?> rounded px-3 py-2">
                                <strong><?php echo e($key); ?>.</strong> <?php echo e($option); ?>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php if($question->explanation): ?>
                        <p class="mt-3 text-sm text-gray-600"><strong>Explanation:</strong> <?php echo e($question->explanation); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-gray-500 text-center py-10">No questions added yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\learning-sessions\edit.blade.php ENDPATH**/ ?>