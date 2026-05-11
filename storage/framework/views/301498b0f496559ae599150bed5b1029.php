

<?php $__env->startSection('title', 'Manage Classes'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600">Manage Classes</h2>
            <p class="text-gray-600 mt-1 text-sm">Create and organize school classes</p>
        </div>
    </div>

    <!-- Add New Class Form -->
    <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg border border-gray-100 p-8">
        <h3 class="text-2xl font-bold text-gray-900 mb-6">➕ Add New Class</h3>
        
        <form action="<?php echo e(route('admin.class.store')); ?>" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Class Name *</label>
                <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-purple-500 transition"
                       placeholder="e.g., Year 7 Stars">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Description</label>
                <input type="text" name="description" value="<?php echo e(old('description')); ?>"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-purple-500 transition"
                       placeholder="e.g., Junior Secondary">
                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:shadow-lg text-white px-6 py-3 rounded-xl font-bold transform hover:scale-105 transition">
                    Add Class
                </button>
            </div>
        </form>
    </div>

    <!-- Classes List -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-4">
            <h3 class="text-white font-bold text-lg">📚 All Classes (<?php echo e($classes->count()); ?>)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 border-b-2 border-gray-100">
                    <tr>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">#</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Class Name</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Description</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Students</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Exams</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 transition">
                        <td class="px-8 py-4 whitespace-nowrap text-sm font-semibold text-gray-600"><?php echo e($index + 1); ?></td>
                        <td class="px-8 py-4 whitespace-nowrap">
                            <div class="font-bold text-gray-900 text-base"><?php echo e($class->display_name); ?></div>
                        </td>
                        <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo e($class->description ?? '—'); ?></td>
                        <td class="px-8 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-700">
                                <?php echo e($class->students_count); ?>

                            </span>
                        </td>
                        <td class="px-8 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700">
                                <?php echo e($class->exams_count); ?>

                            </span>
                        </td>
                        <td class="px-8 py-4 whitespace-nowrap text-sm">
                            <form action="<?php echo e(route('admin.class.delete', $class->id)); ?>" method="POST" 
                                  onsubmit="return confirm('Delete this class?')" class="inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-lg font-semibold transition">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="px-8 py-16 text-center">
                            <div class="text-gray-400 text-lg">📭 No classes yet</div>
                            <p class="text-gray-500 text-sm mt-1">Add one above to get started</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\classes\index.blade.php ENDPATH**/ ?>