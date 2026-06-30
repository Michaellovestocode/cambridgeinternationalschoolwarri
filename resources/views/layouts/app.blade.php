<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="48x48" href="/favicon-48x48.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <title>@yield('title', 'Cambridge International School CBT System')</title>
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
        html,
        body {
            max-width: 100%;
        }
        img,
        video,
        canvas,
        svg {
            max-width: 100%;
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
        .mobile-safe-scroll {
            -webkit-overflow-scrolling: touch;
        }
        .desktop-nav-links {
            flex-wrap: wrap;
            overflow-x: visible !important;
            scrollbar-width: none;
        }
        .desktop-nav-links a {
            flex: 0 1 auto;
            white-space: nowrap;
            padding: .45rem .65rem !important;
            border-radius: .55rem !important;
            font-size: .75rem !important;
            line-height: 1.1rem !important;
        }
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }
        .overflow-x-auto > table {
            min-width: 680px;
        }
        @media (min-width: 768px) {
            .auth-nav-row {
                height: auto !important;
                min-height: 4.5rem;
            }
            .desktop-nav-links {
                gap: .35rem !important;
                padding-left: .25rem !important;
                padding-right: .25rem !important;
            }
            .auth-actions {
                gap: .5rem !important;
            }
            .auth-actions button {
                padding: .45rem .7rem !important;
                font-size: .75rem !important;
            }
        }
        @media (max-width: 640px) {
            body {
                background-attachment: scroll;
            }
            main {
                padding-top: 1rem !important;
                padding-bottom: 1.5rem !important;
            }
            .app-shell {
                padding-left: 0.875rem !important;
                padding-right: 0.875rem !important;
            }
            .auth-nav-row {
                height: auto;
                min-height: 3.75rem;
                padding-top: .55rem;
                padding-bottom: .55rem;
                align-items: flex-start;
            }
            .auth-nav-row .h-12 {
                width: 2.25rem !important;
                height: 2.25rem !important;
                border-radius: .85rem !important;
            }
            .auth-nav-row .w-8,
            .auth-nav-row .h-8 {
                width: 1.6rem !important;
                height: 1.6rem !important;
            }
            .auth-nav-row h1 {
                font-size: .82rem !important;
                line-height: 1rem !important;
            }
            .mobile-nav-shell {
                margin-left: -1rem;
                margin-right: -1rem;
                padding: .5rem .75rem .7rem;
                background: rgba(15, 23, 42, .24);
                border-top: 1px solid rgba(255, 255, 255, .16);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08);
            }
            .auth-actions {
                gap: .5rem;
            }
            .auth-actions form,
            .auth-actions button {
                width: auto;
            }
            .auth-actions button {
                padding: .4rem .62rem !important;
                border-radius: .6rem !important;
                font-size: .72rem !important;
            }
            .auth-actions .w-10 {
                width: 2rem !important;
                height: 2rem !important;
                font-size: .8rem !important;
            }
            .mobile-nav-links {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .45rem;
            }
            .mobile-nav-links a {
                display: flex;
                min-height: 2.35rem;
                align-items: center;
                justify-content: center;
                white-space: normal;
                text-align: center;
                border-radius: .7rem;
                padding: .45rem .4rem !important;
                font-size: .72rem !important;
                line-height: .95rem !important;
                box-shadow: 0 7px 16px rgba(15, 23, 42, .14);
            }
            .mobile-action-stack {
                flex-direction: column;
            }
            .mobile-action-stack > * {
                width: 100%;
                justify-content: center;
                text-align: center;
            }
            .mobile-action-stack > div > a {
                display: flex;
                width: 100%;
                justify-content: center;
            }
            input,
            select,
            textarea,
            button {
                font-size: 16px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @auth
    @if(trim($__env->yieldContent('hideAuthNav')) !== 'true')
    @php
        if (auth()->user()->isStudent()) {
            $dashboardRoute = 'student.dashboard';
        } elseif (auth()->user()->isParent()) {
            $dashboardRoute = 'parent.dashboard';
        } elseif (auth()->user()->isNonTeachingStaff()) {
            $dashboardRoute = 'attendance.my';
        } else {
            $dashboardRoute = 'admin.dashboard';
        }

        $messagesRoute = null;
        $unreadMessagesCount = 0;

        if (auth()->user()->isParent()) {
            $messagesRoute = 'parent.messages.index';
            $unreadMessagesCount = auth()->user()->receivedMessages()->whereNull('read_at')->count();
        } elseif (auth()->user()->isAdmin() || auth()->user()->isTeacher() || auth()->user()->isBlogManager()) {
            $messagesRoute = 'admin.messages.index';
            $unreadMessagesCount = auth()->user()->receivedMessages()->whereNull('read_at')->count();
        }

        $canManageReportCards = auth()->user()->isAdmin()
            || auth()->user()->canReviewReportCards()
            || (auth()->user()->isTeacher()
                && \App\Models\FormTeacher::where('teacher_id', auth()->id())->where('is_active', true)->exists());
    @endphp

    <nav class="nav-gradient text-white shadow-2xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="auth-nav-row flex flex-wrap items-center justify-between gap-3 py-3 xl:h-20 xl:flex-nowrap xl:py-0">
                <div class="flex shrink-0 items-center space-x-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-lg">
                        <img src="{{ asset('images/schoollogo.jpg') }}" alt="School Logo" class="w-8 h-8 rounded-full object-cover">
                        <span class="sr-only">Cambridge International School</span>
                    </div>
                    <div class="min-w-0 leading-tight">
                        <h1 class="text-base font-black lg:text-lg">CAMBRIDGE</h1>
                        <p class="hidden text-xs font-medium text-white/70 lg:block">International School</p>
                    </div>
                </div>

                <div class="desktop-nav-links order-3 hidden w-full min-w-0 flex-1 items-center gap-1 overflow-visible px-2 md:flex xl:order-none xl:w-auto">
                    <a href="{{ route($dashboardRoute) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Dashboard
                    </a>
                    @if($messagesRoute)
                    <a href="{{ route($messagesRoute) }}" class="relative bg-white/15 hover:bg-white/25 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Messages
                        @if($unreadMessagesCount > 0)
                        <span class="absolute -top-2 -right-2 min-w-[1.5rem] h-6 px-2 rounded-full bg-rose-500 text-white text-xs font-bold flex items-center justify-center">
                            {{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}
                        </span>
                        @endif
                    </a>
                    @endif
                    @if(auth()->user()->isParent())
                    <a href="{{ route('parent.dashboard') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Parent Portal
                    </a>
                    @elseif(auth()->user()->isNonTeachingStaff())
                    <a href="{{ route('attendance.my') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        My Attendance
                    </a>
                    @elseif(auth()->user()->isStudent())
                    <a href="{{ route('attendance.my') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Attendance
                    </a>
                    <a href="{{ route('student.learning.index') }}" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Learning
                    </a>
                    <a href="{{ route('student.dashboard') }}#report-cards" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Report Cards
                    </a>
                    @elseif(!auth()->user()->isStudent())
                    @if(auth()->user()->isTeacher())
                    <a href="{{ route('teacher.blog.index') }}" class="bg-violet-500 hover:bg-violet-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        My Blog
                    </a>
                    <a href="{{ route('attendance.my') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        My Attendance
                    </a>
                    @endif
                    @if(auth()->user()->canManageBlogStudio())
                    <a href="{{ route('admin.blog.index') }}" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Blog Studio
                    </a>
                    @endif
                    @if(auth()->user()->isAdmin() || auth()->user()->isTeacher())
                    <a href="{{ route('admin.learning-sessions.index') }}" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Learning
                    </a>
                    @endif
                    @if($canManageReportCards)
                    <a href="{{ route('admin.report-cards') }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Report Cards
                    </a>
                    <a href="{{ route('admin.developmental-reports.index') }}" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Development
                    </a>
                    @endif
                    @if(auth()->user()->canReviewReportCards())
                    <a href="{{ route('admin.report-cards.reviews') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Reviews
                    </a>
                    @endif
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.enquiries.index') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Admissions
                    </a>
                    <a href="{{ route('attendance.my') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        My Attendance
                    </a>
                    <a href="{{ route('admin.fee-clearances.index') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Fee Clearance
                    </a>
                    @endif
                    @endif
                </div>

                <div class="auth-actions flex shrink-0 items-center space-x-3">
                    <div class="hidden sm:flex flex-col items-end">
                        <p class="text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                        <span class="text-xs text-white/70 font-medium">{{ ucfirst(auth()->user()->role) }}</span>
                    </div>

                    @if(auth()->user()->photo)
                    <img src="{{ asset('storage/' . auth()->user()->photo) }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-white/40 shadow-sm">
                    @else
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-secondary text-white px-4 py-2 rounded-lg text-sm font-semibold">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            <div class="mobile-nav-shell md:hidden">
                <div class="mobile-nav-links flex flex-wrap gap-2">
                    <a href="{{ route($dashboardRoute) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Dashboard
                    </a>
                    @if($messagesRoute)
                    <a href="{{ route($messagesRoute) }}" class="relative bg-white/15 hover:bg-white/25 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Messages
                        @if($unreadMessagesCount > 0)
                        <span class="absolute -top-2 -right-2 min-w-[1.5rem] h-6 px-2 rounded-full bg-rose-500 text-white text-xs font-bold flex items-center justify-center">
                            {{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}
                        </span>
                        @endif
                    </a>
                    @endif
                    @if(auth()->user()->isParent())
                    <a href="{{ route('parent.dashboard') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Parent Portal
                    </a>
                    @elseif(auth()->user()->isNonTeachingStaff())
                    <a href="{{ route('attendance.my') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        My Attendance
                    </a>
                    @elseif(auth()->user()->isStudent())
                    <a href="{{ route('attendance.my') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Attendance
                    </a>
                    <a href="{{ route('student.learning.index') }}" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Learning
                    </a>
                    <a href="{{ route('student.dashboard') }}#report-cards" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Report Cards
                    </a>
                    @elseif(!auth()->user()->isStudent())
                    @if(auth()->user()->isTeacher())
                    <a href="{{ route('teacher.blog.index') }}" class="bg-violet-500 hover:bg-violet-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        My Blog
                    </a>
                    <a href="{{ route('attendance.my') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        My Attendance
                    </a>
                    @endif
                    @if(auth()->user()->canManageBlogStudio())
                    <a href="{{ route('admin.blog.index') }}" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Blog Studio
                    </a>
                    @endif
                    @if(auth()->user()->isAdmin() || auth()->user()->isTeacher())
                    <a href="{{ route('admin.learning-sessions.index') }}" class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Learning
                    </a>
                    @endif
                    @if($canManageReportCards)
                    <a href="{{ route('admin.report-cards') }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Report Cards
                    </a>
                    <a href="{{ route('admin.developmental-reports.index') }}" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Development
                    </a>
                    @endif
                    @if(auth()->user()->canReviewReportCards())
                    <a href="{{ route('admin.report-cards.reviews') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Reviews
                    </a>
                    @endif
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.enquiries.index') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Admissions
                    </a>
                    <a href="{{ route('attendance.my') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        My Attendance
                    </a>
                    <a href="{{ route('admin.fee-clearances.index') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold transition text-sm">
                        Fee Clearance
                    </a>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </nav>
    @endif
    @endauth

    <main class="min-h-screen py-8">
        <div class="app-shell max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="fade-in mb-6 success-bg text-white px-6 py-4 rounded-xl font-semibold shadow-lg flex items-center space-x-3">
                    <span class="text-2xl">OK</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="fade-in mb-6 error-bg text-white px-6 py-4 rounded-xl font-semibold shadow-lg flex items-center space-x-3">
                    <span class="text-2xl">!</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
