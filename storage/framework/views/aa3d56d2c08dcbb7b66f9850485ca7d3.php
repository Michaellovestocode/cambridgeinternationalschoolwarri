<?php $__env->startSection('title', 'Report Cards'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Report Cards</h1>
            <p class="text-gray-600 mt-1">
                Generate and manage report cards for the active session and term.
            </p>
        </div>
        <a href="<?php echo e(route('admin.report-cards.manual')); ?>"
           class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-lg font-medium text-center">
            Manual Report Card Builder
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

    <div class="bg-white rounded-xl shadow p-6">
        <form method="GET" action="<?php echo e(route('admin.report-cards')); ?>" class="grid lg:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Student</label>
                <input type="text" id="search" name="search" value="<?php echo e(request('search')); ?>"
                       placeholder="Name or registration number"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>
            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                <select id="student_id" name="student_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">All students</option>
                    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($student->id); ?>" <?php echo e((string) request('student_id') === (string) $student->id ? 'selected' : ''); ?>>
                            <?php echo e($student->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label for="session_id" class="block text-sm font-medium text-gray-700 mb-2">Session</label>
                <select id="session_id" name="session_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($session->id); ?>" <?php echo e((string) request('session_id', $selectedSession?->id) === (string) $session->id ? 'selected' : ''); ?>>
                            <?php echo e($session->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label for="term_id" class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                <select id="term_id" name="term_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($term->id); ?>" <?php echo e((string) request('term_id', $selectedTerm?->id) === (string) $term->id ? 'selected' : ''); ?>>
                            <?php echo e($term->name); ?><?php echo e($term->session ? ' - ' . $term->session->name : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <select id="class_id" name="class_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">All classes</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>" <?php echo e((string) request('class_id') === (string) $class->id ? 'selected' : ''); ?>>
                            <?php echo e($class->display_name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">All statuses</option>
                    <option value="draft" <?php echo e(request('status') === 'draft' ? 'selected' : ''); ?>>Draft</option>
                    <option value="generated" <?php echo e(request('status') === 'generated' ? 'selected' : ''); ?>>Generated</option>
                    <option value="published" <?php echo e(request('status') === 'published' ? 'selected' : ''); ?>>Published</option>
                </select>
            </div>
            <div class="lg:col-span-4 flex flex-wrap gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-medium">
                    Search
                </button>
                <a href="<?php echo e(route('admin.report-cards')); ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-3 rounded-lg font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Generate For Student</h2>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between border border-gray-200 rounded-lg p-4">
                        <div>
                            <p class="font-semibold text-gray-900"><?php echo e($student->name); ?></p>
                            <p class="text-sm text-gray-600">
                                <?php echo e($student->registration_number); ?><?php echo e($student->class ? ' • ' . $student->class->display_name : ''); ?>

                            </p>
                        </div>
                        <a href="<?php echo e(route('admin.report-cards.generate', [
                            'student' => $student->id,
                            'session_id' => request('session_id', $selectedSession?->id),
                            'term_id' => request('term_id', $selectedTerm?->id),
                        ])); ?>"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            Generate
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-gray-600">No students available for report card generation.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Bulk Generate By Class</h2>
            <form action="<?php echo e(route('admin.report-cards.bulk')); ?>" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="bulk_session_id" class="block text-sm font-medium text-gray-700 mb-2">Session</label>
                        <select id="bulk_session_id" name="session_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                            <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($session->id); ?>" <?php echo e((string) request('session_id', $selectedSession?->id) === (string) $session->id ? 'selected' : ''); ?>>
                                    <?php echo e($session->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label for="bulk_term_id" class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                        <select id="bulk_term_id" name="term_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                            <?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($term->id); ?>" <?php echo e((string) request('term_id', $selectedTerm?->id) === (string) $term->id ? 'selected' : ''); ?>>
                                    <?php echo e($term->name); ?><?php echo e($term->session ? ' - ' . $term->session->name : ''); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                    <select id="class_id" name="class_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                        <option value="">Select class</option>
                        <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($class->id); ?>"><?php echo e($class->display_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-lg font-medium">
                    Generate Report Cards
                </button>
            </form>

            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Current Window</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-blue-700">Selected Session</p>
                        <p class="font-semibold text-blue-900"><?php echo e($selectedSession?->name ?? 'Not set'); ?></p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-sm text-purple-700">Selected Term</p>
                        <p class="font-semibold text-purple-900"><?php echo e($selectedTerm?->name ?? 'Not set'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Generated Report Cards</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="px-4 py-3 text-left">Student</th>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-left">Session</th>
                        <th class="px-4 py-3 text-left">Term</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $reportCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reportCard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-t border-gray-200">
                            <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($reportCard->student->name); ?></td>
                            <td class="px-4 py-3"><?php echo e($reportCard->class->display_name); ?></td>
                            <td class="px-4 py-3"><?php echo e($reportCard->session->name); ?></td>
                            <td class="px-4 py-3"><?php echo e($reportCard->term->name); ?></td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs <?php echo e($reportCard->isPublished() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                    <?php echo e(ucfirst($reportCard->status)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <form method="POST" action="<?php echo e(route('admin.report-cards.publication', $reportCard->id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>
                                        <input type="hidden" name="published" value="<?php echo e($reportCard->isPublished() ? 0 : 1); ?>">
                                        <button type="submit"
                                                class="<?php echo e($reportCard->isPublished() ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200'); ?> px-4 py-2 rounded-lg text-sm font-medium">
                                            <?php echo e($reportCard->isPublished() ? 'Hide' : 'Publish'); ?>

                                        </button>
                                    </form>
                                    <a href="<?php echo e(route('admin.report-cards.preview', $reportCard->id)); ?>"
                                       class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-medium">
                                        Open
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No report cards have been generated yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <?php echo e($reportCards->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/report-cards/index.blade.php ENDPATH**/ ?>