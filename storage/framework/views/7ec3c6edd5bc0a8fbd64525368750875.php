

<?php $__env->startSection('title', 'Subjects Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">📚 Subjects Management</h1>
                <p class="text-blue-100 mt-1">Manage subjects and assign them to teachers</p>
            </div>
            <a href="<?php echo e(route('admin.subjects.create')); ?>" class="bg-white text-blue-600 hover:bg-blue-50 px-6 py-2 rounded-lg font-semibold">
                + Add New Subject
            </a>
        </div>
    </div>

    <!-- Subjects Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">All Subjects</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Code</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Teachers</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Exams</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php $__empty_1 = true; $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo e($subjects->firstItem() + $loop->index); ?></td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo e($subject->name); ?></div>
                            <?php if($subject->description): ?>
                                <div class="text-xs text-gray-600"><?php echo e(Str::limit($subject->description, 50)); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($subject->code): ?>
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    <?php echo e($subject->code); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-gray-500">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-semibold text-blue-600"><?php echo e($subject->teachers_count); ?></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-semibold text-green-600"><?php echo e($subject->exams_count); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($subject->is_active): ?>
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    Active
                                </span>
                            <?php else: ?>
                                <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    Inactive
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex gap-2">
                                <a href="<?php echo e(route('admin.subjects.edit', $subject->id)); ?>" 
                                   class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition">
                                    ✏️ Edit
                                </a>
                                <a href="<?php echo e(route('admin.subjects.assign-teachers', $subject->id)); ?>" 
                                   class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-purple-100 text-purple-800 hover:bg-purple-200 transition">
                                    👨‍🏫 Teachers
                                </a>
                                <form action="<?php echo e(route('admin.subjects.destroy', $subject->id)); ?>" method="POST" style="display:inline;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" 
                                            class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-red-100 text-red-800 hover:bg-red-200 transition"
                                            onclick="return confirm('Are you sure you want to delete this subject? This action cannot be undone.')">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No subjects found. <a href="<?php echo e(route('admin.subjects.create')); ?>" class="text-blue-600 hover:underline">Create one</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($subjects->hasPages()): ?>
        <div class="px-6 py-4 border-t">
            <?php echo e($subjects->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/subjects/index.blade.php ENDPATH**/ ?>