

<?php $__env->startSection('title', 'Enter Scores - ' . ($class->name ?? 'Class')); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            📝 Enter Scores - <?php echo e($class->name ?? 'Class'); ?>

        </h1>
        <p class="text-gray-600"><?php echo e($subject->name ?? 'Subject'); ?> | <?php echo e($activeSession->name ?? ''); ?> - <?php echo e($activeTerm->name ?? ''); ?></p>
    </div>

    <!-- Navigation -->
    <div class="mb-6 flex gap-3">
        <a href="<?php echo e(route('teacher.scores.dashboard')); ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-semibold">
            ← Back to Dashboard
        </a>
    </div>

    <!-- Score Entry Form -->
    <div class="bg-white rounded-lg shadow-lg p-8">
        <form id="scoresForm" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            <input type="hidden" name="class_id" value="<?php echo e($class->id); ?>">
            <input type="hidden" name="subject_id" value="<?php echo e($subject->id); ?>">

            <!-- Score Grading System Info -->
            <div class="bg-blue-50 border border-blue-300 rounded-lg p-4 mb-6">
                <h3 class="font-bold text-blue-800 mb-2">📊 Grading System</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-blue-800 text-sm">
                    <div>
                        <span class="font-bold">CA1:</span> 0-10
                    </div>
                    <div>
                        <span class="font-bold">CA2:</span> 0-10
                    </div>
                    <div>
                        <span class="font-bold">CA3:</span> 0-10
                    </div>
                    <div>
                        <span class="font-bold">Exam:</span> 0-70
                    </div>
                </div>
                <p class="text-blue-800 text-sm mt-3">Total = CA (30) + Exam (70) = 100 marks</p>
            </div>

            <!-- Students Score Table -->
            <?php if($students->isEmpty()): ?>
                <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-6 text-center">
                    <p class="text-yellow-800 font-semibold">No students found in this class</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto mb-6">
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="border border-gray-300 px-4 py-3 text-left font-bold">S/N</th>
                                <th class="border border-gray-300 px-4 py-3 text-left font-bold">Student Name</th>
                                <th class="border border-gray-300 px-4 py-3 text-center font-bold">CA1 (10)</th>
                                <th class="border border-gray-300 px-4 py-3 text-center font-bold">CA2 (10)</th>
                                <th class="border border-gray-300 px-4 py-3 text-center font-bold">CA3 (10)</th>
                                <th class="border border-gray-300 px-4 py-3 text-center font-bold">Exam (70)</th>
                                <th class="border border-gray-300 px-4 py-3 text-center font-bold">Total (100)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $existingScore = $scores[$student->id] ?? null;
                                    $ca1 = (float)($existingScore->ca1 ?? 0);
                                    $ca2 = (float)($existingScore->ca2 ?? 0);
                                    $ca3 = (float)($existingScore->ca3 ?? 0);
                                    $exam = (float)($existingScore->exam ?? 0);
                                ?>
                                <tr class="hover:bg-gray-50 border-b border-gray-300">
                                    <td class="border border-gray-300 px-4 py-3 text-center font-bold text-gray-700"><?php echo e($index + 1); ?></td>
                                    <td class="border border-gray-300 px-4 py-3 font-semibold text-gray-800">
                                        <?php echo e($student->name); ?>

                                        <br>
                                        <span class="text-sm text-gray-500"><?php echo e($student->registration_number); ?></span>
                                    </td>
                                    <td class="border border-gray-300 px-4 py-3">
                                        <input type="number" name="scores[<?php echo e($index); ?>][student_id]" value="<?php echo e($student->id); ?>" hidden>
                                        <input type="number" 
                                            name="scores[<?php echo e($index); ?>][ca1]" 
                                            value="<?php echo e($ca1); ?>"
                                            min="0" max="10" step="0.5"
                                            class="w-full border border-gray-400 rounded px-2 py-1 text-center focus:outline-none focus:border-blue-500"
                                            placeholder="0">
                                    </td>
                                    <td class="border border-gray-300 px-4 py-3">
                                        <input type="number" 
                                            name="scores[<?php echo e($index); ?>][ca2]" 
                                            value="<?php echo e($ca2); ?>"
                                            min="0" max="10" step="0.5"
                                            class="w-full border border-gray-400 rounded px-2 py-1 text-center focus:outline-none focus:border-blue-500"
                                            placeholder="0">
                                    </td>
                                    <td class="border border-gray-300 px-4 py-3">
                                        <input type="number" 
                                            name="scores[<?php echo e($index); ?>][ca3]" 
                                            value="<?php echo e($ca3); ?>"
                                            min="0" max="10" step="0.5"
                                            class="w-full border border-gray-400 rounded px-2 py-1 text-center focus:outline-none focus:border-blue-500"
                                            placeholder="0">
                                    </td>
                                    <td class="border border-gray-300 px-4 py-3">
                                        <input type="number" 
                                            name="scores[<?php echo e($index); ?>][exam]" 
                                            value="<?php echo e($exam); ?>"
                                            min="0" max="70" step="0.5"
                                            class="w-full border border-gray-400 rounded px-2 py-1 text-center focus:outline-none focus:border-blue-500"
                                            placeholder="0">
                                    </td>
                                    <td class="border border-gray-300 px-4 py-3 text-center font-bold bg-gray-100">
                                        <span class="total-score"><?php echo e(($ca1 ?? 0) + ($ca2 ?? 0) + ($ca3 ?? 0) + ($exam ?? 0)); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="flex gap-4 justify-between pt-6 border-t">
                <div>
                    <a href="<?php echo e(route('teacher.scores.dashboard')); ?>" class="bg-gray-400 hover:bg-gray-500 text-white px-8 py-3 rounded-lg font-semibold">
                        ← Cancel
                    </a>
                </div>
                <div class="flex gap-4">
                    <button type="button" onclick="submitForm('<?php echo e(route('teacher.scores.save')); ?>')" class="bg-yellow-600 hover:bg-yellow-700 text-white px-8 py-3 rounded-lg font-semibold">
                        💾 Save as Draft
                    </button>
                    <button type="button" onclick="submitForm('<?php echo e(route('teacher.scores.submit')); ?>')" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
                        ✓ Submit for Approval
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tips Section -->
    <div class="mt-8 bg-green-50 rounded-lg p-6 border border-green-300">
        <h3 class="text-lg font-bold text-green-800 mb-3">💡 Useful Tips</h3>
        <ul class="text-green-700 space-y-2">
            <li>✓ Use decimal values (e.g., 7.5) for precision</li>
            <li>✓ Totals are automatically calculated</li>
            <li>✓ Save regularly as draft to avoid losing data</li>
            <li>✓ Only submit when you're completely sure about the scores</li>
        </ul>
    </div>
</div>

<script>
// Calculate totals dynamically
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('change', function() {
        const row = this.closest('tr');
        if (row) {
            const ca1 = parseFloat(row.querySelector('input[name*="[ca1]"]').value) || 0;
            const ca2 = parseFloat(row.querySelector('input[name*="[ca2]"]').value) || 0;
            const ca3 = parseFloat(row.querySelector('input[name*="[ca3]"]').value) || 0;
            const exam = parseFloat(row.querySelector('input[name*="[exam]"]').value) || 0;
            const total = ca1 + ca2 + ca3 + exam;
            row.querySelector('.total-score').textContent = total.toFixed(1);
        }
    });
});

// Submit form with specific action
function submitForm(action) {
    const form = document.getElementById('scoresForm');
    form.action = action;
    form.submit();
}

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/teacher/scores/enter.blade.php ENDPATH**/ ?>