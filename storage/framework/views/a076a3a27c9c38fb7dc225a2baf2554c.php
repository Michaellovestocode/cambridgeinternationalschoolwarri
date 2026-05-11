<?php $__env->startSection('title', 'Admissions Inbox'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Admissions Inbox</h1>
            <p class="text-sm text-gray-500">Review website enquiries and full admission applications in one place.</p>
        </div>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-sm text-blue-600 hover:underline">Back to dashboard</a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <?php
            $summaryCards = [
                ['label' => 'New', 'value' => $summary[\App\Models\AdmissionEnquiry::STATUS_NEW] ?? 0, 'class' => 'from-sky-500 to-blue-600'],
                ['label' => 'Under Review', 'value' => $summary[\App\Models\AdmissionEnquiry::STATUS_UNDER_REVIEW] ?? 0, 'class' => 'from-amber-400 to-orange-500'],
                ['label' => 'Approved', 'value' => $summary[\App\Models\AdmissionEnquiry::STATUS_APPROVED] ?? 0, 'class' => 'from-emerald-500 to-green-600'],
                ['label' => 'Rejected', 'value' => $summary[\App\Models\AdmissionEnquiry::STATUS_REJECTED] ?? 0, 'class' => 'from-rose-500 to-red-600'],
                ['label' => 'Contacted', 'value' => $summary[\App\Models\AdmissionEnquiry::STATUS_CONTACTED] ?? 0, 'class' => 'from-violet-500 to-indigo-600'],
            ];
        ?>

        <?php $__currentLoopData = $summaryCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-2xl bg-gradient-to-br <?php echo e($card['class']); ?> p-5 text-white shadow-lg">
                <p class="text-xs font-semibold uppercase tracking-wide text-white/80"><?php echo e($card['label']); ?></p>
                <p class="mt-3 text-3xl font-black"><?php echo e($card['value']); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="bg-white shadow rounded-2xl p-6">
        <form method="GET" action="<?php echo e(route('admin.enquiries.index')); ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-xs font-semibold text-gray-500 mb-1">Search</label>
                <input id="search" name="search" type="text" value="<?php echo e($filters['search']); ?>"
                    placeholder="Parent, email, phone, student" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="status" class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                <select name="status" id="status"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">All statuses</option>
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status); ?>" <?php echo e($filters['status'] === $status ? 'selected' : ''); ?>>
                        <?php echo e(ucfirst(str_replace('_', ' ', $status))); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded-xl font-semibold hover:bg-blue-700 transition">Filter</button>
                <a href="<?php echo e(route('admin.enquiries.index')); ?>"
                    class="w-full border border-gray-200 text-gray-700 py-2 rounded-xl text-center">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-2xl overflow-hidden">
        <div class="w-full overflow-x-auto">
            <?php
                $statusClasses = [
                    \App\Models\AdmissionEnquiry::STATUS_NEW => 'bg-sky-100 text-sky-700',
                    \App\Models\AdmissionEnquiry::STATUS_UNDER_REVIEW => 'bg-amber-100 text-amber-700',
                    \App\Models\AdmissionEnquiry::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700',
                    \App\Models\AdmissionEnquiry::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
                    \App\Models\AdmissionEnquiry::STATUS_CONTACTED => 'bg-violet-100 text-violet-700',
                ];
            ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Parent / Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Details</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Received</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $enquiries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enquiry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold"><?php echo e($enquiry->parent_name); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($enquiry->email ?: $enquiry->phone); ?></p>
                            <?php if($enquiry->academic_year): ?>
                                <p class="text-[11px] text-gray-400"><?php echo e($enquiry->academic_year); ?></p>
                            <?php endif; ?>
                            <span class="mt-2 inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-gray-700">
                                <?php echo e($enquiry->inquiry_type); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-semibold"><?php echo e($enquiry->student_name); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($enquiry->class_level); ?></p>
                            <?php if($enquiry->current_school_name): ?>
                                <p class="text-[11px] text-gray-400"><?php echo e($enquiry->current_school_name); ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <div class="max-w-sm break-words space-y-1">
                                <?php if($enquiry->home_address): ?>
                                    <p class="text-xs text-gray-500"><?php echo e($enquiry->home_address); ?></p>
                                <?php endif; ?>
                                <p><?php echo e($enquiry->message ?: 'No message provided.'); ?></p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold <?php echo e($statusClasses[$enquiry->status] ?? 'bg-gray-100 text-gray-700'); ?>">
                                <?php echo e(ucfirst(str_replace('_', ' ', $enquiry->status))); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            <?php echo e($enquiry->created_at->diffForHumans()); ?>

                        </td>
                        <td class="px-4 py-3">
                            <a href="<?php echo e(route('admin.enquiries.show', $enquiry)); ?>"
                                class="text-blue-600 font-semibold hover:underline text-sm">Open record</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                            No admission records captured yet.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-6 py-3 bg-gray-50">
            <?php echo e($enquiries->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/enquiries/index.blade.php ENDPATH**/ ?>