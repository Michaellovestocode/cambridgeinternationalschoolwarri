

<?php $__env->startSection('title', 'Results Portal'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">Results Portal</h1>
                <p class="text-blue-100 mt-1">Comprehensive examination results management and analysis</p>
            </div>
            <div class="flex gap-2">
                <a href="<?php echo e(route('admin.results.statistics')); ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold">
                    📊 Statistics
                </a>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="bg-blue-400 hover:bg-blue-500 text-white px-4 py-2 rounded-lg">
                    ← Back
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-sm text-gray-600">Total Attempts</div>
            <div class="text-3xl font-bold text-blue-600"><?php echo e($statistics['total_attempts']); ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="text-sm text-gray-600">Graded</div>
            <div class="text-3xl font-bold text-green-600"><?php echo e($statistics['graded']); ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="text-sm text-gray-600">Average Score</div>
            <div class="text-3xl font-bold text-yellow-600"><?php echo e($statistics['average']); ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="text-sm text-gray-600">Pass Rate</div>
            <div class="text-3xl font-bold text-purple-600"><?php echo e($statistics['pass_rate']); ?>%</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">🔍 Filter Results</h2>
        
        <form method="GET" action="<?php echo e(route('admin.results.index')); ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Exam Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Exam</label>
                <select name="exam_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500">
                    <option value="">All Exams</option>
                    <?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($exam->id); ?>" <?php echo e(request('exam_id') == $exam->id ? 'selected' : ''); ?>>
                            <?php echo e($exam->title); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Class Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <select name="class_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500">
                    <option value="">All Classes</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>" <?php echo e(request('class_id') == $class->id ? 'selected' : ''); ?>>
                            <?php echo e($class->display_name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Student Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                <select name="student_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500">
                    <option value="">All Students</option>
                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($student->id); ?>" <?php echo e(request('student_id') == $student->id ? 'selected' : ''); ?>>
                            <?php echo e($student->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="in_progress" <?php echo e(request('status') == 'in_progress' ? 'selected' : ''); ?>>In Progress</option>
                    <option value="submitted" <?php echo e(request('status') == 'submitted' ? 'selected' : ''); ?>>Submitted</option>
                    <option value="graded" <?php echo e(request('status') == 'graded' ? 'selected' : ''); ?>>Graded</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" name="date_from" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500" value="<?php echo e(request('date_from')); ?>">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" name="date_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-500" value="<?php echo e(request('date_to')); ?>">
            </div>

            <!-- Buttons -->
            <div class="flex gap-2 md:col-span-2 lg:col-span-4">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                    🔍 Apply Filters
                </button>
                <a href="<?php echo e(route('admin.results.index')); ?>" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold text-center">
                    ↻ Reset
                </a>
                <a href="<?php echo e(route('admin.results.export-pdf', request()->query())); ?>" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">
                    📄 PDF
                </a>
                <a href="<?php echo e(route('admin.results.export-csv', request()->query())); ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                    📊 CSV
                </a>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">📋 Exam Attempts (<?php echo e($attempts->total()); ?> total)</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Exam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Result</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php $__empty_1 = true; $__currentLoopData = $attempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo e($attempts->firstItem() + $loop->index); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900"><?php echo e($attempt->user->name); ?></div>
                            <div class="text-xs text-gray-600"><?php echo e($attempt->user->registration_number); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo e($attempt->exam->title); ?></div>
                            <div class="text-xs text-gray-600"><?php echo e($attempt->exam->subject); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo e($attempt->user->class->display_name ?? 'N/A'); ?>

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
                                <div class="text-xs text-gray-600">
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?php echo e($attempt->submitted_at?->format('M d, Y') ?? 'N/A'); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex gap-2">
                                <a href="<?php echo e(route('admin.results.student', $attempt->user->id)); ?>" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                    View
                                </a>
                                <?php if(in_array($attempt->status, ['submitted', 'graded'])): ?>
                                    <a href="<?php echo e(route('admin.attempt.grade', $attempt->id)); ?>" class="text-orange-600 hover:text-orange-900 text-sm font-medium">
                                        <?php echo e($attempt->status === 'graded' ? 'View/Edit' : 'Grade'); ?>

                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            No results found. Try adjusting your filters.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($attempts->hasPages()): ?>
        <div class="px-6 py-4 border-t">
            <?php echo e($attempts->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\results\index.blade.php ENDPATH**/ ?>