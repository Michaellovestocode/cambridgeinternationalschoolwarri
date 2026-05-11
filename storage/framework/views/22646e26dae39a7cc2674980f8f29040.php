<?php $__env->startSection('title', 'Manual Report Card Builder'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manual Report Card Builder</h1>
            <p class="text-gray-600 mt-1">
                Enter paper-exam scores directly. The normal report card PDF layout remains unchanged.
            </p>
        </div>
        <a href="<?php echo e(route('admin.report-cards')); ?>"
           class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-medium">
            Back to Report Cards
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <?php echo e(session('error')); ?>

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

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Choose Report Card</h2>
        <form method="GET" action="<?php echo e(route('admin.report-cards.manual')); ?>" class="grid lg:grid-cols-4 gap-4">
            <div>
                <label for="session_id" class="block text-sm font-medium text-gray-700 mb-2">Session</label>
                <select id="session_id" name="session_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($session->id); ?>" <?php echo e((string) request('session_id', $selectedSession?->id) === (string) $session->id ? 'selected' : ''); ?>>
                            <?php echo e($session->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label for="term_id" class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                <select id="term_id" name="term_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                    <?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($term->id); ?>" <?php echo e((string) request('term_id', $selectedTerm?->id) === (string) $term->id ? 'selected' : ''); ?>>
                            <?php echo e($term->name); ?><?php echo e($term->session ? ' - ' . $term->session->name : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <select id="class_id" name="class_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                    <option value="">Select class</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>" <?php echo e((string) request('class_id') === (string) $class->id ? 'selected' : ''); ?>>
                            <?php echo e($class->display_name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                <select id="student_id" name="student_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                    <option value="">Select student</option>
                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($student->id); ?>" <?php echo e((string) request('student_id') === (string) $student->id ? 'selected' : ''); ?>>
                            <?php echo e($student->name); ?><?php echo e($student->registration_number ? ' - ' . $student->registration_number : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="lg:col-span-4 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-medium">
                    Load Subjects
                </button>
            </div>
        </form>
    </div>

    <?php if($selectedClass && $selectedStudent && $selectedSession && $selectedTerm): ?>
        <form method="POST" action="<?php echo e(route('admin.report-cards.manual.store')); ?>" class="bg-white rounded-xl shadow p-6 space-y-6">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="session_id" value="<?php echo e($selectedSession->id); ?>">
            <input type="hidden" name="term_id" value="<?php echo e($selectedTerm->id); ?>">
            <input type="hidden" name="class_id" value="<?php echo e($selectedClass->id); ?>">
            <input type="hidden" name="student_id" value="<?php echo e($selectedStudent->id); ?>">

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900"><?php echo e($selectedStudent->name); ?></h2>
                    <p class="text-sm text-gray-600">
                        <?php echo e($selectedClass->display_name); ?> | <?php echo e($selectedSession->name); ?> | <?php echo e($selectedTerm->name); ?>

                    </p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    <div class="bg-blue-50 text-blue-800 rounded-lg px-4 py-3">
                        <span class="font-semibold">CA1</span> 0-10
                    </div>
                    <div class="bg-blue-50 text-blue-800 rounded-lg px-4 py-3">
                        <span class="font-semibold">CA2</span> 0-10
                    </div>
                    <div class="bg-blue-50 text-blue-800 rounded-lg px-4 py-3">
                        <span class="font-semibold">CA3</span> 0-10
                    </div>
                    <div class="bg-blue-50 text-blue-800 rounded-lg px-4 py-3">
                        <span class="font-semibold">Exam</span> 0-70
                    </div>
                </div>
            </div>

            <?php if($subjects->isEmpty()): ?>
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-6 rounded-lg text-center">
                    No active subjects found. Add subjects before building a manual report card.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="px-4 py-3 text-left">Subject</th>
                                <th class="px-4 py-3 text-center">CA1</th>
                                <th class="px-4 py-3 text-center">CA2</th>
                                <th class="px-4 py-3 text-center">CA3</th>
                                <th class="px-4 py-3 text-center">Exam</th>
                                <th class="px-4 py-3 text-center">Total</th>
                                <th class="px-4 py-3 text-center">Current Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $score = $scores[$subject->id] ?? null;
                                    $ca1 = old("scores.$index.ca1", $score?->ca1);
                                    $ca2 = old("scores.$index.ca2", $score?->ca2);
                                    $ca3 = old("scores.$index.ca3", $score?->ca3);
                                    $exam = old("scores.$index.exam", $score?->exam);
                                    $total = (float) ($ca1 ?? 0) + (float) ($ca2 ?? 0) + (float) ($ca3 ?? 0) + (float) ($exam ?? 0);
                                ?>
                                <tr class="border-t border-gray-200 score-row">
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        <?php echo e($subject->name); ?>

                                        <input type="hidden" name="scores[<?php echo e($index); ?>][subject_id]" value="<?php echo e($subject->id); ?>">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="scores[<?php echo e($index); ?>][ca1]" value="<?php echo e($ca1); ?>"
                                               min="0" max="10" step="0.5"
                                               class="score-input w-24 border border-gray-300 rounded-lg px-3 py-2 text-center">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="scores[<?php echo e($index); ?>][ca2]" value="<?php echo e($ca2); ?>"
                                               min="0" max="10" step="0.5"
                                               class="score-input w-24 border border-gray-300 rounded-lg px-3 py-2 text-center">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="scores[<?php echo e($index); ?>][ca3]" value="<?php echo e($ca3); ?>"
                                               min="0" max="10" step="0.5"
                                               class="score-input w-24 border border-gray-300 rounded-lg px-3 py-2 text-center">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" name="scores[<?php echo e($index); ?>][exam]" value="<?php echo e($exam); ?>"
                                               min="0" max="70" step="0.5"
                                               class="score-input w-24 border border-gray-300 rounded-lg px-3 py-2 text-center">
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-gray-900">
                                        <span class="row-total"><?php echo e(number_format($total, 1)); ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-700">
                                        <?php echo e($score?->grade ?? '-'); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        Saving here creates normal score records, then opens the regular report-card preview for attendance and remarks.
                    </p>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium">
                        Save Scores & Generate Report Card
                    </button>
                </div>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.score-row').forEach((row) => {
    const inputs = row.querySelectorAll('.score-input');
    const total = row.querySelector('.row-total');

    inputs.forEach((input) => {
        input.addEventListener('input', () => {
            const sum = Array.from(inputs).reduce((value, field) => value + (parseFloat(field.value) || 0), 0);
            total.textContent = sum.toFixed(1);
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\report-cards\manual.blade.php ENDPATH**/ ?>