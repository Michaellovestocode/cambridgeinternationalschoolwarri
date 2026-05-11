<?php $__env->startSection('title', 'Report Card'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-3xl shadow-lg p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500"><?php echo e($reportCard->session); ?> • <?php echo e($reportCard->term); ?></p>
            <h1 class="text-3xl font-black text-gray-900"><?php echo e($reportCard->student->name); ?></h1>
            <p class="text-sm text-gray-600"><?php echo e($reportCard->class->display_name ?? 'Class not assigned'); ?></p>
        </div>
        <a href="<?php echo e(route('parent.dashboard')); ?>" class="text-sm text-blue-600 hover:underline">← Back to portal</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Report Summary</h3>
            <p class="text-sm text-gray-700"><?php echo e($reportCard->comments); ?></p>
        </div>
        <div class="bg-gray-50 p-4 rounded-2xl">
            <p class="text-xs text-gray-500">Total Average</p>
            <p class="text-3xl font-bold text-gray-900"><?php echo e($reportCard->total_average ?? 'N/A'); ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Detailed Scores</h3>
        <p class="text-sm text-gray-500">Refer to the admin report card PDF for the full breakdown.</p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\parent\report-card.blade.php ENDPATH**/ ?>