<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" type="image/png" sizes="48x48" href="<?php echo e(asset('favicon-48x48.png')); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('apple-touch-icon.png')); ?>">
    <link rel="manifest" href="<?php echo e(asset('site.webmanifest')); ?>">
    <title><?php echo $__env->yieldContent('title', 'Cambridge International School CBT System'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; }
        body {
            background-image: url('/images/cambridge.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(2px);
            pointer-events: none;
            z-index: 0;
        }
        body::after {
            display: none;
        }
        main { position: relative; z-index: 1; }
        .nav-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); transition: all 0.3s ease; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4); }
        .btn-secondary { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); transition: all 0.3s ease; }
        .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(245, 87, 108, 0.4); }
        .user-badge { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .sidebar-link { transition: all 0.3s ease; }
        .sidebar-link:hover { transform: translateX(4px); }
        .success-bg { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .error-bg { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
        .fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <?php if(auth()->guard()->check()): ?>
    <?php
        if (auth()->user()->isStudent()) {
            $dashboardRoute = 'student.dashboard';
        } elseif (auth()->user()->isParent()) {
            $dashboardRoute = 'parent.dashboard';
        } else {
            $dashboardRoute = 'admin.dashboard';
        }

        $messagesRoute = null;
        $unreadMessagesCount = 0;

        if (auth()->user()->isParent()) {
            $messagesRoute = 'parent.messages.index';
            $unreadMessagesCount = auth()->user()->receivedMessages()->whereNull('read_at')->count();
        } elseif (!auth()->user()->isStudent()) {
            $messagesRoute = 'admin.messages.index';
            $unreadMessagesCount = auth()->user()->receivedMessages()->whereNull('read_at')->count();
        }

        $canManageReportCards = auth()->user()->isAdmin()
            || (auth()->user()->isTeacher()
                && \App\Models\FormTeacher::where('teacher_id', auth()->id())->where('is_active', true)->exists());
    ?>

    <nav class="nav-gradient text-white shadow-2xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-12 h-12 bg-white/20 rounded-2xl backdrop-blur-lg">
                        <img src="<?php echo e(asset('images/schoollogo.jpg')); ?>" alt="School Logo" class="w-8 h-8 rounded-full object-cover">
                        <span class="sr-only">Cambridge International School</span>
                    </div>
                    <div>
                        <h1 class="text-lg font-black">CAMBRIDGE</h1>
                        <p class="text-xs text-white/70 font-medium">International School</p>
                    </div>
                </div>

                <div class="hidden md:flex items-center space-x-1">
                    <a href="<?php echo e(route($dashboardRoute)); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Dashboard
                    </a>
                    <?php if($messagesRoute): ?>
                    <a href="<?php echo e(route($messagesRoute)); ?>" class="relative bg-white/15 hover:bg-white/25 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Messages
                        <?php if($unreadMessagesCount > 0): ?>
                        <span class="absolute -top-2 -right-2 min-w-[1.5rem] h-6 px-2 rounded-full bg-rose-500 text-white text-xs font-bold flex items-center justify-center">
                            <?php echo e($unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount); ?>

                        </span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                    <?php if(auth()->user()->isParent()): ?>
                    <a href="<?php echo e(route('parent.dashboard')); ?>" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Parent Portal
                    </a>
                    <?php elseif(auth()->user()->isStudent()): ?>
                    <a href="<?php echo e(route('student.learning.index')); ?>" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Learning
                    </a>
                    <a href="<?php echo e(route('student.dashboard')); ?>#report-cards" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Report Cards
                    </a>
                    <?php elseif(!auth()->user()->isStudent()): ?>
                    <?php if(auth()->user()->isTeacher()): ?>
                    <a href="<?php echo e(route('teacher.blog.index')); ?>" class="bg-violet-500 hover:bg-violet-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        My Blog
                    </a>
                    <?php endif; ?>
                    <?php if(auth()->user()->canManageBlogStudio()): ?>
                    <a href="<?php echo e(route('admin.blog.index')); ?>" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Blog Studio
                    </a>
                    <?php endif; ?>
                    <?php if(auth()->user()->isAdmin() || auth()->user()->isTeacher()): ?>
                    <a href="<?php echo e(route('admin.learning-sessions.index')); ?>" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Learning
                    </a>
                    <?php endif; ?>
                    <?php if($canManageReportCards): ?>
                    <a href="<?php echo e(route('admin.report-cards')); ?>" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Report Cards
                    </a>
                    <?php endif; ?>
                    <?php if(auth()->user()->isAdmin()): ?>
                    <a href="<?php echo e(route('admin.blog.index')); ?>" class="bg-violet-500 hover:bg-violet-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Blog
                    </a>
                    <a href="<?php echo e(route('admin.fee-clearances.index')); ?>" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Fee Clearance
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="hidden sm:flex flex-col items-end">
                        <p class="text-sm font-semibold text-white"><?php echo e(auth()->user()->name); ?></p>
                        <span class="text-xs text-white/70 font-medium"><?php echo e(ucfirst(auth()->user()->role)); ?></span>
                    </div>

                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center font-bold text-white">
                        <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

                    </div>

                    <form action="<?php echo e(route('logout')); ?>" method="POST" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-secondary text-white px-4 py-2 rounded-lg text-sm font-semibold">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            <div class="md:hidden pb-4">
                <div class="flex flex-wrap gap-2">
                    <a href="<?php echo e(route($dashboardRoute)); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Dashboard
                    </a>
                    <?php if($messagesRoute): ?>
                    <a href="<?php echo e(route($messagesRoute)); ?>" class="relative bg-white/15 hover:bg-white/25 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Messages
                        <?php if($unreadMessagesCount > 0): ?>
                        <span class="absolute -top-2 -right-2 min-w-[1.5rem] h-6 px-2 rounded-full bg-rose-500 text-white text-xs font-bold flex items-center justify-center">
                            <?php echo e($unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount); ?>

                        </span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                    <?php if(auth()->user()->isParent()): ?>
                    <a href="<?php echo e(route('parent.dashboard')); ?>" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Parent Portal
                    </a>
                    <?php elseif(auth()->user()->isStudent()): ?>
                    <a href="<?php echo e(route('student.learning.index')); ?>" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Learning
                    </a>
                    <a href="<?php echo e(route('student.dashboard')); ?>#report-cards" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Report Cards
                    </a>
                    <?php elseif(!auth()->user()->isStudent()): ?>
                    <?php if(auth()->user()->isTeacher()): ?>
                    <a href="<?php echo e(route('teacher.blog.index')); ?>" class="bg-violet-500 hover:bg-violet-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        My Blog
                    </a>
                    <?php endif; ?>
                    <?php if(auth()->user()->canManageBlogStudio()): ?>
                    <a href="<?php echo e(route('admin.blog.index')); ?>" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Blog Studio
                    </a>
                    <?php endif; ?>
                    <?php if(auth()->user()->isAdmin() || auth()->user()->isTeacher()): ?>
                    <a href="<?php echo e(route('admin.learning-sessions.index')); ?>" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Learning
                    </a>
                    <?php endif; ?>
                    <?php if($canManageReportCards): ?>
                    <a href="<?php echo e(route('admin.report-cards')); ?>" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Report Cards
                    </a>
                    <?php endif; ?>
                    <?php if(auth()->user()->isAdmin()): ?>
                    <a href="<?php echo e(route('admin.blog.index')); ?>" class="bg-violet-500 hover:bg-violet-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Blog
                    </a>
                    <a href="<?php echo e(route('admin.fee-clearances.index')); ?>" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Fee Clearance
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if(session('success')): ?>
                <div class="fade-in mb-6 success-bg text-white px-6 py-4 rounded-xl font-semibold shadow-lg flex items-center space-x-3">
                    <span class="text-2xl">OK</span>
                    <span><?php echo e(session('success')); ?></span>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="fade-in mb-6 error-bg text-white px-6 py-4 rounded-xl font-semibold shadow-lg flex items-center space-x-3">
                    <span class="text-2xl">!</span>
                    <span><?php echo e(session('error')); ?></span>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\layouts\app.blade.php ENDPATH**/ ?>