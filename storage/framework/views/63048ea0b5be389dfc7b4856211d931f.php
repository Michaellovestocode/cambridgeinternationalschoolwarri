

<?php $__env->startSection('title', 'Assign Teachers to ' . $subject->name); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">👥 Assign Teachers</h2>
            <p class="text-gray-600 mt-2">Subject: <strong><?php echo e($subject->name); ?></strong></p>
        </div>

        <form action="<?php echo e(route('admin.subjects.update-teachers', $subject->id)); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Select Teachers *</label>
                <div class="border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center mb-3">
                        <input type="checkbox" name="teachers[]" value="<?php echo e($teacher->id); ?>"
                               <?php echo e($subject->teachers->contains($teacher->id) ? 'checked' : ''); ?>

                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <label class="ml-3 text-sm text-gray-700">
                            <div class="font-medium"><?php echo e($teacher->name); ?></div>
                            <div class="text-xs text-gray-600"><?php echo e($teacher->email); ?> (<?php echo e($teacher->registration_number); ?>)</div>
                        </label>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-gray-500">No teachers found.</p>
                    <?php endif; ?>
                </div>
                <?php $__errorArgs = ['teachers'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold">
                    Assign Teachers
                </button>
                <a href="<?php echo e(route('admin.subjects.index')); ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/subjects/assign.blade.php ENDPATH**/ ?>