

<?php $__env->startSection('title', 'Report Cards'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Report Cards</h1>
                    <p class="text-gray-600 mt-1">
                        <?php if($formTeacherAssignments->count() == 1): ?>
                            Manage report cards for <?php echo e($formTeacherAssignments->first()->schoolClass->name); ?>

                        <?php else: ?>
                            Manage report cards for your assigned classes
                        <?php endif; ?>
                    </p>
                </div>
                <a href="<?php echo e(route('teacher.report-card.create')); ?>"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 ease-in-out">
                    <i class="fas fa-plus mr-2"></i>Create Report Card
                </a>
            </div>
        </div>

        <!-- Report Cards List -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <?php if($reportCards->count() > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $reportCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reportCard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                                <span class="text-white font-medium text-sm">
                                                    <?php echo e(substr($reportCard->student->name, 0, 1)); ?>

                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo e($reportCard->student->name); ?>

                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo e($reportCard->student->email); ?>

                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e($reportCard->class->name); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e($reportCard->session->name); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e($reportCard->term->name); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($reportCard->created_at->format('M d, Y')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="<?php echo e(route('teacher.report-card.edit', $reportCard->student->id)); ?>"
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="<?php echo e(route('teacher.report-card.pdf', $reportCard->id)); ?>"
                                           class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-download"></i> PDF
                                        </a>
                                        <form method="POST" action="<?php echo e(route('teacher.report-card.delete', $reportCard->id)); ?>"
                                              onsubmit="return confirm('Are you sure you want to delete this report card?')"
                                              class="inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-file-alt text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Report Cards Yet</h3>
                    <p class="text-gray-500 mb-6">Get started by creating your first report card for a student.</p>
                    <a href="<?php echo e(route('teacher.report-card.create')); ?>"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-150 ease-in-out">
                        Create First Report Card
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\teacher\report-cards.blade.php ENDPATH**/ ?>