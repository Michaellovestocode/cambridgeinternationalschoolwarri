

<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Form Teacher Banner -->
    <?php if($isFormTeacher ?? false): ?>
        <div class="bg-gradient-to-r from-green-600 to-indigo-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-2">Welcome, Form Teacher! 👨‍🏫</h3>
                <p class="text-white/90">You are assigned as a form teacher. Access score entry and report cards for your class from here.</p>
                </div>
                <div class="flex gap-3 ml-4">
                    <a href="<?php echo e(route('teacher.scores.dashboard')); ?>" 
                       class="bg-white text-indigo-600 hover:bg-indigo-50 px-6 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all whitespace-nowrap">
                        📚 Score Entry
                    </a>
                    <a href="<?php echo e(route('admin.report-cards')); ?>" 
                       class="bg-yellow-300 text-indigo-900 hover:bg-yellow-200 px-6 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all whitespace-nowrap">
                        📄 Report Cards
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Welcome Header with Background -->
    <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-3xl shadow-2xl overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl"></div>
        
        <div class="relative p-8 text-white">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-bold mb-2">Welcome, <?php echo e(Auth::user()->name); ?>! 👨‍💼</h1>
                    <p class="text-white/90 text-lg">Administrator Dashboard</p>
                    <p class="text-white/70 text-sm mt-2 italic">Cambridge International School - CBT System</p>
                </div>
                <div class="bg-white/20 backdrop-blur-md rounded-2xl p-6 border border-white/30">
                    <div class="text-center">
                        <div class="text-5xl font-bold"><?php echo e(\Carbon\Carbon::now()->format('d')); ?></div>
                        <div class="text-sm mt-1"><?php echo e(\Carbon\Carbon::now()->format('F Y')); ?></div>
                        <div class="text-xs mt-2 opacity-80"><?php echo e(\Carbon\Carbon::now()->format('l')); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- Total Exams -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold"><?php echo e($examsCount); ?></div>
                </div>
            </div>
            <div class="text-white/90 font-semibold text-lg">Total Exams</div>
            <div class="text-white/70 text-sm mt-1">All exams in system</div>
        </div>

        <!-- Total Students -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold"><?php echo e($studentsCount); ?></div>
                </div>
            </div>
            <div class="text-white/90 font-semibold text-lg"><?php echo e($isFormTeacher ? 'Class Students' : 'Total Students'); ?></div>
            <div class="text-white/70 text-sm mt-1">
                <?php echo e($isFormTeacher ? ($formTeacherAssignment?->schoolClass?->display_name ?? 'Assigned class') : 'Registered students'); ?>

            </div>
        </div>

        <!-- Recent Attempts -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold"><?php echo e($groupedAttempts->flatten()->count()); ?></div>
                </div>
            </div>
            <div class="text-white/90 font-semibold text-lg">Recent Attempts</div>
            <div class="text-white/70 text-sm mt-1">Latest submissions</div>
        </div>
        <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8m-2 10H5a2 2 0 01-2-2V8a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold"><?php echo e($unreadMessagesCount); ?></div>
                </div>
            </div>
            <div class="text-white/90 font-semibold text-lg">Unread Messages</div>
            <div class="text-white/70 text-sm mt-1">Parents waiting for a response</div>
        </div>
        <?php if(auth()->user()->isAdmin()): ?>
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l5.447 5.447a2 2 0 002.828 0L21 3"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17h18M3 12h18"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold"><?php echo e($newEnquiriesCount); ?></div>
                </div>
            </div>
            <div class="text-white/90 font-semibold text-lg">New Applications</div>
            <p class="text-white/70 text-sm mt-1">Awaiting admissions review</p>
            <a href="<?php echo e(route('admin.enquiries.index', ['status' => \App\Models\AdmissionEnquiry::STATUS_NEW])); ?>" class="mt-4 inline-flex items-center text-white text-sm font-semibold hover:underline">
                View inbox
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions & Recent Exams -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-blue-50 px-6 py-4 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <span class="mr-2">⚡</span> Quick Actions
                </h3>
            </div>
            <div class="p-6 space-y-3">
                <a href="<?php echo e(route('admin.exam.create')); ?>" 
                   class="flex items-center justify-between bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create New Exam
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="<?php echo e(route('admin.exams')); ?>" 
                   class="flex items-center justify-between bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        View All Exams
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="<?php echo e(route('admin.results.index')); ?>" 
                   class="flex items-center justify-between bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Results Portal
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <?php if(auth()->user()->isAdmin() || $isFormTeacher): ?>
                <a href="<?php echo e(route('admin.report-cards')); ?>" 
                   class="flex items-center justify-between bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m-7 5h8a2 2 0 002-2V7.828a2 2 0 00-.586-1.414l-3.828-3.828A2 2 0 0014.172 2H8a2 2 0 00-2 2v16a2 2 0 002 2z"></path>
                        </svg>
                        Manage Report Cards
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                <?php endif; ?>

                <a href="<?php echo e(route('admin.messages.index')); ?>"
                   class="flex items-center justify-between bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8m-2 10H5a2 2 0 01-2-2V8a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z"></path>
                        </svg>
                        Messages
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <?php if($unreadMessagesCount > 0): ?>
                            <span class="bg-white/20 text-white text-xs px-2 py-1 rounded-full"><?php echo e($unreadMessagesCount); ?></span>
                        <?php endif; ?>
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                </a>

                <?php if(auth()->user()->isAdmin()): ?>
                <a href="<?php echo e(route('admin.enquiries.index')); ?>"
                   class="flex items-center justify-between bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm2 0v12h14V5H5zm0 0h14v4H5V5zm6 7h6v2h-6v-2zm-4 0H7a2 2 0 01-2-2v-2a2 2 0 012-2h2"></path>
                        </svg>
                        Admissions Inbox
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="<?php echo e(route('admin.announcements.index')); ?>"
                   class="flex items-center justify-between bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-600 hover:to-cyan-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 5H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 9h10M7 13h6"></path>
                        </svg>
                        Website News And Events
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="<?php echo e(route('admin.blog.index')); ?>"
                   class="flex items-center justify-between bg-gradient-to-r from-violet-500 to-indigo-600 hover:from-violet-600 hover:to-indigo-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                        Blog Moderation
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="<?php echo e(route('admin.students')); ?>" 
                   class="flex items-center justify-between bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Manage Students
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="<?php echo e(route('admin.teachers')); ?>" 
                   class="flex items-center justify-between bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Manage Teachers
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="<?php echo e(route('admin.teachers')); ?>" 
                   class="flex items-center justify-between bg-gradient-to-r from-cyan-500 to-teal-600 hover:from-cyan-600 hover:to-teal-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 7v12h14V7M8 11h3m2 0h3M8 15h3m2 0h3M9 3h6v4H9V3z"></path>
                        </svg>
                        Assign Classes to Teachers
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="<?php echo e(route('admin.parents.index')); ?>"
                   class="flex items-center justify-between bg-gradient-to-r from-pink-500 to-rose-600 hover:from-pink-600 hover:to-rose-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7a4 4 0 118 0 4 4 0 01-8 0zm-3 7a4 4 0 018 0v2h-8v-2zm14-2a3 3 0 116 0 3 3 0 01-6 0zm3 5v2h-6v-2a4 4 0 018 0z"></path>
                        </svg>
                        Manage Parents
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="<?php echo e(route('admin.form-teachers.index')); ?>" 
                   class="flex items-center justify-between bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Manage Form Teachers
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="<?php echo e(route('admin.classes')); ?>" 
                   class="flex items-center justify-between bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Manage Classes
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="<?php echo e(route('admin.blog-managers.index')); ?>"
                   class="flex items-center justify-between bg-gradient-to-r from-slate-800 to-indigo-700 hover:from-slate-900 hover:to-indigo-800 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h8l6 6v8a2 2 0 01-2 2z"></path>
                        </svg>
                        Blog Managers
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="<?php echo e(route('admin.subjects.index')); ?>" 
                   class="flex items-center justify-between bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17.25m20-11.002c0 5.159-4.592 9.513-10 9.513m10-9.513c5.05 0 9.233 4.233 10 9.513M12 19.25m0 0c-5.408 0-10-4.354-10-9.513"></path>
                        </svg>
                        Manage Subjects
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Exams -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <span class="mr-2">📚</span> Recent Exams
                </h3>
            </div>
            <div class="p-6">
                <?php $__empty_1 = true; $__currentLoopData = $recentExams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="border-l-4 border-green-500 bg-green-50 rounded-r-xl pl-4 py-3 mb-3 hover:bg-green-100 transition-colors">
                    <p class="font-bold text-gray-800"><?php echo e($exam->title); ?></p>
                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-semibold">
                            <?php echo e($exam->subject); ?>

                        </span>
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full font-semibold">
                            📝 <?php echo e($exam->questions->count()); ?> questions
                        </span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-12">
                    <div class="text-5xl mb-3">📭</div>
                    <p class="text-gray-500">No exams yet</p>
                    <a href="<?php echo e(route('admin.exam.create')); ?>" class="text-green-600 hover:text-green-700 font-semibold text-sm mt-2 inline-block">
                        Create your first exam →
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Student Attempts Grouped by Class -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-4 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <span class="mr-2">📊</span> Recent Student Attempts by Class
            </h3>
        </div>
        <div class="p-6">
            <?php if($groupedAttempts->isNotEmpty()): ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php $__currentLoopData = $groupedAttempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $className => $attempts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3">
                            <h4 class="text-white font-bold text-lg flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <?php echo e($className); ?>

                                <span class="ml-auto bg-white/20 text-white text-sm px-2 py-1 rounded-full">
                                    <?php echo e($attempts->count()); ?>

                                </span>
                            </h4>
                        </div>
                        <div class="p-4 space-y-3">
                            <?php $__currentLoopData = $attempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-800 text-sm"><?php echo e($attempt->user->name); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo e($attempt->user->registration_number); ?></div>
                                    </div>
                                    <div class="text-right">
                                        <?php if($attempt->status === 'graded'): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full font-semibold">✓ Graded</span>
                                        <?php elseif($attempt->status === 'submitted'): ?>
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full font-semibold">⏳ Pending</span>
                                        <?php else: ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full font-semibold">⚪ In Progress</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-600 mb-2">
                                    <div class="font-medium"><?php echo e($attempt->exam->title); ?></div>
                                    <div class="text-gray-500"><?php echo e($attempt->exam->subject); ?></div>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <div>
                                        <?php if($attempt->total_score !== null): ?>
                                        <span class="font-bold text-gray-800"><?php echo e($attempt->total_score); ?></span>
                                        <span class="text-gray-500">marks</span>
                                        <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-gray-500">
                                        <?php echo e($attempt->created_at->format('d M')); ?>

                                        <div class="text-gray-400"><?php echo e($attempt->created_at->format('H:i')); ?></div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <?php if($attempt->status === 'submitted'): ?>
                                    <a href="<?php echo e(route('admin.attempt.grade', $attempt->id)); ?>" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-semibold inline-flex items-center w-full justify-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Grade
                                    </a>
                                    <?php else: ?>
                                    <a href="<?php echo e(route('admin.exam.results', $attempt->exam_id)); ?>" 
                                       class="text-blue-600 hover:text-blue-800 font-semibold text-xs text-center block">
                                        View Results →
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="text-5xl mb-3">📝</div>
                    <p class="text-gray-500 text-lg">No attempts yet</p>
                    <p class="text-gray-400 text-sm mt-1">Student submissions will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if($isFormTeacher): ?>
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-50 to-cyan-50 px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">My Class Students</h3>
                    <p class="text-sm text-gray-500 mt-1"><?php echo e($formTeacherAssignment?->schoolClass?->display_name ?? 'Assigned class'); ?></p>
                </div>
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-100 text-emerald-700">
                    <?php echo e($classStudents->count()); ?> students
                </span>
            </div>
        </div>
        <div class="p-6">
            <?php if($classStudents->isNotEmpty()): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    <?php $__currentLoopData = $classStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border border-gray-100 rounded-2xl p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
                        <?php if($student->photo): ?>
                            <img src="<?php echo e(asset('storage/' . $student->photo)); ?>" alt="<?php echo e($student->name); ?>" class="w-16 h-16 rounded-2xl object-cover border border-gray-200 shadow-sm">
                        <?php else: ?>
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-100 to-cyan-100 text-emerald-700 flex items-center justify-center text-xl font-black border border-emerald-200">
                                <?php echo e(strtoupper(substr($student->name, 0, 1))); ?>

                            </div>
                        <?php endif; ?>
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 truncate"><?php echo e($student->name); ?></p>
                            <p class="text-xs text-gray-500 mt-1"><?php echo e($student->registration_number); ?></p>
                            <?php if($student->sex): ?>
                                <p class="text-xs text-gray-500 mt-1">Sex: <?php echo e(ucfirst($student->sex)); ?></p>
                            <?php endif; ?>
                            <?php if($student->date_of_birth): ?>
                                <p class="text-xs text-gray-400 mt-1"><?php echo e($student->date_of_birth->format('d/m/Y')); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-500">No students are currently assigned to this class.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>