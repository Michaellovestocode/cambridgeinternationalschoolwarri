<?php if($errors->any()): ?>
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-lg p-4">
        <ul class="list-disc list-inside text-sm">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?php echo e($action); ?>" method="POST" class="space-y-5">
    <?php echo csrf_field(); ?>
    <?php if($method !== 'POST'): ?>
        <?php echo method_field($method); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Class</label>
            <select name="school_class_id" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                <option value="">Choose class</option>
                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($class->id); ?>" <?php if(old('school_class_id', $learningSession->school_class_id ?? '') == $class->id): echo 'selected'; endif; ?>>
                        <?php echo e($class->display_name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Subject</label>
            <select name="subject_id" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                <option value="">Choose subject</option>
                <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($subject->id); ?>" <?php if(old('subject_id', $learningSession->subject_id ?? '') == $subject->id): echo 'selected'; endif; ?>>
                        <?php echo e($subject->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Estimated Minutes</label>
            <input type="number" min="1" max="300" name="estimated_minutes" value="<?php echo e(old('estimated_minutes', $learningSession->estimated_minutes ?? 20)); ?>" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
        </div>
    </div>

    <?php if($subjects->isEmpty() || $classes->isEmpty()): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4 text-sm">
            No available class and subject assignment was found for this account yet.
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Session Title</label>
            <input type="text" name="title" value="<?php echo e(old('title', $learningSession->title ?? '')); ?>" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Topic</label>
            <input type="text" name="topic" value="<?php echo e(old('topic', $learningSession->topic ?? '')); ?>" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500"><?php echo e(old('description', $learningSession->description ?? '')); ?></textarea>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Learning Goals</label>
        <textarea name="learning_goals" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="One goal per line"><?php echo e(old('learning_goals', $learningSession->learning_goals ?? '')); ?></textarea>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Lesson Content</label>
        <textarea name="lesson_content" rows="10" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Write the teaching note, worked example, or explanation here."><?php echo e(old('lesson_content', $learningSession->lesson_content ?? '')); ?></textarea>
    </div>

    <label class="inline-flex items-center gap-3">
        <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300" <?php if(old('is_published', $learningSession->is_published ?? false)): echo 'checked'; endif; ?>>
        <span class="font-semibold text-gray-700">Publish for students</span>
    </label>

    <div class="pt-2">
        <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-3 rounded-lg font-bold">
            Save Learning Session
        </button>
    </div>
</form>
<?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/learning-sessions/partials/form.blade.php ENDPATH**/ ?>