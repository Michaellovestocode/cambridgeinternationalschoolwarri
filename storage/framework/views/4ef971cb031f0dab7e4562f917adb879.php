<?php $__env->startSection('title', 'Parent Portal'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <?php
        $unreadMessagesCount = auth()->user()->receivedMessages()->whereNull('read_at')->count();
    ?>
    <div class="bg-white rounded-3xl shadow-lg p-6">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Parent Portal</h1>
                <p class="text-sm text-gray-500">Monitor <?php echo e($notifications['children']); ?> child(ren), upcoming exam dates, and completed exam activity in one place.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="<?php echo e(route('parent.messages.index')); ?>" class="px-4 py-2 rounded-full text-sm font-semibold bg-rose-50 text-rose-700">
                    Messages <?php echo e($unreadMessagesCount > 0 ? '(' . $unreadMessagesCount . ' new)' : ''); ?>

                </a>
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-blue-50 text-blue-600">Children <?php echo e($notifications['children']); ?></span>
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-amber-50 text-amber-700">Upcoming Exams <?php echo e($notifications['upcomingExams']); ?></span>
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-green-50 text-green-600">New Results <?php echo e($notifications['gradedAttempts']); ?></span>
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-indigo-50 text-indigo-600">Report Cards <?php echo e($notifications['reportCards']); ?></span>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <?php $__empty_1 = true; $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $overview = $childExamOverview[$child->id] ?? null;
                $nextExam = $overview['next_exam'] ?? null;
            ?>
            <div class="bg-white rounded-3xl shadow-lg p-5 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900"><?php echo e($child->name); ?></h2>
                        <p class="text-sm text-gray-500"><?php echo e($child->class?->display_name ?? 'Not assigned yet'); ?></p>
                    </div>
                    <span class="text-xs font-semibold text-gray-500 uppercase"><?php echo e($overview['completed_count'] ?? 0); ?> done</span>
                </div>

                <?php if($nextExam): ?>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700"><?php echo e($nextExam->status_label); ?></p>
                        <p class="mt-1 text-sm font-semibold text-gray-900"><?php echo e($nextExam->title); ?></p>
                        <p class="text-sm text-gray-600"><?php echo e($nextExam->subject ?: 'General exam'); ?></p>
                        <p class="mt-2 text-xs text-gray-500">
                            <?php echo e($nextExam->start_date->format('D, M j, Y g:i A')); ?> to <?php echo e($nextExam->end_date->format('D, M j, Y g:i A')); ?>

                        </p>
                    </div>
                <?php else: ?>
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-sm font-semibold text-gray-700">No active exam schedule right now.</p>
                        <p class="text-xs text-gray-500 mt-1">The next exam will appear here automatically once it is scheduled for this child&apos;s class.</p>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-blue-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-blue-600 font-semibold">Upcoming</p>
                        <p class="text-2xl font-black text-blue-900 mt-1"><?php echo e($overview['upcoming_count'] ?? 0); ?></p>
                    </div>
                    <div class="rounded-2xl bg-green-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-green-600 font-semibold">Completed</p>
                        <p class="text-2xl font-black text-green-900 mt-1"><?php echo e($overview['completed_count'] ?? 0); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="lg:col-span-3 bg-white rounded-3xl shadow-lg p-8 text-center text-gray-500">
                No children are linked to this parent account yet.
            </div>
        <?php endif; ?>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Exam Schedule</h3>
                    <p class="text-sm text-gray-500">Parents can see dates and status only. Questions remain private to the student.</p>
                </div>
            </div>

            <div class="space-y-5">
                <?php $__empty_1 = true; $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $schedule = $childExamOverview[$child->id]['schedule'] ?? collect();
                    ?>
                    <div class="border border-gray-100 rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div>
                                <p class="font-semibold text-gray-900"><?php echo e($child->name); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($child->class?->display_name ?? 'Unassigned'); ?></p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <?php $__empty_2 = true; $__currentLoopData = $schedule; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $examItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <div class="rounded-2xl border border-gray-100 px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-gray-900"><?php echo e($examItem->title); ?></p>
                                        <span class="text-xs font-semibold px-3 py-1 rounded-full
                                            <?php if($examItem->status === 'graded'): ?> bg-green-50 text-green-700
                                            <?php elseif($examItem->status === 'submitted'): ?> bg-blue-50 text-blue-700
                                            <?php elseif($examItem->status === 'in_progress'): ?> bg-amber-50 text-amber-700
                                            <?php elseif($examItem->status === 'open'): ?> bg-orange-50 text-orange-700
                                            <?php elseif($examItem->status === 'missed'): ?> bg-rose-50 text-rose-700
                                            <?php else: ?> bg-gray-100 text-gray-700
                                            <?php endif; ?>">
                                            <?php echo e($examItem->status_label); ?>

                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo e($examItem->start_date->format('D, M j, Y g:i A')); ?> to <?php echo e($examItem->end_date->format('D, M j, Y g:i A')); ?>

                                    </p>
                                    <?php if($examItem->attempt?->submitted_at): ?>
                                        <p class="text-xs text-gray-500 mt-2">
                                            Submitted on <?php echo e($examItem->attempt->submitted_at->format('M j, Y g:i A')); ?>

                                        </p>
                                    <?php elseif($examItem->attempt?->started_at): ?>
                                        <p class="text-xs text-gray-500 mt-2">
                                            Started on <?php echo e($examItem->attempt->started_at->format('M j, Y g:i A')); ?>

                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                <p class="text-sm text-gray-500">No exams scheduled yet for this child.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-gray-500">No children linked yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            <h3 class="text-xl font-bold text-gray-900">Completed Exam Activity</h3>
            <div class="space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $recentActivity = $childExamOverview[$child->id]['recent_activity'] ?? collect();
                    ?>
                    <div class="border border-gray-100 rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div>
                                <p class="font-semibold text-gray-900"><?php echo e($child->name); ?></p>
                                <p class="text-xs text-gray-500">Recent exam outcomes</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <?php $__empty_2 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $examItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-gray-900"><?php echo e($examItem->title); ?></p>
                                        <span class="text-xs font-semibold text-gray-600"><?php echo e($examItem->status_label); ?></span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo e($examItem->start_date->format('M j, Y g:i A')); ?></p>
                                    <?php if($examItem->status === 'graded' && !is_null($examItem->attempt?->total_score)): ?>
                                        <p class="text-sm text-green-700 mt-2">Score released: <?php echo e($examItem->attempt->total_score); ?> / <?php echo e($examItem->total_marks); ?></p>
                                    <?php elseif($examItem->status === 'submitted'): ?>
                                        <p class="text-sm text-blue-700 mt-2">Child has completed this exam and the result is waiting for grading.</p>
                                    <?php elseif($examItem->status === 'missed'): ?>
                                        <p class="text-sm text-rose-700 mt-2">No attempt has been recorded for this exam.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                <p class="text-sm text-gray-500">No completed exam activity yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-gray-500">No children linked yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            <h3 class="text-xl font-bold text-gray-900">Report Cards</h3>
            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $reportCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reportCard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border border-gray-100 rounded-2xl p-4 flex justify-between items-center gap-3">
                        <div>
                            <p class="text-sm text-gray-500"><?php echo e($reportCard->session); ?> • <?php echo e($reportCard->term); ?></p>
                            <p class="font-semibold text-gray-900"><?php echo e($reportCard->student->name ?? 'Student'); ?></p>
                        </div>
                        <a href="<?php echo e(route('parent.report-cards.preview', $reportCard)); ?>" class="text-blue-600 font-semibold text-xs hover:underline">View</a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-gray-500">No report cards issued yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            <h3 class="text-xl font-bold text-gray-900">Communications</h3>
            <p class="text-sm text-gray-500">Admission enquiries and updates.</p>
            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $enquiries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enquiry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border border-gray-100 rounded-2xl p-4 flex justify-between items-start gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900"><?php echo e($enquiry->parent_name); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e(ucfirst(str_replace('_', ' ', $enquiry->status))); ?> • <?php echo e($enquiry->created_at->format('M j, Y')); ?></p>
                            <p class="text-sm text-gray-700 mt-2"><?php echo e(\Illuminate\Support\Str::limit($enquiry->message ?? 'No message provided.', 160)); ?></p>
                        </div>
                        <span class="text-xs px-3 py-1 rounded-full bg-gray-100 text-gray-500"><?php echo e($enquiry->phone); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-gray-500">No enquiries logged yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/parent/dashboard.blade.php ENDPATH**/ ?>