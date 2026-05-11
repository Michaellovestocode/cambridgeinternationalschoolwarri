

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Add Students to <?php echo e($class->name); ?></h2>
        <p class="text-gray-600">Manage students in your assigned class</p>
    </div>

    <?php if($errors->any()): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <ul class="mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="flex flex-wrap -mx-4">
        <!-- Add New Student - Disabled -->
        <div class="w-full md:w-1/2 px-4 mb-6">
            <div class="bg-white rounded-lg shadow-md border-2 border-yellow-400">
                <div class="bg-yellow-100 text-yellow-800 px-4 py-3 rounded-t-lg">
                    <h5 class="text-lg font-semibold mb-0">⚠️ Student Management Unavailable</h5>
                </div>
                <div class="p-4">
                    <p class="text-yellow-700 font-medium mb-4">
                        The student management feature is temporarily unavailable. This functionality should be accessed through the Admin Dashboard.
                    </p>
                    <a href="<?php echo e(route('teacher.scores.dashboard')); ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Go to Score Entry
                    </a>
                </div>
            </div>
        </div>

        <!-- Original Form - Commented Out -->
        <!--
        <div class="w-full md:w-1/2 px-4 mb-6">
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-blue-600 text-white px-4 py-3 rounded-t-lg">
                    <h5 class="text-lg font-semibold mb-0">Add Student to <?php echo e($class->name); ?></h5>
                </div>
                <div class="p-4">
                    <?php if($availableStudents->isEmpty()): ?>
                        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                            All available students have already been added to this class.
                        </div>
                    <?php else: ?>
                        <form action="<?php echo e(route('teacher.form-teacher.store-student', $class->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="mb-4">
                                <label for="student_id" class="block text-gray-700 text-sm font-bold mb-2">Select Student</label>
                                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?php $__errorArgs = ['student_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                    id="student_id" name="student_id" required>
                                    <option value="">-- Choose a student --</option>
                                    <?php $__currentLoopData = $availableStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($student->id); ?>">
                                            <?php echo e($student->name); ?> (<?php echo e($student->registration_number); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['student_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-red-500 text-xs italic"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-plus"></i> Add Student
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Current Students - Commented Out -->
        <!--
        <div class="w-full md:w-1/2 px-4">
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-blue-500 text-white px-4 py-3 rounded-t-lg">
                    <h5 class="text-lg font-semibold mb-0">Students in <?php echo e($class->name); ?>

                        <?php if($studentsInClass): ?>
                            <span class="bg-white text-blue-600 px-2 py-1 rounded-full text-sm ml-2"><?php echo e(count($studentsInClass)); ?></span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="p-4">
                    <?php if($studentsInClass && count($studentsInClass) > 0): ?>
                        <div class="space-y-2">
                            <?php
                                $classStudents = \App\Models\User::whereIn('id', $studentsInClass)->orderBy('name')->get();
                            ?>
                            <?php $__currentLoopData = $classStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex justify-between items-center bg-gray-50 p-3 rounded">
                                    <div class="flex items-center gap-3">
                                        <?php if($student->photo): ?>
                                            <img src="<?php echo e(asset('storage/' . $student->photo)); ?>" alt="Profile Picture" class="w-12 h-12 rounded-full object-cover border-2 border-gray-300">
                                        <?php else: ?>
                                            <div class="w-12 h-12 rounded-full bg-gray-300 border-2 border-gray-300 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="font-semibold text-gray-800 mb-1"><?php echo e($student->name); ?></h6>
                                            <p class="text-gray-600 text-sm"><?php echo e($student->registration_number); ?></p>
                                            <?php if($student->date_of_birth): ?>
                                                <p class="text-gray-600 text-sm">DOB: <?php echo e(\Carbon\Carbon::parse($student->date_of_birth)->format('M j, Y')); ?></p>
                                            <?php endif; ?>
                                            <?php if($student->parent_phone_number): ?>
                                                <p class="text-gray-600 text-sm">Parent: <?php echo e($student->parent_phone_number); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <form action="<?php echo e(route('teacher.form-teacher.remove-student', [$class->id, $student->id])); ?>" 
                                        method="POST" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm"
                                            onclick="return confirm('Are you sure you want to remove this student?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                            No students have been added to this class yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        -->
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="<?php echo e(route('teacher.scores.dashboard')); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\teacher\add-students.blade.php ENDPATH**/ ?>