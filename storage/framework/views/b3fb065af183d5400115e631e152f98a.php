<?php $__env->startSection('title', 'Manage Parents'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Parents</h1>
            <p class="text-sm text-gray-500">Review parent accounts and their linked children.</p>
        </div>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-sm text-blue-600 hover:underline">← Back to dashboard</a>
    </div>

    <?php if(session('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-2xl px-6 py-4 text-sm font-semibold">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl px-6 py-4 text-sm">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-3">Add Parent</h3>
        <form method="POST" action="<?php echo e(route('admin.parents.store')); ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Full Name</label>
                <input name="name" value="<?php echo e(old('name')); ?>" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="e.g. Mrs. Johnson" />
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-rose-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Email Address</label>
                <input name="email" type="email" value="<?php echo e(old('email')); ?>" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="parent@example.com" />
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-rose-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Phone / WhatsApp</label>
                <input name="parent_phone_number" value="<?php echo e(old('parent_phone_number')); ?>" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="+234..." />
                <?php $__errorArgs = ['parent_phone_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-rose-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">WhatsApp Number</label>
                <input name="whatsapp_number" value="<?php echo e(old('whatsapp_number')); ?>" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="+234..." />
                <?php $__errorArgs = ['whatsapp_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-rose-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Password</label>
                <input name="password" type="password" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Min 6 characters" />
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-rose-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-5 py-3 rounded-xl shadow">
                    Create Parent Account
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded-2xl shadow">
            <div class="text-sm text-gray-500 uppercase font-semibold tracking-wide">Total Parents</div>
            <div class="text-3xl font-black text-gray-900 mt-2"><?php echo e($totalParents); ?></div>
            <div class="text-xs text-gray-500 mt-1">Showing <?php echo e($parents->count()); ?> matching filters</div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow">
            <div class="text-sm text-gray-500 uppercase font-semibold tracking-wide">Parents With Children</div>
            <div class="text-3xl font-black text-gray-900 mt-2"><?php echo e($parentsWithChildren); ?></div>
            <div class="text-xs text-gray-500 mt-1"><?php echo e($parentsWithoutChildren); ?> without linked students</div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow">
            <div class="text-sm text-gray-500 uppercase font-semibold tracking-wide">Total Linked Students</div>
            <div class="text-3xl font-black text-gray-900 mt-2"><?php echo e($totalChildren); ?></div>
            <div class="text-xs text-gray-500 mt-1">Across all filtered parents</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-6">
        <form method="GET" action="<?php echo e(route('admin.parents.index')); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Search parents</label>
                <input name="search" value="<?php echo e(request('search')); ?>" placeholder="name, email, or phone" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" />
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Filter by class</label>
                <select name="class_id" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500">
                    <option value="">All classes</option>
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>" <?php echo e((string) request('class_id') === (string) $class->id ? 'selected' : ''); ?>>
                            <?php echo e($class->display_name ?? $class->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-5 py-3 rounded-xl shadow">
                    Apply
                </button>
                <a href="<?php echo e(route('admin.parents.index')); ?>" class="text-gray-500 px-5 py-3 border border-gray-200 rounded-xl text-sm hover:bg-gray-100 inline-flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Send Message To Parents</h3>
                <p class="text-sm text-gray-500">Tick one parent for a direct message, or tick multiple parents to send a bulk message.</p>
            </div>
            <a href="<?php echo e(route('admin.messages.index')); ?>" class="text-sm text-blue-600 hover:underline">Open messages inbox</a>
        </div>

        <form method="POST" action="<?php echo e(route('admin.messages.store')); ?>" class="space-y-4" id="bulk-parent-message-form">
            <?php echo csrf_field(); ?>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Subject</label>
                    <input name="subject" value="<?php echo e(old('subject')); ?>" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Optional subject" />
                </div>
                <div class="rounded-2xl bg-blue-50 px-4 py-3 text-sm text-blue-700 flex items-center justify-between">
                    <span>Selected parents</span>
                    <strong id="selected-parents-count">0</strong>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Message</label>
                <textarea name="body" rows="4" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Type the message you want to send to the selected parents..."><?php echo e(old('body')); ?></textarea>
            </div>
            <div id="bulk-parent-message-hidden-inputs"></div>
            <button type="submit" id="bulk-parent-message-submit" class="bg-gray-900 hover:bg-black text-white font-semibold px-5 py-3 rounded-xl shadow disabled:opacity-50" disabled>
                Send Message
            </button>
        </form>
    </div>

    <div class="bg-white shadow rounded-2xl overflow-hidden">
        <div class="w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <input type="checkbox" id="select-all-parents" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" />
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Parent</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Email / Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Children</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Link Child</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $availableStudents = $students->reject(fn ($student) => $parent->children->contains('id', $student->id));
                        ?>
                        <tr>
                            <td class="px-4 py-3 align-top">
                                <input type="checkbox" class="parent-message-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500" value="<?php echo e($parent->id); ?>" data-parent-name="<?php echo e($parent->name); ?>" />
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold"><?php echo e($parent->name); ?></p>
                                <p class="text-xs text-gray-500">Registered: <?php echo e($parent->created_at->format('M j, Y')); ?></p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <div><?php echo e($parent->email); ?></div>
                                <div><?php echo e($parent->parent_phone_number ?? 'No phone'); ?></div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 space-y-2">
                                <?php $__empty_2 = true; $__currentLoopData = $parent->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                    <div class="flex flex-wrap items-center justify-between gap-2 border border-gray-100 rounded-xl px-3 py-2">
                                        <span><?php echo e($child->name); ?> • <?php echo e($child->class?->display_name ?? 'Unassigned'); ?></span>
                                        <form method="POST" action="<?php echo e(route('admin.parents.children.detach', [$parent, $child])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                    <div class="text-xs text-gray-400">No linked students</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 min-w-[240px]">
                                <form method="POST" action="<?php echo e(route('admin.parents.children.attach', $parent)); ?>" class="space-y-2 parent-link-form" data-parent-link-form>
                                    <?php echo csrf_field(); ?>
                                    <select class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500" data-class-filter>
                                        <option value="">Select class first</option>
                                        <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $studentsInClass = $availableStudents->where('class_id', $class->id);
                                            ?>
                                            <?php if($studentsInClass->isNotEmpty()): ?>
                                                <option value="<?php echo e($class->id); ?>">
                                                    <?php echo e($class->display_name ?? $class->name); ?>

                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <select name="student_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 disabled:bg-gray-100 disabled:text-gray-400" data-student-select <?php echo e($availableStudents->isEmpty() ? 'disabled' : ''); ?>>
                                        <option value="">
                                            <?php echo e($availableStudents->isEmpty() ? 'All students linked' : 'Choose class to load students'); ?>

                                        </option>
                                        <?php $__currentLoopData = $availableStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($student->id); ?>" data-class-id="<?php echo e($student->class_id); ?>">
                                                <?php echo e($student->name); ?> - <?php echo e($student->class?->display_name ?? 'Unassigned'); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <p class="text-xs text-gray-500">
                                        Pick a class like Year 11 Eagles first, then choose the student in that class.
                                    </p>
                                    <button type="submit" class="w-full bg-gray-900 hover:bg-black text-white text-xs font-semibold px-4 py-2 rounded-xl disabled:opacity-50" data-link-button <?php echo e($availableStudents->isEmpty() ? 'disabled' : ''); ?>>
                                        <?php echo e($availableStudents->isEmpty() ? 'All students linked' : 'Link Student'); ?>

                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                                No parent accounts yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-parent-link-form]').forEach((form) => {
        const classFilter = form.querySelector('[data-class-filter]');
        const studentSelect = form.querySelector('[data-student-select]');
        const linkButton = form.querySelector('[data-link-button]');
        const studentOptions = Array.from(studentSelect.querySelectorAll('option[data-class-id]'));

        if (!classFilter || !studentSelect || !linkButton) {
            return;
        }

        const emptyText = studentOptions.length === 0 ? 'All students linked' : 'Choose class to load students';

        const renderStudents = () => {
            const selectedClassId = classFilter.value;
            const currentValue = studentSelect.value;
            let hasVisibleOptions = false;

            studentSelect.querySelector('option:first-child').textContent = selectedClassId
                ? 'Select student'
                : emptyText;

            studentOptions.forEach((option) => {
                const shouldShow = selectedClassId !== '' && option.dataset.classId === selectedClassId;
                option.hidden = !shouldShow;

                if (shouldShow) {
                    hasVisibleOptions = true;
                } else if (option.value === currentValue) {
                    studentSelect.value = '';
                }
            });

            studentSelect.disabled = !selectedClassId || !hasVisibleOptions;
            linkButton.disabled = studentSelect.disabled || !studentSelect.value;

            if (selectedClassId && !hasVisibleOptions) {
                studentSelect.querySelector('option:first-child').textContent = 'No unlinked students in this class';
            }
        };

        classFilter.addEventListener('change', () => {
            studentSelect.value = '';
            renderStudents();
        });

        studentSelect.addEventListener('change', () => {
            linkButton.disabled = !studentSelect.value;
        });

        renderStudents();
    });

    const selectAllParents = document.getElementById('select-all-parents');
    const parentCheckboxes = Array.from(document.querySelectorAll('.parent-message-checkbox'));
    const hiddenInputsContainer = document.getElementById('bulk-parent-message-hidden-inputs');
    const selectedParentsCount = document.getElementById('selected-parents-count');
    const bulkSubmitButton = document.getElementById('bulk-parent-message-submit');

    const syncSelectedParents = () => {
        if (!hiddenInputsContainer || !selectedParentsCount || !bulkSubmitButton) {
            return;
        }

        const selected = parentCheckboxes.filter((checkbox) => checkbox.checked);
        hiddenInputsContainer.innerHTML = '';

        selected.forEach((checkbox) => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'recipient_ids[]';
            hiddenInput.value = checkbox.value;
            hiddenInputsContainer.appendChild(hiddenInput);
        });

        selectedParentsCount.textContent = String(selected.length);
        bulkSubmitButton.disabled = selected.length === 0;

        if (selectAllParents) {
            selectAllParents.checked = selected.length > 0 && selected.length === parentCheckboxes.length;
            selectAllParents.indeterminate = selected.length > 0 && selected.length < parentCheckboxes.length;
        }
    };

    if (selectAllParents) {
        selectAllParents.addEventListener('change', () => {
            parentCheckboxes.forEach((checkbox) => {
                checkbox.checked = selectAllParents.checked;
            });
            syncSelectedParents();
        });
    }

    parentCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', syncSelectedParents);
    });

    syncSelectedParents();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\admin\parents\index.blade.php ENDPATH**/ ?>