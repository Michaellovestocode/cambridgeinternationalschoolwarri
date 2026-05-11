<?php $__env->startSection('title', 'Fee Clearances'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">School Fee Clearances</h1>
                <p class="text-sm text-gray-500 mt-1">Approve report-card access per student, session, and term.</p>
            </div>
            <a href="<?php echo e(route('admin.report-cards')); ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold">
                Report Cards
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <form method="GET" action="<?php echo e(route('admin.fee-clearances.index')); ?>" class="grid lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Session</label>
                <select name="session_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($session->id); ?>" <?php echo e($selectedSessionId === $session->id ? 'selected' : ''); ?>>
                            <?php echo e($session->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Term</label>
                <select name="term_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($term->id); ?>" <?php echo e($selectedTermId === $term->id ? 'selected' : ''); ?>>
                            <?php echo e($term->session?->name); ?> - <?php echo e($term->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Class</label>
                <select name="class_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">All classes</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>" <?php echo e(request('class_id') == $class->id ? 'selected' : ''); ?>>
                            <?php echo e($class->display_name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Name or reg no">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-semibold">
                    Filter
                </button>
                <a href="<?php echo e(route('admin.fee-clearances.index')); ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-5 py-3 rounded-lg font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-600">
                        <th class="px-4 py-3 text-left">Student</th>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Payment Details</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $clearance = $clearances[$student->id] ?? null;
                            $isApproved = (bool) ($clearance?->is_approved);
                        ?>
                        <tr class="border-t border-gray-100 align-top">
                            <td class="px-4 py-4">
                                <p class="font-semibold text-gray-900"><?php echo e($student->name); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($student->registration_number); ?></p>
                            </td>
                            <td class="px-4 py-4"><?php echo e($student->class?->display_name ?? 'No class'); ?></td>
                            <td class="px-4 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo e($isApproved ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo e($isApproved ? 'Approved' : 'Locked'); ?>

                                </span>
                                <?php if($clearance?->approved_at): ?>
                                    <p class="text-xs text-gray-500 mt-2">Approved <?php echo e($clearance->approved_at->format('M j, Y')); ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4">
                                <form id="fee-clearance-<?php echo e($student->id); ?>" method="POST" action="<?php echo e(route('admin.fee-clearances.update', $student)); ?>" class="space-y-2">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <input type="hidden" name="session_id" value="<?php echo e($selectedSessionId); ?>">
                                    <input type="hidden" name="term_id" value="<?php echo e($selectedTermId); ?>">
                                    <input type="hidden" name="is_approved" value="<?php echo e($isApproved ? 0 : 1); ?>">
                                    <div class="grid md:grid-cols-2 gap-2">
                                        <input type="number" step="0.01" min="0" name="amount_paid" value="<?php echo e(old('amount_paid', $clearance?->amount_paid)); ?>" class="border border-gray-300 rounded-lg px-3 py-2" placeholder="Amount paid">
                                        <input type="text" name="payment_reference" value="<?php echo e(old('payment_reference', $clearance?->payment_reference)); ?>" class="border border-gray-300 rounded-lg px-3 py-2" placeholder="Payment reference">
                                    </div>
                                    <textarea name="note" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Optional note"><?php echo e(old('note', $clearance?->note)); ?></textarea>
                                </form>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <button type="submit" form="fee-clearance-<?php echo e($student->id); ?>"
                                        class="<?php echo e($isApproved ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'); ?> text-white px-4 py-2 rounded-lg font-semibold">
                                    <?php echo e($isApproved ? 'Revoke' : 'Approve'); ?>

                                </button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="p-6">
            <?php echo e($students->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/fee-clearances/index.blade.php ENDPATH**/ ?>