<?php $__env->startSection('title', 'Edit Website Update'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Edit Website Update</h1>
            <p class="text-sm text-gray-500 mt-1">Adjust how this announcement appears on the homepage and in the ticker.</p>
        </div>
        <a href="<?php echo e(route('admin.announcements.index')); ?>" class="text-sm text-blue-600 hover:underline">Back to updates</a>
    </div>

    <div class="bg-white rounded-3xl shadow-lg p-6">
        <?php echo $__env->make('admin.announcements._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/announcements/edit.blade.php ENDPATH**/ ?>