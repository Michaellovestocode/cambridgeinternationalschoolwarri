<?php $__env->startSection('title', auth()->user()->isAdmin() ? 'Admin Messages' : 'Teacher Messages'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $parentInboxMessages = collect();
    $teacherInboxMessages = collect();
    $parentSentMessages = collect();
    $teacherSentMessages = collect();

    if (auth()->user()->isAdmin()) {
        $parentInboxMessages = $receivedMessages->filter(fn ($message) => $message->sender?->role === 'parent')->values();
        $teacherInboxMessages = $receivedMessages->filter(fn ($message) => $message->sender?->role === 'teacher')->values();
        $parentSentMessages = $sentMessages->filter(fn ($message) => collect($message->recipient_roles ?? [])->contains('parent'))->values();
        $teacherSentMessages = $sentMessages->filter(fn ($message) => collect($message->recipient_roles ?? [])->contains('teacher'))->values();
    }
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Messages</h1>
            <p class="text-sm text-gray-500">
                <?php if(auth()->user()->isAdmin()): ?>
                    Parent-admin and teacher-admin communication lives here. Teachers cannot see parent messages.
                <?php else: ?>
                    Send private messages to admin only.
                <?php endif; ?>
            </p>
        </div>
        <?php if(auth()->user()->isAdmin()): ?>
        <a href="<?php echo e(route('admin.parents.index')); ?>" class="text-sm text-blue-600 hover:underline">Go to parents page for parent messaging</a>
        <?php endif; ?>
    </div>

    <?php if($errors->any()): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl px-6 py-4 text-sm">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            <?php if(auth()->user()->isAdmin()): ?>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Send Message To Teachers</h2>
                    <p class="text-sm text-gray-500">Tick one teacher for a direct message, or select several teachers for a general staff message.</p>
                </div>

                <form method="POST" action="<?php echo e(route('admin.messages.store')); ?>" class="space-y-4" id="teacher-message-form">
                    <?php echo csrf_field(); ?>
                    <div class="rounded-2xl bg-blue-50 px-4 py-3 text-sm text-blue-700 flex items-center justify-between">
                        <span>Selected teachers</span>
                        <strong id="selected-teachers-count">0</strong>
                    </div>
                    <div class="border border-gray-200 rounded-2xl p-4 max-h-72 overflow-y-auto space-y-3">
                        <label class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                            <input type="checkbox" id="select-all-teachers" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" />
                            Select all teachers
                        </label>
                        <?php $__empty_1 = true; $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <label class="flex items-start gap-3 border border-gray-100 rounded-xl px-3 py-3">
                                <input type="checkbox" class="teacher-message-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500 mt-1" value="<?php echo e($teacher->id); ?>" />
                                <span>
                                    <span class="block font-semibold text-gray-900"><?php echo e($teacher->name); ?></span>
                                    <span class="block text-xs text-gray-500"><?php echo e($teacher->email ?: $teacher->registration_number); ?></span>
                                </span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-sm text-gray-500">No teachers found.</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Subject</label>
                        <input name="subject" value="<?php echo e(old('subject')); ?>" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Optional subject" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Message</label>
                        <textarea name="body" rows="6" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Type the message for the selected teachers..."><?php echo e(old('body')); ?></textarea>
                    </div>
                    <div id="teacher-message-hidden-inputs"></div>
                    <button type="submit" id="teacher-message-submit" class="bg-gray-900 hover:bg-black text-white font-semibold px-5 py-3 rounded-xl shadow disabled:opacity-50" disabled>
                        Send To Teachers
                    </button>
                </form>
            <?php else: ?>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Send Message To Admin</h2>
                    <p class="text-sm text-gray-500">This channel is private between teachers and admin.</p>
                </div>

                <form method="POST" action="<?php echo e(route('admin.messages.store')); ?>" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Subject</label>
                        <input name="subject" value="<?php echo e(old('subject')); ?>" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Optional subject" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Message</label>
                        <textarea name="body" rows="7" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500" placeholder="Type your message to admin..."><?php echo e(old('body')); ?></textarea>
                    </div>
                    <button type="submit" class="bg-gray-900 hover:bg-black text-white font-semibold px-5 py-3 rounded-xl shadow">
                        Send To Admin
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            <?php if(auth()->user()->isAdmin()): ?>
                <div
                    x-data="{
                        inboxTab: localStorage.getItem('adminMessagesInboxTab') || 'parents'
                    }"
                    x-init="$watch('inboxTab', value => localStorage.setItem('adminMessagesInboxTab', value))"
                    class="space-y-4"
                >
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">Inbox</h2>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700"><?php echo e($receivedMessages->count()); ?> messages</span>
                    </div>

                    <div class="inline-flex rounded-2xl bg-gray-100 p-1">
                        <button type="button" @click="inboxTab = 'parents'" :class="inboxTab === 'parents' ? 'bg-white text-gray-900 shadow' : 'text-gray-500'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">
                            Parent Messages (<?php echo e($parentInboxMessages->count()); ?>)
                        </button>
                        <button type="button" @click="inboxTab = 'teachers'" :class="inboxTab === 'teachers' ? 'bg-white text-gray-900 shadow' : 'text-gray-500'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">
                            Teacher Messages (<?php echo e($teacherInboxMessages->count()); ?>)
                        </button>
                    </div>

                    <div x-show="inboxTab === 'parents'" class="space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $parentInboxMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border border-gray-100 rounded-2xl p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900"><?php echo e($message->subject ?: 'No subject'); ?></p>
                                        <p class="text-xs text-gray-500">From <?php echo e($message->sender?->name ?? 'Unknown sender'); ?> • Parent</p>
                                    </div>
                                    <span class="text-xs text-gray-400"><?php echo e($message->created_at->format('M j, Y g:i A')); ?></span>
                                </div>
                                <p class="text-sm text-gray-700 mt-3 whitespace-pre-line"><?php echo e($message->body); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-sm text-gray-500">No parent messages yet.</p>
                        <?php endif; ?>
                    </div>

                    <div x-show="inboxTab === 'teachers'" class="space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $teacherInboxMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border border-gray-100 rounded-2xl p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900"><?php echo e($message->subject ?: 'No subject'); ?></p>
                                        <p class="text-xs text-gray-500">From <?php echo e($message->sender?->name ?? 'Unknown sender'); ?> • Teacher</p>
                                    </div>
                                    <span class="text-xs text-gray-400"><?php echo e($message->created_at->format('M j, Y g:i A')); ?></span>
                                </div>
                                <p class="text-sm text-gray-700 mt-3 whitespace-pre-line"><?php echo e($message->body); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-sm text-gray-500">No teacher messages yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Inbox</h2>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700"><?php echo e($receivedMessages->count()); ?> messages</span>
                </div>

                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $receivedMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border border-gray-100 rounded-2xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900"><?php echo e($message->subject ?: 'No subject'); ?></p>
                                    <p class="text-xs text-gray-500">From <?php echo e($message->sender?->name ?? 'Unknown sender'); ?> • <?php echo e(ucfirst($message->sender?->role ?? 'user')); ?></p>
                                </div>
                                <span class="text-xs text-gray-400"><?php echo e($message->created_at->format('M j, Y g:i A')); ?></span>
                            </div>
                            <p class="text-sm text-gray-700 mt-3 whitespace-pre-line"><?php echo e($message->body); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500">No admin messages yet.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
        <?php if(auth()->user()->isAdmin()): ?>
            <div
                x-data="{
                    sentTab: localStorage.getItem('adminMessagesSentTab') || 'parents'
                }"
                x-init="$watch('sentTab', value => localStorage.setItem('adminMessagesSentTab', value))"
                class="space-y-4"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Sent Messages</h2>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700"><?php echo e($sentMessages->count()); ?> batches</span>
                </div>

                <div class="inline-flex rounded-2xl bg-gray-100 p-1">
                    <button type="button" @click="sentTab = 'parents'" :class="sentTab === 'parents' ? 'bg-white text-gray-900 shadow' : 'text-gray-500'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">
                        To Parents (<?php echo e($parentSentMessages->count()); ?>)
                    </button>
                    <button type="button" @click="sentTab = 'teachers'" :class="sentTab === 'teachers' ? 'bg-white text-gray-900 shadow' : 'text-gray-500'" class="px-4 py-2 rounded-xl text-sm font-semibold transition">
                        To Teachers (<?php echo e($teacherSentMessages->count()); ?>)
                    </button>
                </div>

                <div x-show="sentTab === 'parents'" class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $parentSentMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border border-gray-100 rounded-2xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900"><?php echo e($message->subject ?: 'No subject'); ?></p>
                                    <p class="text-xs text-gray-500">
                                        Sent to <?php echo e($message->recipient_count === 1 ? $message->recipients->first() : $message->recipient_count . ' parents'); ?>

                                    </p>
                                </div>
                                <span class="text-xs text-gray-400"><?php echo e($message->created_at->format('M j, Y g:i A')); ?></span>
                            </div>
                            <?php if($message->recipient_count > 1): ?>
                                <p class="text-xs text-gray-500 mt-2"><?php echo e($message->recipients->join(', ')); ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-700 mt-3 whitespace-pre-line"><?php echo e($message->body); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500">No messages sent to parents yet.</p>
                    <?php endif; ?>
                </div>

                <div x-show="sentTab === 'teachers'" class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $teacherSentMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border border-gray-100 rounded-2xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900"><?php echo e($message->subject ?: 'No subject'); ?></p>
                                    <p class="text-xs text-gray-500">
                                        Sent to <?php echo e($message->recipient_count === 1 ? $message->recipients->first() : $message->recipient_count . ' teachers'); ?>

                                    </p>
                                </div>
                                <span class="text-xs text-gray-400"><?php echo e($message->created_at->format('M j, Y g:i A')); ?></span>
                            </div>
                            <?php if($message->recipient_count > 1): ?>
                                <p class="text-xs text-gray-500 mt-2"><?php echo e($message->recipients->join(', ')); ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-700 mt-3 whitespace-pre-line"><?php echo e($message->body); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500">No messages sent to teachers yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Sent Messages</h2>
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700"><?php echo e($sentMessages->count()); ?> batches</span>
            </div>

            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $sentMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border border-gray-100 rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900"><?php echo e($message->subject ?: 'No subject'); ?></p>
                                <p class="text-xs text-gray-500">
                                    Sent to <?php echo e($message->recipient_count === 1 ? $message->recipients->first() : $message->recipient_count . ' recipients'); ?>

                                </p>
                            </div>
                            <span class="text-xs text-gray-400"><?php echo e($message->created_at->format('M j, Y g:i A')); ?></span>
                        </div>
                        <?php if($message->recipient_count > 1): ?>
                            <p class="text-xs text-gray-500 mt-2"><?php echo e($message->recipients->join(', ')); ?></p>
                        <?php endif; ?>
                        <p class="text-sm text-gray-700 mt-3 whitespace-pre-line"><?php echo e($message->body); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-gray-500">No sent messages yet.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectAllTeachers = document.getElementById('select-all-teachers');
    const teacherCheckboxes = Array.from(document.querySelectorAll('.teacher-message-checkbox'));
    const hiddenInputsContainer = document.getElementById('teacher-message-hidden-inputs');
    const selectedTeachersCount = document.getElementById('selected-teachers-count');
    const teacherSubmitButton = document.getElementById('teacher-message-submit');

    if (!hiddenInputsContainer || !selectedTeachersCount || !teacherSubmitButton) {
        return;
    }

    const syncTeachers = () => {
        const selected = teacherCheckboxes.filter((checkbox) => checkbox.checked);
        hiddenInputsContainer.innerHTML = '';

        selected.forEach((checkbox) => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'recipient_ids[]';
            hiddenInput.value = checkbox.value;
            hiddenInputsContainer.appendChild(hiddenInput);
        });

        selectedTeachersCount.textContent = String(selected.length);
        teacherSubmitButton.disabled = selected.length === 0;

        if (selectAllTeachers) {
            selectAllTeachers.checked = selected.length > 0 && selected.length === teacherCheckboxes.length;
            selectAllTeachers.indeterminate = selected.length > 0 && selected.length < teacherCheckboxes.length;
        }
    };

    if (selectAllTeachers) {
        selectAllTeachers.addEventListener('change', () => {
            teacherCheckboxes.forEach((checkbox) => {
                checkbox.checked = selectAllTeachers.checked;
            });
            syncTeachers();
        });
    }

    teacherCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', syncTeachers);
    });

    syncTeachers();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/messages/index.blade.php ENDPATH**/ ?>