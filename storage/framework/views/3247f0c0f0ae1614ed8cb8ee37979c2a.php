

<?php $__env->startSection('title', 'Exam Results'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800"><?php echo e($exam->title); ?> - Results</h2>
                <p class="text-gray-600"><?php echo e($exam->subject); ?></p>
            </div>
            <a href="<?php echo e(route('admin.exams')); ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                ← Back to Exams
            </a>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Total Students</div>
                <div class="text-2xl font-bold text-blue-600"><?php echo e($statistics['total_students']); ?></div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Graded</div>
                <div class="text-2xl font-bold text-green-600"><?php echo e($statistics['graded']); ?></div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Pending</div>
                <div class="text-2xl font-bold text-yellow-600"><?php echo e($statistics['pending']); ?></div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Pass Rate</div>
                <div class="text-2xl font-bold text-purple-600"><?php echo e($statistics['pass_rate']); ?>%</div>
            </div>
        </div>

        <?php if($statistics['graded'] > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Average Score</div>
                <div class="text-xl font-bold text-gray-800"><?php echo e($statistics['average']); ?>/<?php echo e($exam->total_marks); ?></div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Highest Score</div>
                <div class="text-xl font-bold text-green-600"><?php echo e($statistics['highest']); ?>/<?php echo e($exam->total_marks); ?></div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Lowest Score</div>
                <div class="text-xl font-bold text-red-600"><?php echo e($statistics['lowest']); ?>/<?php echo e($exam->total_marks); ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Export Buttons -->
        <div class="flex gap-3 mt-6">
            <a href="<?php echo e(route('admin.exam.export.pdf', $exam->id)); ?>" 
               class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded font-semibold">
                📄 Export PDF
            </a>
            <a href="<?php echo e(route('admin.exam.export.word', $exam->id)); ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-semibold">
                📝 Export Word
            </a>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-800">Student Results</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $attempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo e($index + 1); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900"><?php echo e($attempt->user->name); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php echo e($attempt->user->registration_number); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php echo e($attempt->user->class->display_name ?? 'N/A'); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php echo e($attempt->submitted_at ? $attempt->submitted_at->format('d M Y') : 'N/A'); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($attempt->status === 'graded'): ?>
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Graded</span>
                            <?php elseif($attempt->status === 'submitted'): ?>
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">In Progress</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($attempt->total_score !== null): ?>
                                <span class="font-semibold <?php echo e($attempt->total_score >= $exam->pass_mark ? 'text-green-600' : 'text-red-600'); ?>">
                                    <?php echo e($attempt->total_score); ?>/<?php echo e($exam->total_marks); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex gap-3 items-center">
                                <?php if($attempt->status === 'in_progress'): ?>
                                    <!-- Submit For Student Button -->
                                    <form method="POST" 
                                          action="<?php echo e(route('admin.submit-for-student', $attempt->id)); ?>" 
                                          onsubmit="return confirm('⚠️ Submit exam for <?php echo e($attempt->user->name); ?>?\n\nThis will:\n✓ Mark exam as completed\n✓ Auto-grade MCQ/Fill-blank\n✓ Stop the timer\n\nCannot be undone!')"
                                          class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" 
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-semibold">
                                            ✓ Submit
                                        </button>
                                    </form>
                                    <a href="<?php echo e(route('admin.attempt.grade', $attempt->id)); ?>" 
                                       class="text-gray-400 hover:text-gray-600 text-xs" title="View Progress">
                                        👁️ View
                                    </a>
                                <?php elseif($attempt->status === 'submitted'): ?>
                                    <a href="<?php echo e(route('admin.attempt.grade', $attempt->id)); ?>" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-semibold">
                                        📝 Grade Now
                                    </a>
                                <?php elseif($attempt->status === 'graded'): ?>
                                    <div class="flex gap-2">
                                        <a href="<?php echo e(route('admin.attempt.print', $attempt->id)); ?>" 
                                           class="text-purple-600 hover:text-purple-800 text-lg" title="Print Script">
                                            🖨️
                                        </a>
                                        <a href="<?php echo e(route('admin.attempt.grade', $attempt->id)); ?>" 
                                           class="text-green-600 hover:text-green-800 text-lg" title="View/Edit">
                                            👁️
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            No student attempts yet
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\exams\results.blade.php ENDPATH**/ ?>