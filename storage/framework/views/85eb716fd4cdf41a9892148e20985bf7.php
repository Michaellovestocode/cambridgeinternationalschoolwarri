<?php $__env->startSection('title', 'News And Events'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Website News And Events</h1>
            <p class="text-sm text-gray-500 mt-1">Manage homepage cards, ticker headlines, and admissions announcements from one place.</p>
            <p class="text-xs text-gray-400 mt-1">Pinned updates appear first on the homepage, and lower sort-order numbers win when multiple pinned updates exist.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-sm text-blue-600 hover:underline">Back to dashboard</a>
            <a href="<?php echo e(route('admin.announcements.create')); ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-semibold shadow-lg">Create update</a>
        </div>
    </div>

    <div class="bg-white shadow rounded-2xl p-6">
        <form method="GET" action="<?php echo e(route('admin.announcements.index')); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Search</label>
                <input name="search" value="<?php echo e($filters['search']); ?>" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500" placeholder="Title, summary, ticker...">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Category</label>
                <select name="category" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All categories</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category); ?>" <?php if($filters['category'] === $category): echo 'selected'; endif; ?>><?php echo e(ucfirst($category)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    <option value="published" <?php if($filters['status'] === 'published'): echo 'selected'; endif; ?>>Published</option>
                    <option value="draft" <?php if($filters['status'] === 'draft'): echo 'selected'; endif; ?>>Draft</option>
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-xl font-semibold hover:bg-indigo-700 transition">Filter</button>
                <a href="<?php echo e(route('admin.announcements.index')); ?>" class="w-full border border-gray-200 text-gray-700 py-2 rounded-xl text-center">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="bg-white shadow rounded-3xl overflow-hidden border border-gray-100">
                <?php if($announcement->image_url): ?>
                    <img src="<?php echo e($announcement->image_url); ?>" alt="<?php echo e($announcement->title); ?>" class="w-full h-56 object-cover">
                <?php endif; ?>
                <div class="p-6 space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700"><?php echo e(ucfirst($announcement->category)); ?></span>
                        <?php if($announcement->is_published): ?>
                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Published</span>
                        <?php else: ?>
                            <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Draft</span>
                        <?php endif; ?>
                        <?php if($announcement->is_pinned): ?>
                            <span class="inline-flex rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">Pinned</span>
                        <?php endif; ?>
                        <?php if($announcement->show_in_ticker): ?>
                            <span class="inline-flex rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">Ticker</span>
                        <?php endif; ?>
                        <?php if($announcement->parent_messages_sent_at): ?>
                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Sent to parents</span>
                        <?php elseif($announcement->send_to_parent_dashboard): ?>
                            <span class="inline-flex rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700">Parent delivery pending</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h2 class="text-xl font-black text-gray-900"><?php echo e($announcement->title); ?></h2>
                        <p class="text-sm text-gray-500 mt-2"><?php echo e($announcement->summary); ?></p>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3 text-sm text-gray-500">
                        <div>Display date: <?php echo e($announcement->display_date ?? 'Not set'); ?></div>
                        <div>Location: <?php echo e($announcement->location ?: 'Not set'); ?></div>
                        <div>Published at: <?php echo e($announcement->published_at?->format('d M Y, H:i') ?? 'Immediate'); ?></div>
                        <div>Expires at: <?php echo e($announcement->expires_at?->format('d M Y, H:i') ?? 'No expiry'); ?></div>
                        <div>Sort order: <?php echo e($announcement->sort_order); ?></div>
                        <div>Parent delivery: <?php echo e($announcement->parent_messages_sent_at?->format('d M Y, H:i') ?? ($announcement->send_to_parent_dashboard ? 'Pending' : 'Off')); ?></div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 text-sm text-slate-700">
                        <p class="font-semibold text-slate-900">Ticker headline</p>
                        <p class="mt-1"><?php echo e($announcement->ticker_text); ?></p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="<?php echo e(route('admin.announcements.edit', $announcement)); ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl font-semibold">Edit</a>
                        <form method="POST" action="<?php echo e(route('admin.announcements.destroy', $announcement)); ?>" onsubmit="return confirm('Delete this website update?');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="border border-rose-200 text-rose-700 hover:bg-rose-50 px-4 py-2 rounded-xl font-semibold">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="xl:col-span-2 bg-white rounded-3xl shadow p-12 text-center text-gray-500">
                <p class="text-lg font-semibold text-gray-800">No website updates yet.</p>
                <p class="mt-2">Create your first announcement to power the homepage ticker and news cards.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl shadow px-6 py-4">
        <?php echo e($announcements->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/announcements/index.blade.php ENDPATH**/ ?>