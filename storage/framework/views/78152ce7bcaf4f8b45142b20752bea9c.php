

<?php $__env->startSection('title', $student ? 'Edit Report Card - ' . $student->name : 'Create Report Card'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <?php echo e($student ? 'Edit Report Card' : 'Create Report Card'); ?>

                    </h1>
                    <p class="text-gray-600 mt-1">
                        <?php echo e($student ? 'Update report card for ' . $student->name : 'Create a new report card for a student in your assigned classes'); ?>

                    </p>
                </div>
                <a href="<?php echo e(route('teacher.report-cards')); ?>"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 ease-in-out">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Report Cards
                </a>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="<?php echo e(route('teacher.report-card.store')); ?>" class="bg-white shadow-sm rounded-lg p-6">
            <?php echo csrf_field(); ?>

            <!-- Display Validation Errors -->
            <?php if($errors->any()): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-red-400 mt-0.5 mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-red-800">There were some errors with your submission:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Student Selection (only show if not editing) -->
            <?php if(!$student): ?>
            <div class="mb-6">
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Select Student</label>
                <select name="student_id" id="student_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 <?php echo e($errors->has('student_id') ? 'border-red-500' : ''); ?>">
                    <option value="">Choose a student...</option>
                    <?php $__currentLoopData = $formTeacherAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <optgroup label="<?php echo e($assignment->schoolClass->name); ?>">
                        <?php $__currentLoopData = $assignment->schoolClass->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classStudent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($classStudent->id); ?>" <?php echo e(old('student_id') == $classStudent->id ? 'selected' : ''); ?>><?php echo e($classStudent->name); ?> (<?php echo e($classStudent->email); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </optgroup>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['student_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <?php else: ?>
            <input type="hidden" name="student_id" value="<?php echo e($student->id); ?>">
            <?php endif; ?>

            <!-- Session and Term -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="session_id" class="block text-sm font-medium text-gray-700 mb-2">Academic Session</label>
                    <select name="session_id" id="session_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 <?php echo e($errors->has('session_id') ? 'border-red-500' : ''); ?>">
                        <option value="">Select session...</option>
                        <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($session->id); ?>" <?php echo e(old('session_id', $reportCard ? $reportCard->session_id : '') == $session->id ? 'selected' : ''); ?>>
                            <?php echo e($session->name); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['session_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label for="term_id" class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                    <select name="term_id" id="term_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 <?php echo e($errors->has('term_id') ? 'border-red-500' : ''); ?>">
                        <option value="">Select term...</option>
                        <?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($term->id); ?>" <?php echo e(old('term_id', $reportCard ? $reportCard->term_id : '') == $term->id ? 'selected' : ''); ?>>
                            <?php echo e($term->name); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['term_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <!-- Subjects Section -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Subject Scores</h3>
                <div id="subjects-container">
                    <?php if($reportCard && $reportCard->subjects): ?>
                        <?php $__currentLoopData = $reportCard->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $subjectData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="subject-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                                <select name="subjects[<?php echo e($index); ?>][subject_id]" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select subject...</option>
                                    <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($subject->id); ?>" <?php echo e($subjectData['subject_id'] == $subject->id ? 'selected' : ''); ?>>
                                        <?php echo e($subject->name); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Score (%)</label>
                                <input type="number" name="subjects[<?php echo e($index); ?>][score]" min="0" max="100" step="0.01"
                                       value="<?php echo e($subjectData['score']); ?>" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
                                <input type="text" name="subjects[<?php echo e($index); ?>][grade]" maxlength="2"
                                       value="<?php echo e($subjectData['grade']); ?>" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Remark</label>
                                <input type="text" name="subjects[<?php echo e($index); ?>][remark]" maxlength="255"
                                       value="<?php echo e($subjectData['remark'] ?? ''); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="flex items-end">
                                <button type="button" onclick="removeSubject(this)"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg font-medium transition duration-150 ease-in-out">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
                <button type="button" onclick="addSubject()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 ease-in-out">
                    <i class="fas fa-plus mr-2"></i>Add Subject
                </button>
            </div>

            <!-- Overall Remark -->
            <div class="mb-6">
                <label for="overall_remark" class="block text-sm font-medium text-gray-700 mb-2">Overall Remark</label>
                <textarea name="overall_remark" id="overall_remark" rows="3" maxlength="500"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Enter overall performance remark..."><?php echo e($reportCard ? $reportCard->overall_remark : ''); ?></textarea>
            </div>

            <!-- Teacher Comment -->
            <div class="mb-6">
                <label for="teacher_comment" class="block text-sm font-medium text-gray-700 mb-2">Teacher's Comment</label>
                <textarea name="teacher_comment" id="teacher_comment" rows="3" maxlength="500"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Enter teacher's comment..."><?php echo e($reportCard ? $reportCard->teacher_comment : ''); ?></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-150 ease-in-out">
                    <i class="fas fa-save mr-2"></i><?php echo e($student ? 'Update Report Card' : 'Create Report Card'); ?>

                </button>
            </div>
        </form>
    </div>
</div>

<script>
let subjectIndex = <?php echo e($reportCard && $reportCard->subjects ? count($reportCard->subjects) : 0); ?>;

// Create subjects data for JavaScript
const subjectsData = <?php echo json_encode($subjects, 15, 512) ?>;

// Get old input data
const oldSubjects = <?php echo json_encode(old('subjects', []), 512) ?>;

function addSubject(subjectData = null) {
    const container = document.getElementById('subjects-container');
    const subjectRow = document.createElement('div');
    subjectRow.className = 'subject-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 p-4 bg-gray-50 rounded-lg';

    let subjectOptions = '<option value="">Select subject...</option>';
    subjectsData.forEach(subject => {
        const selected = subjectData && subjectData.subject_id == subject.id ? 'selected' : '';
        subjectOptions += `<option value="${subject.id}" ${selected}>${subject.name}</option>`;
    });

    const scoreValue = subjectData ? subjectData.score : '';
    const gradeValue = subjectData ? subjectData.grade : '';
    const remarkValue = subjectData ? subjectData.remark || '' : '';

    subjectRow.innerHTML = `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
            <select name="subjects[${subjectIndex}][subject_id]" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                ${subjectOptions}
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Score (%)</label>
            <input type="number" name="subjects[${subjectIndex}][score]" min="0" max="100" step="0.01" required
                   value="${scoreValue}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
            <input type="text" name="subjects[${subjectIndex}][grade]" maxlength="2" required
                   value="${gradeValue}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Remark</label>
            <input type="text" name="subjects[${subjectIndex}][remark]" maxlength="255"
                   value="${remarkValue}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex items-end">
            <button type="button" onclick="removeSubject(this)"
                    class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg font-medium transition duration-150 ease-in-out">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(subjectRow);
    subjectIndex++;
}

// Load old input subjects on page load
document.addEventListener('DOMContentLoaded', function() {
    if (oldSubjects && oldSubjects.length > 0) {
        oldSubjects.forEach(subject => {
            addSubject(subject);
        });
    }
});

function removeSubject(button) {
    button.closest('.subject-row').remove();
}
</script>

<!-- Success Message -->
<?php if(session('success')): ?>
<div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg z-50" id="success-message">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <?php echo e(session('success')); ?>

    </div>
</div>
<script>
    setTimeout(() => {
        document.getElementById('success-message').style.display = 'none';
    }, 5000);
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/teacher/report-card-form.blade.php ENDPATH**/ ?>