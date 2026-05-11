

<?php $__env->startSection('title', 'Edit Form Teacher'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Form Teacher Assignment</h2>
            <a href="<?php echo e(route('admin.form-teachers.index')); ?>" class="text-gray-600 hover:text-gray-800">
                ← Back
            </a>
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

        <form action="<?php echo e(route('admin.form-teachers.update', $formTeacher->id)); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Class (Read-Only)</label>
                <input type="text" 
                       value="<?php echo e($formTeacher->schoolClass->name); ?> - <?php echo e($formTeacher->schoolClass->description); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100"
                       disabled>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select New Teacher *</label>
                <select name="teacher_id" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        required>
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($teacher->id); ?>" 
                            <?php echo e(old('teacher_id', $formTeacher->teacher_id) == $teacher->id ? 'selected' : ''); ?>>
                        <?php echo e($teacher->name); ?> (<?php echo e($teacher->email); ?>)
                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['teacher_id'];
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
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                           <?php echo e(old('is_active', $formTeacher->is_active) ? 'checked' : ''); ?>

                           class="mr-2 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <span class="font-medium">Form Teacher is Active</span>
                </label>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
                    Update Assignment
                </button>
                <a href="<?php echo e(route('admin.form-teachers.index')); ?>" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/form-teachers/edit.blade.php ENDPATH**/ ?>