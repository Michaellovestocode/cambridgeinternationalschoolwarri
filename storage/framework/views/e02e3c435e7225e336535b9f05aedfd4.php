<?php $__env->startSection('title', 'Report Card Preview'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Report Card Preview</h1>
            <p class="text-gray-600 mt-1">
                Fill attendance, remarks, signatures, and next term date manually. Attendance percentage is calculated automatically.
            </p>
            <p class="mt-2">
                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo e($reportCard->isPublished() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                    <?php echo e($reportCard->isPublished() ? 'Published, fee clearance required' : 'Hidden from parents and students'); ?>

                </span>
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="<?php echo e(route('admin.report-cards')); ?>"
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-medium">
                Back
            </a>
            <form method="POST" action="<?php echo e(route('admin.report-cards.publication', $reportCard->id)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="published" value="<?php echo e($reportCard->isPublished() ? 0 : 1); ?>">
                <button type="submit"
                        class="<?php echo e($reportCard->isPublished() ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'); ?> text-white px-4 py-2 rounded-lg font-medium">
                    <?php echo e($reportCard->isPublished() ? 'Hide From Parents & Students' : 'Publish Report Card'); ?>

                </button>
            </form>
            <a href="<?php echo e(route('admin.report-cards.download', $reportCard->id)); ?>"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                Download PDF
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside space-y-1">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white rounded-xl shadow p-6 space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Student Summary</h2>
            <div class="space-y-2 text-sm">
                <p><span class="font-semibold text-gray-700">Student:</span> <?php echo e($reportCard->student->name); ?></p>
                <p><span class="font-semibold text-gray-700">Class:</span> <?php echo e($reportCard->class->display_name); ?></p>
                <p><span class="font-semibold text-gray-700">Session:</span> <?php echo e($reportCard->session->name); ?></p>
                <p><span class="font-semibold text-gray-700">Term:</span> <?php echo e($reportCard->term->name); ?></p>
                <p><span class="font-semibold text-gray-700">Overall Grade:</span> <?php echo e($reportCard->overall_grade); ?></p>
                <p><span class="font-semibold text-gray-700">Average Score:</span> <?php echo e(number_format($reportCard->average_score, 1)); ?>%</p>
                <p><span class="font-semibold text-gray-700">Position:</span> <?php echo e($reportCard->position); ?>/<?php echo e($reportCard->total_students); ?></p>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Subject Scores</h3>
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    <?php $__currentLoopData = $scores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border border-gray-200 rounded-lg p-3">
                            <p class="font-medium text-gray-900"><?php echo e($score->subject->name); ?></p>
                            <p class="text-sm text-gray-600">
                                Total: <?php echo e(number_format($score->total, 1)); ?> | Grade: <?php echo e($score->grade); ?> | Remark: <?php echo e($score->remark); ?>

                            </p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl shadow p-6">
            <form action="<?php echo e(route('admin.report-cards.update', $reportCard->id)); ?>" method="POST" class="space-y-8">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Appearance</h2>
                    <div>
                        <label for="theme_color" class="block text-sm font-medium text-gray-700 mb-2">Theme Color</label>
                        <select id="theme_color" name="theme_color" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                            <?php $__currentLoopData = $colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($color); ?>" <?php echo e(old('theme_color', $reportCard->theme_color ?? 'blue') === $color ? 'selected' : ''); ?>>
                                    <?php echo e(ucfirst($color)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Attendance</h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label for="days_school_opened" class="block text-sm font-medium text-gray-700 mb-2">Days School Opened</label>
                            <input id="days_school_opened" name="days_school_opened" type="number" min="0"
                                   value="<?php echo e(old('days_school_opened', $reportCard->days_school_opened)); ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                        <div>
                            <label for="days_present" class="block text-sm font-medium text-gray-700 mb-2">Days Present</label>
                            <input id="days_present" name="days_present" type="number" min="0"
                                   value="<?php echo e(old('days_present', $reportCard->days_present)); ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                        <div>
                            <label for="days_absent" class="block text-sm font-medium text-gray-700 mb-2">Days Absent</label>
                            <input id="days_absent" name="days_absent" type="number" min="0"
                                   value="<?php echo e(old('days_absent', $reportCard->days_absent)); ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        Attendance percentage will be calculated automatically from days present and days school opened.
                    </p>
                </div>

                <div class="grid lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-900">Class Teacher Section</h2>
                        <div>
                            <label for="class_teacher_comment" class="block text-sm font-medium text-gray-700 mb-2">Class Teacher's Remark</label>
                            <textarea id="class_teacher_comment" name="class_teacher_comment" rows="4"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3"><?php echo e(old('class_teacher_comment', $reportCard->class_teacher_comment)); ?></textarea>
                        </div>
                        <div>
                            <label for="class_teacher_name" class="block text-sm font-medium text-gray-700 mb-2">Class Teacher's Name</label>
                            <input id="class_teacher_name" name="class_teacher_name" type="text"
                                   value="<?php echo e(old('class_teacher_name', $reportCard->class_teacher_name)); ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                        <div>
                            <label for="class_teacher_signature" class="block text-sm font-medium text-gray-700 mb-2">Class Teacher's Signature</label>
                            <input id="class_teacher_signature" name="class_teacher_signature" type="text"
                                   value="<?php echo e(old('class_teacher_signature', $reportCard->class_teacher_signature)); ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                        <div>
                            <label for="class_teacher_signature_date" class="block text-sm font-medium text-gray-700 mb-2">Signature Date</label>
                            <input id="class_teacher_signature_date" name="class_teacher_signature_date" type="date"
                                   value="<?php echo e(old('class_teacher_signature_date', optional($reportCard->class_teacher_signature_date)->format('Y-m-d'))); ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-900">Head Teacher Section</h2>
                        <div>
                            <label for="head_teacher_comment" class="block text-sm font-medium text-gray-700 mb-2">Head Teacher's Remark</label>
                            <textarea id="head_teacher_comment" name="head_teacher_comment" rows="4"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3"><?php echo e(old('head_teacher_comment', $reportCard->head_teacher_comment)); ?></textarea>
                        </div>
                        <div>
                            <label for="head_teacher_name" class="block text-sm font-medium text-gray-700 mb-2">Head Teacher's Name</label>
                            <input id="head_teacher_name" name="head_teacher_name" type="text"
                                   value="<?php echo e(old('head_teacher_name', $reportCard->head_teacher_name)); ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                        <div>
                            <label for="head_teacher_signature" class="block text-sm font-medium text-gray-700 mb-2">Head Teacher's Signature</label>
                            <input id="head_teacher_signature" name="head_teacher_signature" type="text"
                                   value="<?php echo e(old('head_teacher_signature', $reportCard->head_teacher_signature)); ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                        <div>
                            <label for="head_teacher_signature_date" class="block text-sm font-medium text-gray-700 mb-2">Signature Date</label>
                            <input id="head_teacher_signature_date" name="head_teacher_signature_date" type="date"
                                   value="<?php echo e(old('head_teacher_signature_date', optional($reportCard->head_teacher_signature_date)->format('Y-m-d'))); ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Academic Calendar</h2>
                    <div>
                        <label for="next_term_begins" class="block text-sm font-medium text-gray-700 mb-2">Next Term Begins</label>
                        <input id="next_term_begins" name="next_term_begins" type="date"
                               value="<?php echo e(old('next_term_begins', optional($reportCard->next_term_begins)->format('Y-m-d'))); ?>"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium">
                        Save Report Card Details
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\report-cards\preview.blade.php ENDPATH**/ ?>