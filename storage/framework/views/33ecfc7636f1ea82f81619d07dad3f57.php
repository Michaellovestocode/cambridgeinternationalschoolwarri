

<?php $__env->startSection('title', 'Select Class and Subject'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">📋 Select Class and Subject</h1>
        <p class="text-gray-600">Choose the class and subject to enter scores for</p>
    </div>

    <!-- Session/Term Info -->
    <?php if($activeSession && $activeTerm): ?>
    <div class="bg-blue-50 border border-blue-300 rounded-lg p-4 mb-6">
        <p class="text-blue-800">
            <strong>Current Session:</strong> <?php echo e($activeSession->name ?? 'N/A'); ?> | 
            <strong>Term:</strong> <?php echo e($activeTerm->name ?? 'N/A'); ?>

        </p>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="<?php echo e(route('teacher.scores.enter')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            <!-- Class Selection -->
            <div>
                <label for="class_id" class="block text-gray-700 font-bold mb-2">
                    <span class="text-red-500">*</span> Select Class
                </label>
                <select name="class_id" id="class_id" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 <?php $__errorArgs = ['class_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                    <option value="">-- Choose a class --</option>
                    <?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <option value="<?php echo e($class->id); ?>" <?php echo e(old('class_id') == $class->id ? 'selected' : ''); ?>>
                            <?php echo e($class->name); ?> - <?php echo e($class->description ?? 'No description'); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <option value="" disabled>No classes available</option>
                    <?php endif; ?>
                </select>
                <?php $__errorArgs = ['class_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="text-red-500 text-sm mt-2 block"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Subject Selection -->
            <div>
                <label for="subject_id" class="block text-gray-700 font-bold mb-2">
                    <span class="text-red-500">*</span> Select Subject
                </label>
                <select name="subject_id" id="subject_id" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                    <option value="">-- Choose a subject --</option>
                    <?php $__empty_1 = true; $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <option value="<?php echo e($subject->id); ?>" <?php echo e(old('subject_id') == $subject->id ? 'selected' : ''); ?>>
                            <?php echo e($subject->name); ?> (<?php echo e($subject->code ?? 'N/A'); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <option value="" disabled>No subjects available</option>
                    <?php endif; ?>
                </select>
                <?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="text-red-500 text-sm mt-2 block"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-300 rounded-lg p-4">
                <p class="text-blue-800">
                    <strong>Note:</strong> You can enter and edit scores multiple times before submission. 
                    Once submitted, scores cannot be modified.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    ➜ Continue to Score Entry
                </button>
                <a href="<?php echo e(route('teacher.scores.dashboard')); ?>" class="flex-1 text-center bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 px-6 rounded-lg transition">
                    ← Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="mt-8 bg-gray-50 rounded-lg p-6 border border-gray-300">
        <h3 class="text-lg font-bold text-gray-800 mb-3">❓ Need Help?</h3>
        <ul class="text-gray-700 space-y-2">
            <li>• Make sure you select both a class and a subject</li>
            <li>• You can only enter scores for classes and subjects you're assigned to</li>
            <li>• Contact the admin if you need access to additional classes</li>
        </ul>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/teacher/scores/select.blade.php ENDPATH**/ ?>