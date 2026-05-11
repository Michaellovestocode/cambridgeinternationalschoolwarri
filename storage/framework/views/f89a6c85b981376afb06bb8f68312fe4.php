

<?php $__env->startSection('title', 'Form Teacher Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Form Teacher Details</h2>
            <div class="flex gap-2">
                <a href="<?php echo e(route('admin.student.create', ['class_id' => $formTeacher->schoolClass->id])); ?>"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    + Add Student
                </a>
                <a href="<?php echo e(route('admin.form-teachers.edit', $formTeacher->id)); ?>" 
                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                    ✏️ Edit
                </a>
                <a href="<?php echo e(route('admin.form-teachers.index')); ?>" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                    ← Back
                </a>
            </div>
        </div>

        <!-- Header Card -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6 mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-green-100 text-sm">Class</p>
                    <p class="text-2xl font-bold"><?php echo e($formTeacher->schoolClass->display_name); ?></p>
                    <p class="text-green-100 text-sm"><?php echo e($formTeacher->schoolClass->description); ?></p>
                </div>
                <div>
                    <p class="text-green-100 text-sm">Form Teacher</p>
                    <p class="text-2xl font-bold"><?php echo e($formTeacher->teacher->name); ?></p>
                    <p class="text-green-100 text-sm"><?php echo e($formTeacher->teacher->email); ?></p>
                </div>
            </div>
        </div>

        <!-- Key Information -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Assigned Date</p>
                <p class="text-xl font-bold text-blue-700"><?php echo e($formTeacher->assigned_date->format('d M Y')); ?></p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Total Students</p>
                <p class="text-xl font-bold text-purple-700"><?php echo e($students->count()); ?></p>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Class Exams</p>
                <p class="text-xl font-bold text-orange-700"><?php echo e($exams->count()); ?></p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Status</p>
                <p class="text-xl font-bold <?php echo e($formTeacher->is_active ? 'text-green-700' : 'text-gray-700'); ?>">
                    <?php echo e($formTeacher->is_active ? 'Active' : 'Inactive'); ?>

                </p>
            </div>
        </div>

        <!-- Exam Results Summary -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Class Exam Results Summary</h3>
            
            <?php if($exams->isEmpty()): ?>
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                No exams assigned to this class yet.
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                            <th class="px-4 py-3 text-left">Exam Title</th>
                            <th class="px-4 py-3 text-left">Subject</th>
                            <th class="px-4 py-3 text-center">Total Marks</th>
                            <th class="px-4 py-3 text-center">Pass Mark</th>
                            <th class="px-4 py-3 text-center">Attempts</th>
                            <th class="px-4 py-3 text-center">Avg Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $examAttempts = $attemptsByExam[$exam->id] ?? [];
                            $avgScore = count($examAttempts) > 0 
                                ? number_format(collect($examAttempts)->avg('score'), 2)
                                : 'N/A';
                        ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($exam->title); ?></td>
                            <td class="px-4 py-3 text-gray-700"><?php echo e($exam->subject); ?></td>
                            <td class="px-4 py-3 text-center font-semibold"><?php echo e($exam->total_marks); ?></td>
                            <td class="px-4 py-3 text-center font-semibold"><?php echo e($exam->pass_mark); ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                    <?php echo e(count($examAttempts)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-green-700"><?php echo e($avgScore); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Students List -->
        <div>
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-4">
                <h3 class="text-xl font-bold text-gray-800">Class Students</h3>
                <form method="GET" action="<?php echo e(route('admin.form-teachers.show', $formTeacher->id)); ?>" class="w-full md:w-auto flex gap-2">
                    <input type="text" name="student_search" value="<?php echo e(request('student_search')); ?>"
                           placeholder="Search student name, email, or reg no"
                           class="w-full md:w-80 border border-gray-300 rounded-lg px-4 py-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                        Search
                    </button>
                    <?php if(request()->filled('student_search')): ?>
                    <a href="<?php echo e(route('admin.form-teachers.show', $formTeacher->id)); ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-semibold">
                        Reset
                    </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if($students->isEmpty()): ?>
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                No students matched this search.
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200">
                    <p class="font-bold text-gray-800"><?php echo e($student->name); ?></p>
                    <p class="text-sm text-gray-600"><?php echo e($student->email); ?></p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Reg No:</strong> <?php echo e($student->registration_number ?? 'N/A'); ?>

                    </p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\form-teachers\show.blade.php ENDPATH**/ ?>