

<?php $__env->startSection('title', 'Create New Exam'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Create New Exam</h2>

        <?php if($errors->any()): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <?php endif; ?>

        <form action="<?php echo e(route('admin.exam.store')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Title *</label>
                <input type="text" name="title" value="<?php echo e(old('title')); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="e.g., Computer Science Mid-Term Exam" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="3" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          placeholder="Brief description of the exam"><?php echo e(old('description')); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                    <select name="subject_id" id="subject_id"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           required>
                        <option value="">-- Select a Subject --</option>
                        <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($subject->id); ?>" <?php echo e(old('subject_id') == $subject->id ? 'selected' : ''); ?>>
                            <?php echo e($subject->name); ?> (<?php echo e($subject->code); ?>)
                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Minutes) *</label>
                    <input type="number" name="duration_minutes" value="<?php echo e(old('duration_minutes', 60)); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           min="1" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Marks *</label>
                    <input type="number" name="total_marks" value="<?php echo e(old('total_marks', 100)); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           min="1" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pass Mark *</label>
                    <input type="number" name="pass_mark" value="<?php echo e(old('pass_mark', 50)); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           min="0" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                    <input type="datetime-local" name="start_date" value="<?php echo e(old('start_date', now()->format('Y-m-d\TH:i'))); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                    <input type="datetime-local" name="end_date" value="<?php echo e(old('end_date', now()->addDays(7)->format('Y-m-d\TH:i'))); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Instructions</label>
                <textarea name="instructions" rows="4" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          placeholder="Instructions for students (e.g., Answer all questions, No use of calculators)"><?php echo e(old('instructions')); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Assign to Classes *</label>
                <div id="classes_list" class="space-y-2">
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center" data-class-option data-class-id="<?php echo e($class->id); ?>">
                        <input type="checkbox" name="classes[]" value="<?php echo e($class->id); ?>" 
                               <?php echo e(in_array($class->id, old('classes', [])) ? 'checked' : ''); ?>

                               class="mr-2 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <span><?php echo e($class->display_name); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <p id="no_classes_message" class="hidden text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-3">
                    Select a subject to see the classes available for it.
                </p>
                <?php $__errorArgs = ['classes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="border-t pt-4">
                <label class="flex items-start">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           <?php echo e(old('is_active', true) ? 'checked' : ''); ?>

                           class="mt-1 mr-2 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <span>
                        <span class="font-medium text-gray-800">Publish exam now</span>
                        <span class="block text-sm text-gray-600">Uncheck this to hide the exam from students until you publish it later.</span>
                    </span>
                </label>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
                    Create Exam & Add Questions
                </button>
                <a href="<?php echo e(route('admin.exams')); ?>" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const classesBySubject = <?php echo json_encode($classesBySubject, 15, 512) ?>;
    const subjectSelect = document.getElementById('subject_id');
    const classOptions = Array.from(document.querySelectorAll('[data-class-option]'));
    const noClassesMessage = document.getElementById('no_classes_message');

    function syncClassOptions() {
        const subjectId = subjectSelect.value;
        const allowedClassIds = new Set((classesBySubject[subjectId] || []).map(function (classItem) {
            return String(classItem.id);
        }));
        let visibleCount = 0;

        classOptions.forEach(function (option) {
            const checkbox = option.querySelector('input[type="checkbox"]');
            const isVisible = subjectId !== '' && allowedClassIds.has(String(option.dataset.classId));
            option.classList.toggle('hidden', !isVisible);

            if (!isVisible) {
                checkbox.checked = false;
            } else {
                visibleCount++;
            }
        });

        noClassesMessage.classList.toggle('hidden', subjectId !== '' && visibleCount > 0);
        noClassesMessage.textContent = subjectId === ''
            ? 'Select a subject to see the classes available for it.'
            : 'No class is assigned to you for this subject.';
    }

    subjectSelect.addEventListener('change', syncClassOptions);
    syncClassOptions();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\exams\create.blade.php ENDPATH**/ ?>