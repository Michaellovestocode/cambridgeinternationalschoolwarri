<?php $__env->startSection('title', 'Assign Classes to ' . $teacher->name); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Assign Classes</h2>
            <p class="text-gray-600 mt-2">Teacher: <strong><?php echo e($teacher->name); ?></strong></p>
            <p class="text-gray-600">Email: <?php echo e($teacher->email); ?></p>
        </div>

        <?php if($errors->any()): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <?php endif; ?>

        <form action="<?php echo e(route('admin.teacher.update-classes', $teacher->id)); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Select Classes This Teacher Teaches</label>
                <p class="text-xs text-gray-600 mb-3">These classes will appear when the teacher creates an exam.</p>

                <div class="border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <label class="flex items-center mb-3">
                        <input type="checkbox" name="classes[]" value="<?php echo e($class->id); ?>"
                               <?php echo e(in_array($class->id, old('classes', $teacher->teachingClasses->pluck('id')->toArray())) ? 'checked' : ''); ?>

                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <span class="ml-3 text-sm text-gray-700">
                            <span class="font-medium"><?php echo e($class->display_name); ?></span>
                            <?php if($class->description): ?>
                                <span class="text-xs text-gray-600 block"><?php echo e($class->description); ?></span>
                            <?php endif; ?>
                        </span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-gray-500">No classes found.</p>
                    <?php endif; ?>
                </div>
                <?php $__errorArgs = ['classes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    This is separate from form teacher assignment. A teacher can be a form teacher for one class and teach different classes.
                </p>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold">
                    Save Classes
                </button>
                <a href="<?php echo e(route('admin.teachers')); ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/teachers/assign-classes.blade.php ENDPATH**/ ?>