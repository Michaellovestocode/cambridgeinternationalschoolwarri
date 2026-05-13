

<?php $__env->startSection('title', 'Student Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Welcome Header with Background -->
    <div class="relative bg-gradient-to-r from-green-600 via-blue-600 to-purple-600 rounded-3xl shadow-2xl overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl"></div>
        
        <div class="relative p-8 text-white">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-6">
                    <?php if(auth()->user()->photo): ?>
                        <img src="<?php echo e(asset('storage/' . auth()->user()->photo)); ?>" alt="Profile Picture" class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                    <?php else: ?>
                        <div class="w-20 h-20 rounded-full bg-white/20 border-4 border-white flex items-center justify-center">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="text-4xl font-bold mb-2">Welcome back, <?php echo e(auth()->user()->name); ?>! 👋</h1>
                        <div class="flex items-center gap-4 flex-wrap mt-3">
                            <p class="text-white/90 text-lg flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                </svg>
                                <?php echo e(auth()->user()->registration_number); ?>

                            </p>
                            <p class="text-white/90 text-lg flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <?php echo e(auth()->user()->class->name ?? 'N/A'); ?>

                            </p>
                            <?php if(auth()->user()->date_of_birth): ?>
                                <p class="text-white/90 text-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <?php echo e(\Carbon\Carbon::parse(auth()->user()->date_of_birth)->format('M j, Y')); ?>

                                </p>
                            <?php endif; ?>
                            <?php if(auth()->user()->parent_phone_number): ?>
                                <p class="text-white/90 text-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <?php echo e(auth()->user()->parent_phone_number); ?>

                                </p>
                            <?php endif; ?>
                        </div>
                        <p class="text-white/70 text-sm mt-3 italic">"My school is a place of light, where dreams are shaped both day and night"</p>
                    </div>
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

    <!-- In Progress Exams Warning -->
    <?php if($inProgressAttempts->count() > 0): ?>
    <div class="bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl shadow-lg p-6 text-white">
        <div class="flex items-center mb-4">
            <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-xl">⚠️ Exams In Progress</h3>
                <p class="text-white/90 text-sm">You have unfinished exams. Continue where you left off!</p>
            </div>
        </div>
        <div class="space-y-3">
            <?php $__currentLoopData = $inProgressAttempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 flex justify-between items-center">
                <div>
                    <p class="font-bold text-lg"><?php echo e($attempt->exam->title); ?></p>
                    <p class="text-white/80 text-sm">Started: <?php echo e($attempt->started_at->format('d M Y, h:i A')); ?></p>
                </div>
                <a href="<?php echo e(route('student.take-exam', $attempt->id)); ?>" 
                   class="bg-white text-orange-600 hover:bg-orange-50 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all whitespace-nowrap">
                    Continue Exam →
                </a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <!-- Available Exams -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold"><?php echo e($availableExams->count()); ?></div>
                </div>
            </div>
            <div class="text-white/90 font-semibold text-lg">Available Exams</div>
            <div class="text-white/70 text-sm mt-1">Ready to take</div>
        </div>

        <!-- Completed Exams -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold"><?php echo e($completedAttempts->count()); ?></div>
                </div>
            </div>
            <div class="text-white/90 font-semibold text-lg">Completed</div>
            <div class="text-white/70 text-sm mt-1">Exams finished</div>
        </div>

        <!-- In Progress -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold"><?php echo e($inProgressAttempts->count()); ?></div>
                </div>
            </div>
            <div class="text-white/90 font-semibold text-lg">In Progress</div>
            <div class="text-white/70 text-sm mt-1">Unfinished exams</div>
        </div>

        <!-- Learning Sessions -->
        <a href="<?php echo e(route('student.learning.index')); ?>" class="block bg-gradient-to-br from-cyan-500 to-emerald-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5S19.832 5.477 21 6.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold"><?php echo e($completedLearningAttempts); ?></div>
                </div>
            </div>
            <div class="text-white/90 font-semibold text-lg">Learning</div>
            <div class="text-white/70 text-sm mt-1">Completed sessions</div>
        </a>
    </div>

    <!-- Learning Sessions -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-cyan-50 to-emerald-50 px-6 py-4 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">Learning Sessions</h3>
                <a href="<?php echo e(route('student.learning.index')); ?>" class="text-cyan-700 hover:underline font-semibold text-sm">View all</a>
            </div>
        </div>
        <div class="p-6">
            <?php $__empty_1 = true; $__currentLoopData = $availableLearningSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="border-2 border-gray-100 hover:border-cyan-300 rounded-2xl p-5 mb-4 hover:shadow-md transition-all">
                <div class="flex flex-wrap justify-between items-start gap-4">
                    <div class="flex-1">
                        <span class="bg-cyan-100 text-cyan-800 text-xs font-bold px-3 py-1 rounded-full"><?php echo e($session->subject->name ?? 'Subject'); ?></span>
                        <h4 class="font-bold text-gray-800 text-lg mt-3"><?php echo e($session->title); ?></h4>
                        <p class="text-sm text-emerald-700 font-semibold mt-1"><?php echo e($session->schoolClass->display_name ?? 'Your class'); ?> • <?php echo e($session->topic); ?></p>
                        <p class="text-sm text-gray-600 mt-2"><?php echo e(Str::limit($session->description, 120)); ?></p>
                    </div>
                    <div class="text-sm text-gray-600 bg-gray-50 rounded-xl px-4 py-3">
                        <div><strong><?php echo e($session->questions_count); ?></strong> questions</div>
                        <div><strong><?php echo e($session->estimated_minutes); ?></strong> mins</div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                    <a href="<?php echo e(route('student.learning.show', $session)); ?>" class="bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-3 rounded-xl font-bold shadow">
                        Start Learning
                    </a>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-10">
                <p class="text-gray-500 font-semibold">No learning sessions published yet</p>
                <p class="text-gray-400 text-sm mt-1">Your teachers can add topic lessons from the admin area.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Available Exams -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-4 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <span class="mr-2">📝</span> Available Exams
            </h3>
        </div>
        <div class="p-6">
            <?php $__empty_1 = true; $__currentLoopData = $availableExams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="border-2 border-gray-100 hover:border-blue-300 rounded-2xl p-6 mb-4 hover:shadow-lg transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <h4 class="text-2xl font-bold text-gray-800 mb-2"><?php echo e($exam->title); ?></h4>
                        <p class="text-gray-600 text-sm"><?php echo e($exam->description); ?></p>
                    </div>
                    <span class="bg-green-100 text-green-800 text-xs font-bold px-4 py-2 rounded-full"><?php echo e($exam->subject); ?></span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div class="bg-blue-50 rounded-xl p-3">
                        <div class="text-xs text-gray-600 mb-1">⏱️ Duration</div>
                        <div class="font-bold text-gray-800"><?php echo e($exam->duration_minutes); ?> mins</div>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-3">
                        <div class="text-xs text-gray-600 mb-1">📊 Total Marks</div>
                        <div class="font-bold text-gray-800"><?php echo e($exam->total_marks); ?></div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3">
                        <div class="text-xs text-gray-600 mb-1">✓ Pass Mark</div>
                        <div class="font-bold text-gray-800"><?php echo e($exam->pass_mark); ?></div>
                    </div>
                    <div class="bg-yellow-50 rounded-xl p-3">
                        <div class="text-xs text-gray-600 mb-1">📝 Questions</div>
                        <div class="font-bold text-gray-800"><?php echo e($exam->questions->count()); ?></div>
                    </div>
                </div>

                <?php if($exam->instructions): ?>
                <div class="bg-blue-50 border-l-4 border-blue-400 rounded-r-xl p-4 mb-4">
                    <p class="text-sm text-gray-700 flex items-start">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span><strong>Instructions:</strong> <?php echo e($exam->instructions); ?></span>
                    </p>
                </div>
                <?php endif; ?>

                <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                    <div class="text-sm text-gray-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Available until: <?php echo e($exam->end_date->format('d M Y')); ?>

                    </div>
                    <a href="<?php echo e(route('student.start-exam', $exam->id)); ?>" 
                       class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all flex items-center">
                        Start Exam
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-16">
                <div class="text-7xl mb-4">📭</div>
                <p class="text-gray-500 text-xl font-semibold">No exams available at the moment</p>
                <p class="text-gray-400 text-sm mt-2">Check back later for new assessments</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Report Cards -->
    <div id="report-cards" class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-amber-50 to-green-50 px-6 py-4 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-800">Report Cards</h3>
        </div>
        <div class="p-6">
            <?php $__empty_1 = true; $__currentLoopData = $reportCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reportCard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="border-2 border-gray-100 hover:border-amber-300 rounded-2xl p-5 mb-4 flex flex-wrap justify-between items-center gap-4 hover:shadow-md transition-all">
                <div>
                    <p class="font-bold text-gray-800 text-lg"><?php echo e($reportCard->session?->name ?? 'Session'); ?> - <?php echo e($reportCard->term?->name ?? 'Term'); ?></p>
                    <p class="text-sm text-gray-600 mt-1"><?php echo e($reportCard->class->display_name ?? 'Class not assigned'); ?></p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">
                            Average: <?php echo e($reportCard->average_score !== null ? number_format($reportCard->average_score, 1) . '%' : 'N/A'); ?>

                        </span>
                        <span class="bg-green-50 text-green-700 text-xs font-bold px-3 py-1 rounded-full">
                            Grade: <?php echo e($reportCard->overall_grade ?? '-'); ?>

                        </span>
                    </div>
                </div>
                <a href="<?php echo e(route('student.report-cards.preview', $reportCard)); ?>"
                   class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-xl font-bold shadow transition-all whitespace-nowrap">
                    View Report Card
                </a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg font-semibold">No published report cards yet</p>
                <p class="text-gray-400 text-sm mt-2">Your report cards will appear here after the school publishes them and approves fee clearance.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Exam History -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-green-50 to-blue-50 px-6 py-4 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <span class="mr-2">📊</span> Exam History
            </h3>
        </div>
        <div class="p-6">
            <?php $__empty_1 = true; $__currentLoopData = $completedAttempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="border-2 border-gray-100 hover:border-green-300 rounded-2xl p-5 mb-4 flex justify-between items-center hover:shadow-md transition-all">
                <div class="flex-1">
                    <h4 class="font-bold text-gray-800 text-lg mb-2"><?php echo e($attempt->exam->title); ?></h4>
                    <div class="flex items-center gap-4 flex-wrap">
                        <p class="text-sm text-gray-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Submitted: <?php echo e($attempt->submitted_at->format('d M Y, h:i A')); ?>

                        </p>
                        <?php if($attempt->isGraded() && $attempt->exam->show_results_to_students): ?>
                        <div class="flex items-center">
                            <span class="text-sm font-semibold text-gray-700 mr-2">Score:</span>
                            <span class="text-2xl font-bold px-4 py-1 rounded-lg <?php echo e($attempt->total_score >= $attempt->exam->pass_mark ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
                                <?php echo e($attempt->total_score); ?>/<?php echo e($attempt->exam->total_marks); ?>

                            </span>
                        </div>
                        <?php elseif($attempt->isGraded()): ?>
                        <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full">Submitted</span>
                        <?php else: ?>
                        <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full">⏳ Pending Grading</span>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="<?php echo e(route('student.view-result', $attempt->id)); ?>" 
                   class="ml-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all whitespace-nowrap flex items-center">
                    <?php echo e($attempt->exam->show_results_to_students ? 'View Details' : 'Submitted'); ?>

                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-16">
                <div class="text-7xl mb-4">📊</div>
                <p class="text-gray-500 text-xl font-semibold">No completed exams yet</p>
                <p class="text-gray-400 text-sm mt-2">Your exam results will appear here after submission</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\student\dashboard.blade.php ENDPATH**/ ?>