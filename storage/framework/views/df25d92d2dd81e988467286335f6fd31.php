<?php $__env->startSection('title', 'Create Learning Session'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Learning Session</h1>
                <p class="text-gray-600 text-sm">Add the lesson first, then you can attach questions.</p>
            </div>
            <a href="<?php echo e(route('admin.learning-sessions.index')); ?>" class="text-blue-600 hover:underline">Back</a>
        </div>

        <?php echo $__env->make('admin.learning-sessions.partials.form', [
            'action' => route('admin.learning-sessions.store'),
            'method' => 'POST',
            'learningSession' => null,
            'subjects' => $subjects,
            'classes' => $classes,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/learning-sessions/create.blade.php ENDPATH**/ ?>