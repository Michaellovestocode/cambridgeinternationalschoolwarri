

<?php $__env->startSection('title', $student->name . ' - Results'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-rose-600 to-rose-800 text-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold"><?php echo e($student->name); ?></h1>
                <p class="text-rose-100 mt-1">
                    <?php echo e($student->registration_number); ?> • 
                    <?php echo e($student->class->display_name ?? 'N/A'); ?> • 
                    <?php echo e($student->email); ?>

                </p>
            </div>
            <a href="<?php echo e(route('admin.results.index')); ?>" class="bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg font-semibold">
                ← Back to Results Portal
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
            <div class="text-sm text-gray-600">Total Attempts</div>
            <div class="text-3xl font-bold text-blue-600"><?php echo e($statistics['total_attempts']); ?></div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
            <div class="text-sm text-gray-600">Graded</div>
            <div class="text-3xl font-bold text-green-600"><?php echo e($statistics['graded']); ?></div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border-l-4 border-purple-500">
            <div class="text-sm text-gray-600">Average Score</div>
            <div class="text-3xl font-bold text-purple-600"><?php echo e($statistics['average']); ?></div>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
            <div class="text-sm text-gray-600">Best Score</div>
            <div class="text-3xl font-bold text-yellow-600"><?php echo e($statistics['highest']); ?></div>
        </div>
    </div>

    <!-- Attempts Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">📋 Exam Attempts</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Exam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Percentage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Result</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php $__empty_1 = true; $__currentLoopData = $attempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?php echo e($index + 1); ?></td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo e($attempt->exam->title); ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php echo e($attempt->exam->subject); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php if($attempt->submitted_at): ?>
                                <div class="text-gray-900"><?php echo e($attempt->submitted_at->format('M d, Y')); ?></div>
                                <div class="text-xs text-gray-600"><?php echo e($attempt->submitted_at->format('H:i')); ?></div>
                            <?php else: ?>
                                <span class="text-gray-500">—</span>
                            <?php endif; ?>
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
                            <?php if($attempt->total_score !== null): ?>
                                <div class="font-semibold text-indigo-600">
                                    <?php echo e($attempt->exam->total_marks > 0 ? round(($attempt->total_score / $attempt->exam->total_marks) * 100, 1) : 0); ?>%
                                </div>
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
                            <div class="flex gap-2">
                                <a href="<?php echo e(route('student.view-result', $attempt->id)); ?>" class="text-blue-600 hover:text-blue-900 font-medium">
                                    View
                                </a>
                                <?php if($attempt->status === 'submitted'): ?>
                                    <a href="<?php echo e(route('admin.attempt.grade', $attempt->id)); ?>" class="text-orange-600 hover:text-orange-900 font-medium">
                                        Grade
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            No attempts found for this student.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\results\student-results.blade.php ENDPATH**/ ?>