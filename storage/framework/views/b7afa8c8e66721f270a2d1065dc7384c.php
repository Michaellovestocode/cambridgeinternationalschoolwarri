

<?php $__env->startSection('title', 'Class Results - ' . $class->name); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-800"><?php echo e($class->name); ?> - Results</h2>
                <p class="text-gray-600"><?php echo e($class->description); ?></p>
            </div>
            <div class="flex gap-2">
                <a href="<?php echo e(route('teacher.scores.dashboard')); ?>" 
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                    📥 Export PDF
                </a>
                <a href="<?php echo e(route('teacher.scores.dashboard')); ?>" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold">
                    ← Back
                </a>
            </div>
        </div>

        <!-- Class Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Total Students</p>
                <p class="text-3xl font-bold text-blue-700"><?php echo e($students->count()); ?></p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Total Exams</p>
                <p class="text-3xl font-bold text-purple-700"><?php echo e($exams->count()); ?></p>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Total Attempts</p>
                <p class="text-3xl font-bold text-orange-700">
                    <?php
                        $totalAttempts = 0;
                        foreach($exams as $exam) {
                            $totalAttempts += count($resultMatrix[array_key_first($resultMatrix)][$exam->id] ?? []);
                        }
                    ?>
                    <?php echo e(array_sum(array_map(function($r) use ($exams) { $c = 0; foreach($exams as $e) { if($r[$e->id]) $c++; } return $c; }, $resultMatrix))); ?>

                </p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Form Teacher</p>
                <p class="text-lg font-bold text-green-700"><?php echo e($formTeacher->teacher->name); ?></p>
            </div>
        </div>

        <!-- Results Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                        <th class="px-4 py-3 text-left font-semibold">Student Name</th>
                        <th class="px-4 py-3 text-left font-semibold">Roll No</th>
                        <?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-4 py-3 text-center font-semibold text-sm">
                            <div class="font-bold"><?php echo e(Str::limit($exam->title, 15)); ?></div>
                            <div class="text-xs font-normal text-green-100"><?php echo e($exam->total_marks); ?> marks</div>
                        </th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-4 py-3 text-center font-semibold">Overall %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $studentData = $resultMatrix[$student->id];
                        $totalScore = 0;
                        $totalMarks = 0;
                        foreach($exams as $exam) {
                            if($studentData[$exam->id]) {
                                $totalScore += $studentData[$exam->id]['score'];
                                $totalMarks += $studentData[$exam->id]['total_marks'];
                            }
                        }
                        $overallPercentage = $totalMarks > 0 ? ($totalScore / $totalMarks) * 100 : 0;
                    ?>
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($student->name); ?></td>
                        <td class="px-4 py-3 text-gray-700"><?php echo e($studentData['rollNumber']); ?></td>
                        <?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <td class="px-4 py-3 text-center">
                            <?php if($studentData[$exam->id]): ?>
                            <div class="font-bold text-gray-800">
                                <?php echo e($studentData[$exam->id]['score']); ?>/<?php echo e($studentData[$exam->id]['total_marks']); ?>

                            </div>
                            <div class="text-xs <?php echo e($studentData[$exam->id]['status'] === 'Pass' ? 'text-green-600' : 'text-red-600'); ?>">
                                <?php echo e($studentData[$exam->id]['percentage']); ?>%
                                <span class="font-semibold"><?php echo e($studentData[$exam->id]['status']); ?></span>
                            </div>
                            <?php else: ?>
                            <span class="text-gray-400 text-sm">—</span>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-lg <?php echo e($overallPercentage >= 50 ? 'text-green-700' : 'text-red-700'); ?>">
                                <?php echo e(number_format($overallPercentage, 1)); ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="<?php echo e(3 + $exams->count()); ?>" class="px-4 py-6 text-center text-gray-500">
                            No students in this class.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Legend -->
        <div class="mt-6 bg-gray-50 rounded-lg p-4">
            <p class="font-semibold text-gray-800 mb-2">Legend:</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="font-semibold text-green-600">Pass</span> - Score >= Pass Mark
                </div>
                <div>
                    <span class="font-semibold text-red-600">Fail</span> - Score < Pass Mark
                </div>
                <div>
                    <span class="font-semibold">—</span> - Not attempted
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\teacher\class-results.blade.php ENDPATH**/ ?>