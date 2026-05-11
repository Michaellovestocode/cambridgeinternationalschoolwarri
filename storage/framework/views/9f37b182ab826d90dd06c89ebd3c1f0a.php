

<?php $__env->startSection('title', 'Manage Exams'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Manage Exams</h2>
        <a href="<?php echo e(route('admin.exam.create')); ?>" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
            + Create New Exam
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?php $__empty_1 = true; $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="border-b p-6 hover:bg-gray-50">
            <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-800"><?php echo e($exam->title); ?></h3>
                    <p class="text-gray-600 mt-1"><?php echo e($exam->description); ?></p>
                </div>
                <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1 rounded-full">
                    <?php echo e($exam->subject); ?>

                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
                <div class="text-sm">
                    <span class="text-gray-600">Duration:</span>
                    <p class="font-semibold"><?php echo e($exam->duration_minutes); ?> mins</p>
                </div>
                <div class="text-sm">
                    <span class="text-gray-600">Total Marks:</span>
                    <p class="font-semibold"><?php echo e($exam->total_marks); ?></p>
                </div>
                <div class="text-sm">
                    <span class="text-gray-600">Pass Mark:</span>
                    <p class="font-semibold"><?php echo e($exam->pass_mark); ?></p>
                </div>
                <div class="text-sm">
                    <span class="text-gray-600">Questions:</span>
                    <p class="font-semibold"><?php echo e($exam->questions->count()); ?></p>
                </div>
                <div class="text-sm">
                    <span class="text-gray-600">Status:</span>
                    <p class="font-semibold">
                        <?php if($exam->is_active): ?>
                        <span class="text-green-600">Active</span>
                        <?php else: ?>
                        <span class="text-red-600">Inactive</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="flex gap-3 text-sm">
                <span class="text-gray-600">Classes:</span>
                <?php $__currentLoopData = $exam->classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="bg-gray-100 px-2 py-1 rounded"><?php echo e($class->display_name); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="flex gap-2 mt-4 flex-wrap">
                <a href="<?php echo e(route('admin.exam.edit', $exam->id)); ?>" 
                   class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded">
                    ✏️ Edit Exam
                </a>
                <a href="<?php echo e(route('admin.exam.questions', $exam->id)); ?>" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Manage Questions
                </a>
                <a href="<?php echo e(route('admin.exam.results', $exam->id)); ?>" 
                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                    View Results
                </a>
                <a href="<?php echo e(route('admin.exam.export.pdf', $exam->id)); ?>" 
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                    Export PDF
                </a>
                <a href="<?php echo e(route('admin.exam.export.word', $exam->id)); ?>" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    Export Word
                </a>
                <form method="POST" action="<?php echo e(route('admin.exam.delete', $exam->id)); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this exam? This action cannot be undone.');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded">
                        🗑️ Delete Exam
                    </button>
                </form>
            </div>

            <div class="text-sm text-gray-500 mt-3">
                Created by: <?php echo e($exam->creator->name); ?> | 
                Available: <?php echo e($exam->start_date->format('d M Y')); ?> - <?php echo e($exam->end_date->format('d M Y')); ?>

            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="p-12 text-center">
            <div class="text-gray-400 text-6xl mb-4">📝</div>
            <p class="text-gray-600 text-lg mb-4">No exams created yet</p>
            <a href="<?php echo e(route('admin.exam.create')); ?>" 
               class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                Create Your First Exam
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/exams/index.blade.php ENDPATH**/ ?>