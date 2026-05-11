

<?php $__env->startSection('title', $class->display_name . ' - Results'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-teal-600 to-teal-800 text-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold"><?php echo e($class->display_name); ?></h1>
                <p class="text-teal-100 mt-1">Class Results Summary</p>
            </div>
            <a href="<?php echo e(route('admin.results.index')); ?>" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg font-semibold">
                ← Back to Results Portal
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
            <div class="text-sm text-gray-600">Students</div>
            <div class="text-3xl font-bold text-blue-600"><?php echo e($statistics['total_students']); ?></div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
            <div class="text-sm text-gray-600">Total Attempts</div>
            <div class="text-3xl font-bold text-green-600"><?php echo e($statistics['total_attempts']); ?></div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border-l-4 border-purple-500">
            <div class="text-sm text-gray-600">Graded</div>
            <div class="text-3xl font-bold text-purple-600"><?php echo e($statistics['graded']); ?></div>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
            <div class="text-sm text-gray-600">Average</div>
            <div class="text-3xl font-bold text-yellow-600"><?php echo e($statistics['average']); ?></div>
        </div>
    </div>

    <?php if($statistics['graded'] > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-indigo-50 p-4 rounded-lg border-l-4 border-indigo-500">
            <div class="text-sm text-gray-600">Highest Score</div>
            <div class="text-2xl font-bold text-indigo-600"><?php echo e($statistics['highest']); ?></div>
        </div>
        <div class="bg-teal-50 p-4 rounded-lg border-l-4 border-teal-500">
            <div class="text-sm text-gray-600">Lowest Score</div>
            <div class="text-2xl font-bold text-teal-600"><?php echo e($statistics['lowest']); ?></div>
        </div>
        <div class="bg-pink-50 p-4 rounded-lg border-l-4 border-pink-500">
            <div class="text-sm text-gray-600">Score Range</div>
            <div class="text-2xl font-bold text-pink-600"><?php echo e($statistics['highest'] - $statistics['lowest']); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Results by Attempt -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">📋 All Attempts</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Registration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Exam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Result</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php $__empty_1 = true; $__currentLoopData = $attempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900"><?php echo e($attempt->user->name); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php echo e($attempt->user->registration_number); ?>

                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo e($attempt->exam->title); ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php echo e($attempt->exam->subject); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php switch($attempt->status):
                                case ('in_progress'): ?>
                                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">In Progress</span>
                                    <?php break; ?>
                                <?php case ('submitted'): ?>
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">Submitted</span>
                                    <?php break; ?>
                                <?php case ('graded'): ?>
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Graded</span>
                                    <?php break; ?>
                            <?php endswitch; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($attempt->total_score !== null): ?>
                                <div class="font-bold text-gray-900"><?php echo e($attempt->total_score); ?>/<?php echo e($attempt->exam->total_marks); ?></div>
                            <?php else: ?>
                                <span class="text-gray-500">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($attempt->status === 'graded'): ?>
                                <?php if($attempt->total_score >= $attempt->exam->pass_mark): ?>
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">✓ Pass</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">✗ Fail</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-500">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="<?php echo e(route('admin.results.student', $attempt->user->id)); ?>" class="text-blue-600 hover:text-blue-900 font-medium">
                                View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            No results found for this class.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\results\class-wise.blade.php ENDPATH**/ ?>