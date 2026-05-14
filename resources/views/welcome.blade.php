<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cambridge International School - Education for Excellence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'school-blue': '#1E40AF',
                        'school-yellow': '#FCD34D',
                        'school-green': '#10B981',
                    }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Poppins', sans-serif; }
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        img, video, canvas, svg {
            max-width: 100%;
        }
        .flex > * {
            min-width: 0;
        }
        .break-anywhere {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        /* â”€â”€ Blob animations â”€â”€ */
        .blob-1 {
            border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
            animation: blob-anim 8s ease-in-out infinite;
        }
        .blob-2 {
            border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
            animation: blob-anim 7s ease-in-out infinite reverse;
        }
        @keyframes blob-anim {
            0%, 100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
            50%       { border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%; }
        }

        /* â”€â”€ Hover cards â”€â”€ */
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,.15);
        }

        /* â”€â”€ Student circles â”€â”€ */
        .student-circle {
            width: 150px; height: 150px;
            border-radius: 50% 40% 60% 50%;
            object-fit: cover;
        }
        @media (max-width: 768px) {
            .student-circle { width: 120px; height: 120px; }
        }

        /* Announcement ticker */
        .ticker-wrap {
            overflow: hidden;
            position: relative;
        }
        .ticker-viewport {
            min-width: 0;
            overflow: hidden;
            white-space: nowrap;
        }
        .ticker-track {
            display: inline-flex;
            width: max-content;
            animation: ticker-scroll 60s linear infinite;
            will-change: transform;
        }
        .ticker-track:hover {
            animation-play-state: paused;
        }
        .ticker-item {
            flex: 0 0 auto;
            padding-right: 3rem;
        }
        @keyframes ticker-scroll {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        /* â”€â”€ Fade-in on scroll â”€â”€ */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }
        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* â”€â”€ Testimonial slider â”€â”€ */
        .testimonial-slide { display: none; }
        .testimonial-slide.active { display: block; }

        /* â”€â”€ Active nav link â”€â”€ */
        .nav-link.active { color: #2563EB; }
        .nav-link.active::after {
            content: '';
            display: block;
            height: 2px;
            background: #2563EB;
            border-radius: 2px;
            margin-top: 2px;
        }

        /* â”€â”€ WhatsApp floating button pulse â”€â”€ */
        @keyframes whatsapp-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(37,211,102,.6); }
            70%  { box-shadow: 0 0 0 14px rgba(37,211,102,0); }
            100% { box-shadow: 0 0 0 0 rgba(37,211,102,0); }
        }
        .whatsapp-btn { animation: whatsapp-pulse 2s infinite; }

        /* â”€â”€ Back-to-top â”€â”€ */
        #backToTop {
            opacity: 0; pointer-events: none;
            transition: opacity 0.4s ease;
        }
        #backToTop.visible { opacity: 1; pointer-events: auto; }

        /* â”€â”€ Lightbox â”€â”€ */
        #lightbox {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.9);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        #lightbox.open { display: flex; }

        /* â”€â”€ Sticky nav shadow on scroll â”€â”€ */
        .nav-scrolled { box-shadow: 0 4px 24px rgba(0,0,0,.12) !important; }

        /* â”€â”€ Accordion â”€â”€ */
        .faq-body {
            max-height: 0; overflow: hidden;
            transition: max-height 0.4s ease;
        }
        .faq-body.open { max-height: 300px; }

        /* â”€â”€ Progress bar â”€â”€ */
        #pageProgress {
            position: fixed; top: 0; left: 0;
            height: 3px; width: 0;
            background: linear-gradient(90deg,#2563EB,#10B981);
            z-index: 9999;
            transition: width 0.1s linear;
        }

        @media (max-width: 640px) {
            #mainNav .nav-logo-mark { width: 2.75rem; height: 2.75rem; }
            #mainNav .nav-brand-title { font-size: 1rem; line-height: 1.25rem; }
            #mainNav .nav-brand-subtitle { font-size: .68rem; line-height: 1rem; }
            .about-vision-card {
                position: static !important;
                margin-top: 1rem;
                border-radius: 1.25rem;
            }
            .about-vision-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-yellow-50 to-green-50">

<!-- Page read progress bar -->
<div id="pageProgress"></div>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     ANNOUNCEMENT TICKER
-->
@php
    $defaultTickerAnnouncements = collect([
        (object) ['ticker_text' => '2026/2027 admission is now open. Apply early to secure your child\'s place.'],
        (object) ['ticker_text' => 'Science Olympiad registration and club activities continue this term.'],
        (object) ['ticker_text' => 'Next PTA briefing and admissions enquiries are available through the school office.'],
    ]);
    $homepageTickerItems = ($tickerAnnouncements ?? collect())->isNotEmpty() ? $tickerAnnouncements : $defaultTickerAnnouncements;
    $tickerMessages = $homepageTickerItems->pluck('ticker_text')->map(fn ($item) => 'Latest: ' . strtoupper($item))->values();
    $tickerLine = $tickerMessages->implode('     |     ');
@endphp
<div class="bg-blue-900 text-white text-sm py-3 shadow-lg fixed top-0 left-0 right-0 z-[60] ticker-wrap border-b-4 border-yellow-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3 sm:gap-4">
            <span class="shrink-0 bg-yellow-400 text-blue-950 px-3 py-1 rounded-full text-xs sm:text-sm font-black uppercase" style="letter-spacing:0.2em;">
                Latest News
            </span>
            <div id="topTickerText" class="ticker-viewport flex-1 font-bold uppercase text-white text-xs sm:text-sm lg:text-base" data-messages='@json($tickerMessages)'>
                <div class="ticker-track">
                    <span class="ticker-item">{{ $tickerLine }}</span>
                    <span class="ticker-item" aria-hidden="true">{{ $tickerLine }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="sr-only">{{ $tickerLine }}</div>
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     MOBILE MENU OVERLAY
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<div id="mobileMenu" class="fixed inset-0 bg-black/95 z-50 hidden">
    <div class="flex justify-end p-6">
        <button onclick="toggleMenu()" class="text-white" aria-label="Close menu">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div class="flex flex-col items-center justify-center space-y-8 text-white text-2xl font-semibold mt-12">
        <a href="#home"        onclick="toggleMenu()" class="hover:text-yellow-400 transition">Home</a>
        <a href="#programs"    onclick="toggleMenu()" class="hover:text-yellow-400 transition">Programs</a>
        <a href="#about"       onclick="toggleMenu()" class="hover:text-yellow-400 transition">About</a>
        <a href="#news"        onclick="toggleMenu()" class="hover:text-yellow-400 transition">News</a>
        <a href="#gallery"     onclick="toggleMenu()" class="hover:text-yellow-400 transition">Gallery</a>
        <a href="#faq"         onclick="toggleMenu()" class="hover:text-yellow-400 transition">FAQ</a>
        <a href="#contact"     onclick="toggleMenu()" class="hover:text-yellow-400 transition">Contact</a>
        <div class="flex flex-wrap justify-center gap-3 mt-2">
            <a href="{{ route('blog.index') }}" class="bg-blue-600 text-white px-7 py-3 rounded-full font-bold">Blog</a>
            <a href="{{ route('apply.create') }}" class="bg-white text-gray-900 px-7 py-3 rounded-full font-bold">Apply Now</a>
            <a href="/login" class="bg-gradient-to-r from-blue-600 to-green-600 px-7 py-3 rounded-full font-bold">Login</a>
        </div>
    </div>
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     NAVIGATION
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<nav id="mainNav" class="fixed top-12 w-full z-40 bg-white/90 backdrop-blur-lg shadow-md transition-shadow duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <div class="nav-logo-mark w-14 h-14 bg-gradient-to-br from-blue-600 via-yellow-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg transform rotate-3 shrink-0">
                    <span class="text-white font-black text-2xl -rotate-1"><img src="{{ asset('images/schoollogo.jpg') }}" alt="Vice Principal" class="w-full h-full object-cover rounded-2xl"></span>
                </div>
                <div class="min-w-0">
                    <h1 class="nav-brand-title text-xl font-black text-gray-900">Cambridge</h1>
                    <p class="nav-brand-subtitle text-xs text-gray-600 font-semibold">International School</p>
                </div>
            </div>

            <!-- Desktop nav links -->
            <div class="hidden lg:flex space-x-6">
                <a href="#home"     class="nav-link text-gray-700 hover:text-blue-600 font-semibold transition text-sm">Home</a>
                <a href="#programs" class="nav-link text-gray-700 hover:text-blue-600 font-semibold transition text-sm">Programs</a>
                <a href="#about"    class="nav-link text-gray-700 hover:text-blue-600 font-semibold transition text-sm">About</a>
                <a href="#news"     class="nav-link text-gray-700 hover:text-blue-600 font-semibold transition text-sm">News</a>
                <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-blue-600 font-semibold transition text-sm">Blog</a>
                <a href="#gallery"  class="nav-link text-gray-700 hover:text-blue-600 font-semibold transition text-sm">Gallery</a>
                <a href="#faq"      class="nav-link text-gray-700 hover:text-blue-600 font-semibold transition text-sm">FAQ</a>
                <a href="#contact"  class="nav-link text-gray-700 hover:text-blue-600 font-semibold transition text-sm">Contact</a>
            </div>

            <div class="flex items-center space-x-4">
                <a href="{{ route('blog.index') }}" class="bg-white border-2 border-blue-100 text-blue-700 px-4 sm:px-6 py-2.5 rounded-full font-bold hover:border-blue-500 hover:bg-blue-50 transition text-sm sm:text-base">
                    Blog
                </a>
                <a href="{{ route('apply.create') }}" class="hidden sm:block bg-gradient-to-r from-amber-400 to-orange-500 text-gray-900 px-6 py-2.5 rounded-full font-bold hover:shadow-xl transition transform hover:scale-105">
                    Apply Now
                </a>
                <a href="/login" class="hidden sm:block bg-gradient-to-r from-blue-600 to-green-600 text-white px-6 py-2.5 rounded-full font-bold hover:shadow-xl transition transform hover:scale-105">
                    Login
                </a>
                <button onclick="toggleMenu()" class="lg:hidden" aria-label="Open menu">
                    <svg class="w-7 h-7 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     HERO SECTION
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<section id="home" class="relative min-h-screen flex items-center pt-32 overflow-hidden">
    <div class="absolute top-20 right-10 w-64 h-64 bg-yellow-300 opacity-30 blob-1 blur-3xl"></div>
    <div class="absolute bottom-20 left-10 w-80 h-80 bg-blue-400 opacity-20 blob-2 blur-3xl"></div>
    <div class="absolute top-40 left-1/4 w-40 h-40 bg-green-400 opacity-25 blob-1 blur-2xl"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left -->
            <div class="text-center lg:text-left fade-in-up">
                <div class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-green-100 border-2 border-blue-200 rounded-full px-5 py-2 mb-6 sm:mb-8">
                    <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="text-gray-800 text-sm font-bold">Top-Rated School in Warri</span>
                </div>

                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black text-gray-900 mb-6 leading-tight">
                    Education for<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-purple-600 to-green-600">Excellence.</span>
                </h1>

                <p class="text-lg sm:text-xl text-gray-600 mb-8 sm:mb-10 max-w-xl mx-auto lg:mx-0">
                    A modern learning environment where education meets innovation, inspiring every student to succeed.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('apply.create') }}" class="bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 text-white px-8 py-4 rounded-full font-bold text-lg shadow-xl hover:shadow-2xl transition transform hover:scale-105 flex items-center justify-center space-x-2">
                        <span>Apply Now</span>
                    </a>
                    <a href="{{ route('blog.index') }}" class="bg-blue-600 text-white px-8 py-4 rounded-full font-bold text-lg shadow-xl hover:bg-blue-700 transition flex items-center justify-center space-x-2">
                        <span>Read Blog</span>
                    </a>
                    <a href="/login" class="bg-white border-2 border-gray-200 text-gray-800 px-8 py-4 rounded-full font-bold text-lg hover:border-blue-600 hover:text-blue-600 transition flex items-center justify-center space-x-2">
                        <span>Login</span>
                    </a>
                </div>
            </div>

            <!-- Right â€“ student photos -->
            <div class="relative fade-in-up" style="transition-delay:.2s">
                <div class="grid grid-cols-2 gap-4 sm:gap-6">
                    <div class="flex justify-end">
                        <div class="bg-gradient-to-br from-purple-400 to-purple-600 p-1 shadow-2xl card-hover" style="border-radius:60% 40% 60% 50%">
                            <img src="{{ asset('images/heropage3.png') }}"  alt="Student" class="student-circle bg-purple-100">
                        </div>
                    </div>
                    <div class="mt-8 sm:mt-16">
    <div class="bg-gradient-to-br from-cyan-400 to-cyan-600 p-1 shadow-2xl card-hover" style="border-radius:40% 60% 50% 60%">
        <img src="{{ asset('images/herobox1.png') }}" alt="Student" class="student-circle bg-cyan-100">
    </div>
</div>
                    <div class="flex justify-end -mt-4 sm:-mt-8">
                        <div class="bg-gradient-to-br from-yellow-400 to-orange-500 p-1 shadow-2xl card-hover" style="border-radius:50% 60% 40% 60%">
                            <img src="{{ asset('images/clear.jfif') }}" alt="Student" class="student-circle bg-yellow-100">
                        </div>
                    </div>
                    <div class="mt-0 sm:mt-4">
                        <div class="bg-gradient-to-br from-pink-400 to-pink-600 p-1 shadow-2xl card-hover" style="border-radius:60% 50% 60% 40%">
                            <img src="{{ asset('images/heropage2.png') }}" alt="Student" class="student-circle bg-pink-100">
        
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-yellow-400 rounded-full opacity-70 animate-bounce"></div>
                <div class="absolute top-10 -left-4 w-16 h-16 bg-green-400 rounded-full opacity-60"></div>
            </div>
        </div>
    </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     PROGRAMS SECTION
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<section id="programs" class="py-16 sm:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16 fade-in-up">
            <h2 class="text-4xl sm:text-5xl md:text-6xl font-black text-gray-900 mb-4">Our Programs</h2>
            <p class="text-lg sm:text-xl text-gray-600">Comprehensive educational programs designed to nurture every aspect of student development.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
            <!-- Primary -->
            <div class="card-hover bg-gradient-to-br from-blue-500 to-blue-700 p-8 rounded-3xl shadow-xl text-white fade-in-up">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-black mb-3">Primary/Nursery School</h3>
                <p class="text-blue-100 text-sm leading-relaxed mb-4">Building strong foundations for young learners</p>
                <div class="text-xs font-semibold text-blue-200 mb-4">Ages 2-12</div>
                <a href="#contact" class="inline-block text-xs bg-white/20 hover:bg-white/30 text-white font-bold px-4 py-2 rounded-full transition">Learn More</a>
            </div>

            <!-- High School -->
            <div class="card-hover bg-gradient-to-br from-green-500 to-emerald-700 p-8 rounded-3xl shadow-xl text-white fade-in-up" style="transition-delay:.1s">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-black mb-3">Secondary School</h3>
                <p class="text-green-100 text-sm leading-relaxed mb-4">Preparing students for higher education and career success.</p>
                <div class="text-xs font-semibold text-green-200 mb-4">JSS &amp; SSS</div>
                <a href="#contact" class="inline-block text-xs bg-white/20 hover:bg-white/30 text-white font-bold px-4 py-2 rounded-full transition">Learn More</a>
            </div>

            <!-- Digital Learning -->
            <div class="card-hover bg-gradient-to-br from-purple-500 to-pink-600 p-8 rounded-3xl shadow-xl text-white fade-in-up" style="transition-delay:.2s">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-black mb-3">Digital Learning</h3>
                <p class="text-purple-100 text-sm leading-relaxed mb-4">Innovative education with modern technology and digital tools.</p>
                <div class="text-xs font-semibold text-purple-200 mb-4">CBT Platform</div>
                <a href="/login" class="inline-block text-xs bg-white/20 hover:bg-white/30 text-white font-bold px-4 py-2 rounded-full transition">Open Portal</a>
            </div>

            <!-- Co-Curricular -->
            <div class="card-hover bg-gradient-to-br from-orange-500 to-red-600 p-8 rounded-3xl shadow-xl text-white fade-in-up" style="transition-delay:.3s">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-black mb-3">Co-Curricular</h3>
                <p class="text-orange-100 text-sm leading-relaxed mb-4">Sports, arts, and extracurricular activities for holistic growth.</p>
                <div class="text-xs font-semibold text-orange-200 mb-4">All Levels</div>
                <a href="#gallery" class="inline-block text-xs bg-white/20 hover:bg-white/30 text-white font-bold px-4 py-2 rounded-full transition">See Photos</a>
            </div>
        </div>
    </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     ABOUT CAMBRIDGE
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<section id="about" class="py-16 sm:py-24 bg-gradient-to-br from-slate-950 via-blue-950 to-emerald-950 text-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-[1.05fr_.95fr] gap-10 lg:gap-14 items-center">
            <div class="fade-in-up">
                <div class="inline-flex items-center gap-2 bg-white/10 border border-white/15 rounded-full px-4 py-2 mb-6">
                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                    <span class="text-xs sm:text-sm font-bold uppercase text-yellow-100" style="letter-spacing:.18em;">About Us</span>
                </div>

                <div class="mb-5 flex flex-wrap gap-2 text-xs font-bold uppercase text-blue-50">
                    <span class="rounded-full bg-white/10 border border-white/15 px-3 py-1">Creche</span>
                    <span class="rounded-full bg-white/10 border border-white/15 px-3 py-1">Nursery</span>
                    <span class="rounded-full bg-white/10 border border-white/15 px-3 py-1">Primary</span>
                    <span class="rounded-full bg-white/10 border border-white/15 px-3 py-1">Secondary</span>
                    <span class="rounded-full bg-yellow-300 text-blue-950 px-3 py-1">Day &amp; Boarding</span>
                </div>

                <h2 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight mb-6">
                    Cambridge International School, <span class="text-yellow-300">Warri</span>
                </h2>

                <p class="text-base sm:text-lg text-blue-50/90 leading-8 mb-4">
                    Cambridge International School, Warri is a distinguished education institution committed to delivering world-class education from Creche through Secondary School level.
                </p>
                <p class="text-base sm:text-lg text-blue-50/90 leading-8 mb-8">
                    Established in 1996 and strengthened through visionary leadership, the school has evolved into a centre of excellence known for nurturing well-rounded, disciplined, excellence-driven students who are academically sound, morally upright, and globally competitive.
                </p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white/10 border border-white/15 rounded-2xl p-4">
                        <div class="text-2xl sm:text-3xl font-black text-yellow-300">1996</div>
                        <div class="text-xs font-semibold text-blue-100 mt-1">Established</div>
                    </div>
                    <div class="bg-white/10 border border-white/15 rounded-2xl p-4">
                        <div class="text-2xl sm:text-3xl font-black text-emerald-300">2020</div>
                        <div class="text-xs font-semibold text-blue-100 mt-1">Renewed Vision</div>
                    </div>
                    <div class="bg-white/10 border border-white/15 rounded-2xl p-4">
                        <div class="text-2xl sm:text-3xl font-black text-sky-300">4</div>
                        <div class="text-xs font-semibold text-blue-100 mt-1">School Levels</div>
                    </div>
                    <div class="bg-white/10 border border-white/15 rounded-2xl p-4">
                        <div class="text-2xl sm:text-3xl font-black text-rose-300">3</div>
                        <div class="text-xs font-semibold text-blue-100 mt-1">Curricula Blend</div>
                    </div>
                </div>
            </div>

            <div class="fade-in-up" style="transition-delay:.15s">
                <div class="relative">
                    <img src="{{ asset('images/school life9.jpg') }}" alt="Cambridge students learning" class="w-full h-72 sm:h-[420px] object-cover rounded-[2rem] shadow-2xl border border-white/15">
                    <div class="about-vision-card absolute -bottom-6 left-6 right-6 bg-white text-gray-900 rounded-3xl shadow-2xl p-5">
                        <div class="about-vision-grid grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs font-black uppercase text-blue-600" style="letter-spacing:.16em;">Vision</div>
                                <p class="text-sm font-semibold mt-2 leading-6">To be a globally respected centre of educational excellence, raising transformational leaders for the future.</p>
                            </div>
                            <div>
                                <div class="text-xs font-black uppercase text-emerald-600" style="letter-spacing:.16em;">Mission</div>
                                <p class="text-sm font-semibold mt-2 leading-6">To nurture Godly leaders by bringing out the best in every child through quality education, character development, and innovation.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-20 grid sm:grid-cols-2 lg:grid-cols-4 gap-4 fade-in-up">
            @foreach(['Leadership', 'Excellence', 'Accountability', 'Discipline'] as $valueIndex => $value)
                <div class="bg-white/10 border border-white/15 rounded-2xl p-5">
                    <div class="w-10 h-10 rounded-full bg-yellow-300 text-blue-950 flex items-center justify-center text-sm font-black mb-4">
                        {{ $valueIndex + 1 }}
                    </div>
                    <h3 class="text-lg font-black">{{ $value }}</h3>
                </div>
            @endforeach
        </div>

        <div class="mt-8 grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 bg-white rounded-3xl p-7 shadow-xl fade-in-up">
                <div class="text-xs font-black uppercase text-blue-600 mb-3" style="letter-spacing:.16em;">Unique Approach</div>
                <h3 class="text-2xl font-black text-gray-900 mb-4">Blended learning for global readiness</h3>
                <p class="text-gray-600 leading-7">A blended and enriched curriculum integrating Nigerian, British, and Montessori methodologies so students build strong foundations, independence, critical thinking, and readiness for local and international opportunities.</p>
            </div>

            <div class="bg-white rounded-3xl p-7 shadow-xl fade-in-up" style="transition-delay:.1s">
                <div class="text-xs font-black uppercase text-emerald-600 mb-4" style="letter-spacing:.16em;">Academic Programmes</div>
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <span class="shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-black">1</span>
                        <p class="text-gray-700"><span class="font-bold text-gray-900">Creche &amp; Nursery:</span> Montessori-based early childhood education focused on creativity, independence, and foundational skills.</p>
                    </div>
                    <div class="flex gap-3">
                        <span class="shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-black">2</span>
                        <p class="text-gray-700"><span class="font-bold text-gray-900">Primary:</span> Strong literacy, numeracy, character training, continuous assessment, and individualized learning.</p>
                    </div>
                    <div class="flex gap-3">
                        <span class="shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-black">3</span>
                        <p class="text-gray-700"><span class="font-bold text-gray-900">Secondary:</span> Sciences, Commercial, Arts, Humanities, ICT, Artificial Intelligence, and Robotics.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-7 shadow-xl fade-in-up" style="transition-delay:.2s">
                <div class="text-xs font-black uppercase text-amber-600 mb-4" style="letter-spacing:.16em;">Facilities</div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-amber-50 p-4 text-sm font-bold text-gray-800">Science laboratories</div>
                    <div class="rounded-2xl bg-blue-50 p-4 text-sm font-bold text-gray-800">Library and E-library</div>
                    <div class="rounded-2xl bg-green-50 p-4 text-sm font-bold text-gray-800">ICT and robotics</div>
                    <div class="rounded-2xl bg-rose-50 p-4 text-sm font-bold text-gray-800">Day and boarding</div>
                    <div class="rounded-2xl bg-purple-50 p-4 text-sm font-bold text-gray-800">School clinic</div>
                    <div class="rounded-2xl bg-cyan-50 p-4 text-sm font-bold text-gray-800">Reliable power</div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid lg:grid-cols-2 gap-6">
            <div class="bg-white/10 border border-white/15 rounded-3xl p-7 fade-in-up">
                <div class="text-xs font-black uppercase text-yellow-200 mb-4" style="letter-spacing:.16em;">Co-Curricular Development</div>
                <div class="grid sm:grid-cols-2 gap-3 text-sm font-semibold text-blue-50">
                    <div class="rounded-2xl bg-white/10 p-4">Leadership and personal development</div>
                    <div class="rounded-2xl bg-white/10 p-4">Sports and physical education</div>
                    <div class="rounded-2xl bg-white/10 p-4">Creative arts and cultural activities</div>
                    <div class="rounded-2xl bg-white/10 p-4">ICT and digital skills training</div>
                    <div class="rounded-2xl bg-white/10 p-4">Moral and faith-based development</div>
                    <div class="rounded-2xl bg-white/10 p-4">Excursions and field trips</div>
                </div>
            </div>

            <div class="bg-white/10 border border-white/15 rounded-3xl p-7 fade-in-up" style="transition-delay:.1s">
                <div class="text-xs font-black uppercase text-emerald-200 mb-4" style="letter-spacing:.16em;">Community Impact</div>
                <p class="text-blue-50/90 leading-7 mb-5">Cambridge International School is committed to contributing meaningfully to society through scholarship opportunities, educational outreach, and community engagement initiatives.</p>
                <div class="rounded-2xl bg-yellow-300 text-blue-950 p-5 font-black">
                    "Building Future Leaders Through Excellence, Character, and Innovation."
                </div>
            </div>
        </div>
    </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     FEATURES / WHY CAMBRIDGE
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<section id="why-cambridge" class="py-16 sm:py-24 bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Image -->
            <div class="order-2 lg:order-1 fade-in-up">
                <div class="relative">
                    <img src="{{ asset('images/excursion1.jpg') }}" alt="Classroom" class="rounded-3xl shadow-2xl w-full">
                    <div class="absolute -bottom-6 -right-6 bg-gradient-to-br from-yellow-400 to-orange-500 p-6 rounded-2xl shadow-2xl">
                        <div class="text-white">
                            <div class="text-4xl font-black mb-1">98%</div>
                            <div class="text-sm font-semibold">Success Rate</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="order-1 lg:order-2 fade-in-up" style="transition-delay:.15s">
                <h2 class="text-4xl sm:text-5xl font-black text-gray-900 mb-6">
                    Why Choose <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-green-600">Cambridge?</span>
                </h2>

                <div class="space-y-4">
                    <div class="flex items-start space-x-4 bg-white p-5 rounded-2xl shadow-md card-hover">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-1">Government Approved</h4>
                            <p class="text-sm text-gray-600">Approved at all levels, with preparation for WAEC, NECO, IGCSE, and international academic pathways.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 bg-white p-5 rounded-2xl shadow-md card-hover">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-1">Blended Curriculum</h4>
                            <p class="text-sm text-gray-600">Nigerian and British curriculum strengths supported by Montessori methods, ICT, AI, and robotics.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 bg-white p-5 rounded-2xl shadow-md card-hover">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-1">Expert Teachers</h4>
                            <p class="text-sm text-gray-600">Experienced and dedicated educators across Nursery, Primary, Secondary, ICT, and administration.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 bg-white p-5 rounded-2xl shadow-md card-hover">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-1">Character and Leadership</h4>
                            <p class="text-sm text-gray-600">A strong moral foundation with leadership, personal development, and faith-based growth.</p>
                        </div>
                    </div>

                    <!-- NEW: School bus / safety feature -->
                    <div class="flex items-start space-x-4 bg-white p-5 rounded-2xl shadow-md card-hover">
                        <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17h8M8 17v-2a4 4 0 014-4h0a4 4 0 014 4v2M3 12l2-7h14l2 7M5 12h14"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-1">Safe Learning Environment</h4>
                            <p class="text-sm text-gray-600">Structured classrooms, boarding facilities, sports, clinic support, and a child-focused school culture.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     STAFF SECTION  (NEW)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<section class="py-16 sm:py-24 bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16 fade-in-up">
            <h2 class="text-4xl sm:text-5xl md:text-6xl font-black text-gray-900 mb-4">Meet Our Expert Team</h2>
            <p class="text-lg sm:text-xl text-gray-600">Dedicated educators and professionals committed to nurturing every student's potential</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-6 sm:gap-8">
            <!-- Staff Member 1 -->


             <!-- Staff Member 1 -->
            <div class="bg-white rounded-3xl shadow-lg overflow-hidden card-hover fade-in-up">
                <div class="relative">
    <img src="{{ asset('images/director2.png') }}" alt="Director" class="w-full h-64 object-cover">
    
    <div class="absolute top-4 right-4 bg-gradient-to-r from-blue-600 to-green-600 text-white px-3 py-1 rounded-full text-xs font-bold">
        Director
    </div>
</div>
                <div class="p-6 text-center">
                    <h3 class="font-black text-gray-900 text-lg mb-1">Pst. Paul Awe</h3>
                    <p class="text-purple-600 font-semibold text-sm mb-3">School Director</p>
                    <p class="text-gray-600 text-sm leading-relaxed">A seasoned professional with over two decades of experience in education and banking, bringing strategic leadership, financial expertise, and operational excellence to the school.</p>
                </div>
            </div>



            <div class="bg-white rounded-3xl shadow-lg overflow-hidden card-hover fade-in-up">
                <div class="relative">
    <img src="{{ asset('images/Director.png') }}" alt="Director" class="w-full h-64 object-cover">
    
    <div class="absolute top-4 right-4 bg-gradient-to-r from-blue-600 to-green-600 text-white px-3 py-1 rounded-full text-xs font-bold">
        Director
    </div>
</div>
                <div class="p-6 text-center">
                    <h3 class="font-black text-gray-900 text-lg mb-1">Mrs. Precious Awe</h3>
                    <p class="text-blue-600 font-semibold text-sm mb-3">School Director</p>
                    <p class="text-gray-600 text-sm leading-relaxed">A passionate educationist and visionary leader committed to excellence, discipline, and holistic child development in a supportive learning environment.</p>
                </div>
            </div>

            <!-- Staff Member 2 -->
            <div class="bg-white rounded-3xl shadow-lg overflow-hidden card-hover fade-in-up" style="transition-delay:.1s">
                <div class="relative">
    <img src="{{ asset('images/principal.jpg') }}" alt="Principal" class="w-full h-64 object-cover">
    
    <div class="absolute top-4 right-4 bg-gradient-to-r from-blue-600 to-green-600 text-white px-3 py-1 rounded-full text-xs font-bold">
        Principal
    </div>
</div>
                <div class="p-6 text-center">
                    <h3 class="font-black text-gray-900 text-lg mb-1">Mr. Eziyi John</h3>
                    <p class="text-green-600 font-semibold text-sm mb-3">Principal</p>
                    <p class="text-gray-600 text-sm leading-relaxed">An experienced school leader committed to academic excellence, discipline, and effective teaching practice. He works closely with staff and students to sustain high standards across the school.</p>
                </div>
            </div>

            <!-- Staff Member 3 -->
            <div class="bg-white rounded-3xl shadow-lg overflow-hidden card-hover fade-in-up" style="transition-delay:.2s">
                <div class="relative">
    <img src="{{ asset('images/admin1.jpg') }}" alt="Principal" class="w-full h-64 object-cover">
    
    <div class="absolute top-4 right-4 bg-gradient-to-r from-blue-600 to-green-600 text-white px-3 py-1 rounded-full text-xs font-bold">
        Administrator
    </div>
</div>
                <div class="p-6 text-center">
                    <h3 class="font-black text-gray-900 text-lg mb-1">Mrs Atibaka Toritseju Louisa</h3>
                    <p class="text-purple-600 font-semibold text-sm mb-3">Administrator</p>
                    <p class="text-gray-600 text-sm leading-relaxed">A dedicated administrator who supports smooth school operations, parent communication, and student services. Her work helps maintain an organized, welcoming, and efficient learning environment.</p>
                </div>
            </div>

            <!-- Staff Member 4 -->
            <div class="bg-white rounded-3xl shadow-lg overflow-hidden card-hover fade-in-up" style="transition-delay:.3s">
                <div class="relative">
    <img src="{{ asset('images/vice-principal1.jpg') }}" alt="Principal" class="w-full h-64 object-cover">
    
    <div class="absolute top-4 right-4 bg-gradient-to-r from-blue-600 to-green-600 text-white px-3 py-1 rounded-full text-xs font-bold">
        Vice Principal
    </div>
</div>
                <div class="p-6 text-center">
                    <h3 class="font-black text-gray-900 text-lg mb-1">Mr. Awonuga Daniel Olalekan</h3>
                    <p class="text-orange-600 font-semibold text-sm mb-3">Vice Principal</p>
                    <p class="text-gray-600 text-sm leading-relaxed">A committed educational leader focused on student discipline, academic supervision, and daily school coordination. He supports teachers and learners in building a culture of excellence.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     STATS SECTION
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<section class="py-16 sm:py-20 bg-gradient-to-r from-blue-600 via-purple-600 to-green-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center fade-in-up">
                <div class="text-5xl sm:text-6xl font-black text-white mb-2 counter" data-target="500">0</div>
                <div class="text-white/90 font-semibold text-sm sm:text-base">Happy Students</div>
            </div>
            <div class="text-center fade-in-up" style="transition-delay:.1s">
                <div class="text-5xl sm:text-6xl font-black text-white mb-2 counter" data-target="80">0</div>
                <div class="text-white/90 font-semibold text-sm sm:text-base">Expert Teachers</div>
            </div>
            <div class="text-center fade-in-up" style="transition-delay:.2s">
                <div class="text-5xl sm:text-6xl font-black text-white mb-2 counter" data-target="15">0</div>
                <div class="text-white/90 font-semibold text-sm sm:text-base">Years Excellence</div>
            </div>
            <div class="text-center fade-in-up" style="transition-delay:.3s">
                <div class="text-5xl sm:text-6xl font-black text-white mb-2"><span class="counter" data-target="98">0</span>%</div>
                <div class="text-white/90 font-semibold text-sm sm:text-base">Success Rate</div>
            </div>
        </div>
    </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     NEWS & EVENTS SECTION  (NEW)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<section id="news" class="py-16 sm:py-24 bg-white">
    @php
        $categoryStyles = [
            'achievement' => ['badge' => 'bg-blue-100 text-blue-700', 'image' => asset('images/achieve.jpg')],
            'admission' => ['badge' => 'bg-green-100 text-green-700', 'image' => asset('images/admission.jpg')],
            'event' => ['badge' => 'bg-amber-100 text-amber-700', 'image' => asset('images/summercamp favourite.jpg')],
            'announcement' => ['badge' => 'bg-indigo-100 text-indigo-700', 'image' => asset('images/homepage1.jfif')],
            'academic' => ['badge' => 'bg-rose-100 text-rose-700', 'image' => asset('images/student in class.jpg')],
            'sports' => ['badge' => 'bg-purple-100 text-purple-700', 'image' => asset('images/sport1.jpg')],
        ];
        $fallbackAnnouncements = collect([
            (object) [
                'category' => 'achievement',
                'category_label' => 'Achievement',
                'title' => 'Students are making Cambridge proud across academics and co-curricular life.',
                'summary' => 'Fresh success stories, school highlights, and student achievements will appear here once published by the admin team.',
                'display_date' => now()->format('F j, Y'),
                'button_url' => '#contact',
                'button_label' => 'Contact us',
                'image_url' => asset('images/achieve.jpg'),
                'location' => null,
            ],
            (object) [
                'category' => 'admission',
                'category_label' => 'Admission',
                'title' => 'Admissions remain open for families looking for a modern learning environment.',
                'summary' => 'Use the application page to begin the process, and watch this section for official deadlines, screening dates, and parent briefings.',
                'display_date' => now()->format('F j, Y'),
                'button_url' => route('apply.create'),
                'button_label' => 'Apply now',
                'image_url' => asset('images/admission.jpg'),
                'location' => null,
            ],
            (object) [
                'category' => 'event',
                'category_label' => 'Event',
                'title' => 'Upcoming school events, clubs, and open-day moments will be published here.',
                'summary' => 'This section is now ready for live updates from the admin dashboard, so the homepage can stay current without editing code.',
                'display_date' => now()->format('F j, Y'),
                'button_url' => '#news',
                'button_label' => 'Stay tuned',
                'image_url' => asset('images/club1.jpg'),
                'location' => null,
            ],
        ]);
        $homepageAnnouncements = ($announcements ?? collect())->isNotEmpty() ? $announcements : $fallbackAnnouncements;
    @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12 fade-in-up">
            <h2 class="text-4xl sm:text-5xl font-black text-gray-900 mb-4">Latest News &amp; Events</h2>
            <p class="text-lg text-gray-600">Stay up to date with everything happening at Cambridge through live updates from the school admin team.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($homepageAnnouncements as $index => $announcement)
                @php
                    $style = $categoryStyles[$announcement->category] ?? $categoryStyles["announcement"];
                    $buttonUrl = $announcement->button_url ?: "#contact";
                    $buttonLabel = $announcement->button_label ?: "Read more";
                    $imageUrl = $announcement->image_url ?: $style["image"];
                    $fallbackImageUrl = $style["image"];
                @endphp
                <article class="bg-white rounded-3xl shadow-lg overflow-hidden card-hover fade-in-up border border-gray-100" @if($index > 0) style="transition-delay:.{{ $index }}s" @endif>
                    <img src="{{ $imageUrl }}" alt="{{ $announcement->title }}" class="w-full h-60 object-cover" onerror="this.onerror=null;this.src='{{ $fallbackImageUrl }}';">
                    <div class="p-6">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="inline-block text-xs font-bold {{ $style["badge"] }} px-3 py-1 rounded-full">{{ $announcement->category_label }}</span>
                            @if(!empty($announcement->is_pinned))
                                <span class="inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Pinned</span>
                            @endif
                        </div>
                        <h3 class="font-black text-gray-900 text-lg mb-2">{{ $announcement->title }}</h3>
                        <p class="text-sm text-gray-500 mb-4">{{ $announcement->summary }}</p>
                        <div class="flex items-center justify-between gap-4 text-xs text-gray-400">
                            <span>{{ $announcement->display_date }}@if(!empty($announcement->location)) · {{ $announcement->location }}@endif</span>
                            <a href="{{ $buttonUrl }}" class="text-blue-600 font-semibold hover:underline">{{ $buttonLabel }}</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     TESTIMONIALS SECTION  (NEW)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<section class="py-16 sm:py-24 bg-gradient-to-br from-blue-50 to-green-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl sm:text-5xl font-black text-gray-900 mb-4 fade-in-up">What Parents Say</h2>
        <p class="text-lg text-gray-600 mb-12 fade-in-up">Real testimonials from families in our Cambridge community</p>

        <div class="relative bg-white rounded-3xl shadow-2xl p-8 sm:p-12 fade-in-up">
            <!-- Quote icon -->
            <div class="text-6xl text-blue-200 font-black leading-none mb-4 select-none">"</div>

            <!-- Slides -->
            <div id="testimonialContainer">
                <div class="testimonial-slide active">
                    <p class="text-lg sm:text-xl text-gray-700 italic mb-6 leading-relaxed">
                        My daughter joined Cambridge in JSS1 and her performance has been outstanding. The CBT platform especially helps her practise at home - we can see her results in real time!
                    </p>
                    <div class="flex items-center justify-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-black">A</div>
                        <div class="text-left">
                            <div class="font-bold text-gray-900">Mrs Adunola Balogun</div>
                            <div class="text-sm text-gray-500">Parent, JSS2 Student - Warri</div>
                        </div>
                        <div class="flex text-yellow-400 text-sm ml-2">*****</div>
                    </div>
                </div>

                <div class="testimonial-slide">
                    <p class="text-lg sm:text-xl text-gray-700 italic mb-6 leading-relaxed">
                        The teachers are incredibly dedicated. My son's WAEC results last year - 8 A1s - speak for themselves. Cambridge is the best investment we made for his future.
                    </p>
                    <div class="flex items-center justify-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-black">K</div>
                        <div class="text-left">
                            <div class="font-bold text-gray-900">Mr Kunle Oladele</div>
                            <div class="text-sm text-gray-500">Parent, SS3 Graduate - Surulere</div>
                        </div>
                        <div class="flex text-yellow-400 text-sm ml-2">*****</div>
                    </div>
                </div>

                <div class="testimonial-slide">
                    <p class="text-lg sm:text-xl text-gray-700 italic mb-6 leading-relaxed">
                        From the safe school bus to the prompt communication from teachers via WhatsApp, Cambridge makes parenthood so much easier. Highly recommended for every Lagos parent.
                    </p>
                    <div class="flex items-center justify-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-black">F</div>
                        <div class="text-left">
                            <div class="font-bold text-gray-900">Mrs Fatima Usman</div>
                            <div class="text-sm text-gray-500">Parent, Primary 4 Student - Ikeja</div>
                        </div>
                        <div class="flex text-yellow-400 text-sm ml-2">*****</div>
                    </div>
                </div>
            </div>

            <!-- Dots -->
            <div class="flex justify-center space-x-2 mt-8">
                <button onclick="setSlide(0)" class="testimonial-dot w-3 h-3 rounded-full bg-blue-600 transition" aria-label="Slide 1"></button>
                <button onclick="setSlide(1)" class="testimonial-dot w-3 h-3 rounded-full bg-gray-300 transition" aria-label="Slide 2"></button>
                <button onclick="setSlide(2)" class="testimonial-dot w-3 h-3 rounded-full bg-gray-300 transition" aria-label="Slide 3"></button>
            </div>
        </div>
    </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     GALLERY SECTION
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<section id="gallery" class="py-16 sm:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16 fade-in-up">
            <h2 class="text-4xl sm:text-5xl md:text-6xl font-black text-gray-900 mb-4">School Life</h2>
            <p class="text-lg sm:text-xl text-gray-600">Experience the vibrant Cambridge community</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
            <div class="card-hover rounded-3xl overflow-hidden shadow-lg cursor-pointer fade-in-up" onclick="openLightbox(this)">
                <img src="{{ asset('images/sport.jpg') }}" alt="Students" class="w-full h-48 sm:h-64 object-cover hover:scale-105 transition duration-500">
            </div>
            <div class="card-hover rounded-3xl overflow-hidden shadow-lg cursor-pointer fade-in-up" onclick="openLightbox(this)" style="transition-delay:.05s">
                <img src="{{ asset('images/school life1.jpg') }}" alt="Classroom" class="w-full h-48 sm:h-64 object-cover hover:scale-105 transition duration-500">
            </div>
            <div class="card-hover rounded-3xl overflow-hidden shadow-lg cursor-pointer fade-in-up" onclick="openLightbox(this)" style="transition-delay:.1s">
                <img src="{{ asset('images/boycomputer.jpg') }}" alt="Learning" class="w-full h-48 sm:h-64 object-cover hover:scale-105 transition duration-500">
            </div>
            <div class="card-hover rounded-3xl overflow-hidden shadow-lg cursor-pointer fade-in-up md:col-span-2" onclick="openLightbox(this)" style="transition-delay:.15s">
                <img src="{{ asset('images/lifee.jpg') }}" alt="Technology" class="w-full h-48 sm:h-64 object-cover hover:scale-105 transition duration-500">
            </div>
            <div class="card-hover rounded-3xl overflow-hidden shadow-lg cursor-pointer fade-in-up" onclick="openLightbox(this)" style="transition-delay:.2s">
                <img src="https://images.unsplash.com/photo-1546410531-bb4caa6b424d?w=400&q=80" alt="Group learning" class="w-full h-48 sm:h-64 object-cover hover:scale-105 transition duration-500">
            </div>
        </div>
    </div>
</section>

<!-- Lightbox -->
<div id="lightbox" onclick="closeLightbox()">
    <button class="absolute top-6 right-6 text-white text-4xl z-10 leading-none" onclick="closeLightbox()">âœ•</button>
    <img id="lightboxImg" src="" alt="Gallery" class="max-w-[90vw] max-h-[90vh] rounded-2xl shadow-2xl object-contain">
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     FAQ SECTION  (NEW)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<section id="faq" class="py-16 sm:py-24 bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fade-in-up">
            <h2 class="text-4xl sm:text-5xl font-black text-gray-900 mb-4">Frequently Asked Questions</h2>
            <p class="text-lg text-gray-600">Quick answers for parents and prospective students</p>
        </div>

        <div class="space-y-4 fade-in-up">
            <!-- FAQ 1 -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-6 text-left font-bold text-gray-900 hover:bg-gray-50 transition">
                    <span>What are the school fees for 2025/2026?</span>
                    <svg class="w-5 h-5 text-blue-600 transform transition-transform duration-300 faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-body px-6">
                    <p class="text-gray-600 text-sm pb-5 leading-relaxed">School fees vary by level. Contact our admissions office via the form below or call us for the full fee schedule. We also offer a convenient Paystack / bank transfer payment option with flexible instalment plans.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-6 text-left font-bold text-gray-900 hover:bg-gray-50 transition">
                    <span>How does the CBT portal work?</span>
                    <svg class="w-5 h-5 text-blue-600 transform transition-transform duration-300 faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-body px-6">
                    <p class="text-gray-600 text-sm pb-5 leading-relaxed">Each student is given a unique login to access our Computer-Based Testing platform. From there they can take practice tests, view scores instantly, and track their progress across all subjects. The portal is accessible on any device.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-6 text-left font-bold text-gray-900 hover:bg-gray-50 transition">
                    <span>Is there a school boarding facility for students?</span>
                    <svg class="w-5 h-5 text-blue-600 transform transition-transform duration-300 faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-body px-6">
                    <p class="text-gray-600 text-sm pb-5 leading-relaxed">Yes!!!.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-6 text-left font-bold text-gray-900 hover:bg-gray-50 transition">
                    <span>When is the admission deadline?</span>
                    <svg class="w-5 h-5 text-blue-600 transform transition-transform duration-300 faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-body px-6">
                    <p class="text-gray-600 text-sm pb-5 leading-relaxed">Admissions for the 2025/2026 academic session close on 31 May 2026. We strongly advise applying early as spaces fill up quickly, especially for JSS1 and Primary 1.</p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-6 text-left font-bold text-gray-900 hover:bg-gray-50 transition">
                    <span>How can parents track their child's progress?</span>
                    <svg class="w-5 h-5 text-blue-600 transform transition-transform duration-300 faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-body px-6">
                    <p class="text-gray-600 text-sm pb-5 leading-relaxed">Parents receive a separate portal login where they can view their child's scores, attendance records, and teacher remarks. We also send weekly updates via WhatsApp and email.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     CTA SECTION
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<section class="py-16 sm:py-24 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-96 h-96 bg-yellow-400 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-green-400 rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center fade-in-up">
        <h2 class="text-4xl sm:text-5xl md:text-6xl font-black text-white mb-6">Ready to Start Your Journey?</h2>
        <p class="text-xl sm:text-2xl text-white/90 mb-10">Join thousands of students achieving excellence at Cambridge</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('apply.create') }}" class="bg-white text-gray-900 px-10 py-4 rounded-full font-black text-lg hover:shadow-2xl transition transform hover:scale-105">
                Apply for Admission
            </a>
            <a href="/login" class="bg-white/20 backdrop-blur-sm border-2 border-white text-white px-10 py-4 rounded-full font-black text-lg hover:bg-white/30 transition">
                Portal Login
            </a>
        </div>
    </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     CONTACT / ADMISSION FORM  (UPGRADED)
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<section id="contact" class="py-16 sm:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-start">
            <!-- Info -->
            <div class="fade-in-up">
                <h2 class="text-4xl sm:text-5xl font-black text-gray-900 mb-4">Get In Touch</h2>
                <p class="text-lg text-gray-600 mb-8">Have questions about admissions, fees, or our CBT portal? We're here to help.</p>

                <div class="space-y-5 mb-8">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-xs font-bold text-gray-700">ADDR</div>
                        <div>
                            <div class="font-bold text-gray-900">Address</div>
                            <div class="text-gray-500 text-sm">No. 2 Airport Road, By Kosini Junction, Warri, Delta State, Nigeria</div>
                        </div>
                    </div> 
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-xs font-bold text-gray-700">MAIL</div>
                        <div>
                            <div class="font-bold text-gray-900">Email</div>
                            <a href="mailto:info@cambridgeinternationalschoolwarri.com" class="break-anywhere text-blue-600 text-sm hover:underline">info@cambridgeinternationalschoolwarri.com</a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-xs font-bold text-gray-700">WEB</div>
                        <div>
                            <div class="font-bold text-gray-900">Website</div>
                            <a href="https://www.cambridgeinternationalschoolwarri.com" target="_blank" rel="noopener noreferrer" class="break-anywhere text-blue-600 text-sm hover:underline">www.cambridgeinternationalschoolwarri.com</a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center text-xs font-bold text-gray-700">CALL</div>
                        <div>
                            <div class="font-bold text-gray-900">Phone / WhatsApp</div>
                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                <a href="https://wa.me/2348032897744" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">WhatsApp</a>
                                <span class="text-gray-300">|</span>
                                <a href="tel:+2348032897744" class="text-blue-600 hover:underline">Call</a>
                                <span class="text-gray-500">(+234) 803-289-7744</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-xs font-bold text-gray-700">HRS</div>
                        <div>
                            <div class="font-bold text-gray-900">Office Hours</div>
                            <div class="text-gray-500 text-sm">Mon-Fri: 7:30am - 4:00pm</div>
                        </div>
                    </div>
                </div>

                <!-- Social links -->
                <div class="flex space-x-3">
                    <a href="https://www.facebook.com/CambridgeintWarri/" class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold hover:scale-110 transition" title="Facebook">f</a>
                    <a href="#" class="w-10 h-10 bg-gradient-to-br from-pink-500 to-orange-400 text-white rounded-full flex items-center justify-center text-sm font-bold hover:scale-110 transition" title="Instagram">ig</a>
                    <a href="https://wa.me/2348032897744" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-bold hover:scale-110 transition" title="WhatsApp">wa</a>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-gradient-to-br from-blue-50 to-green-50 p-8 rounded-3xl shadow-lg fade-in-up" style="transition-delay:.15s">
                <h3 class="text-2xl font-black text-gray-900 mb-6">Admission Enquiry</h3>
                <a href="{{ route('apply.create') }}" class="mb-5 inline-flex items-center rounded-full bg-gradient-to-r from-amber-400 to-orange-500 px-5 py-2 text-sm font-bold text-gray-900 shadow hover:shadow-lg">
                    Need the full form? Open the Apply Now page
                </a>
                <div id="formSuccess" class="hidden bg-green-100 border border-green-300 text-green-800 rounded-xl p-4 mb-6 text-sm font-semibold">
                    Thank you! We'll be in touch within 24 hours.
                </div>
                <div id="formError" class="hidden bg-red-100 border border-red-300 text-red-800 rounded-xl p-4 mb-6 text-sm font-semibold"></div>
                <form class="space-y-4" id="admissionForm" onsubmit="submitForm(event)">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Parent / Guardian Name *</label>
                            <input type="text" id="parentName" placeholder="e.g. Mrs Balogun" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Phone / WhatsApp *</label>
                            <input type="tel" id="phone" placeholder="+234-803-289-7744" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="email" placeholder="parent@email.com" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Student's Name *</label>
                            <input type="text" id="studentName" placeholder="e.g. Tunde Balogun" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Class Applying For *</label>
                            <select id="classLevel" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                                <option value="">-- Select Level --</option>
                                <optgroup label="Nursery School">
                                    <option>Nursery 1</option>
                                    <option>Nursery 2</option>
                                    <option>Nursery 3</option>
                                </optgroup>
                                <optgroup label="Primary School">
                                    <option>Primary 1</option>
                                    <option>Primary 2</option>
                                    <option>Primary 3</option>
                                    <option>Primary 4</option>
                                    <option>Primary 5</option>
                                    <option>Primary 6</option>
                                </optgroup>
                                <optgroup label="Junior Secondary">
                                    <option>JSS 1</option>
                                    <option>JSS 2</option>
                                    <option>JSS 3</option>
                                </optgroup>
                                <optgroup label="Senior Secondary">
                                    <option>SS 1</option>
                                    <option>SS 2</option>
                                    <option>SS 3</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Message / Questions</label>
                        <textarea id="message" rows="3" placeholder="Any questions about fees, hostel, or .....?" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white resize-none"></textarea>
                    </div>
                    <button id="submitEnquiryButton" type="submit" class="w-full bg-gradient-to-r from-blue-600 to-green-600 text-white py-4 rounded-xl font-bold text-base hover:shadow-xl transition transform hover:scale-[1.02]">
                        Send Enquiry
                    </button>
                    <a id="whatsappFollowUp" href="https://wa.me/2348032897744" target="_blank" rel="noopener noreferrer" class="hidden w-full text-center bg-green-500 text-white py-4 rounded-xl font-bold text-base hover:bg-green-600 transition">
                        Continue on WhatsApp
                    </a>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     FOOTER
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<footer class="bg-gray-900 text-white pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <!-- About -->
            <div class="sm:col-span-2">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 via-yellow-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <span class="text-white font-black text-xl">C</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-black">Cambridge International</h3>
                        <p class="text-sm text-gray-400">Education for Excellence</p>
                    </div>
                </div>
                <p class="text-gray-400 mb-6 leading-relaxed max-w-md">
                    Empowering students with world-class education and modern learning tools for success in the 21st century.
                </p>
                <div class="flex space-x-3">
                    <a href="#" class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-xs font-bold hover:scale-110 transition">f</a>
                    <a href="#" class="w-9 h-9 bg-gradient-to-br from-pink-500 to-orange-400 rounded-full flex items-center justify-center text-xs font-bold hover:scale-110 transition">ig</a>
                    <a href="https://wa.me/2348032897744" target="_blank" rel="noopener noreferrer" class="w-9 h-9 bg-green-500 rounded-full flex items-center justify-center text-xs font-bold hover:scale-110 transition">wa</a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="font-bold text-lg mb-4">Quick Links</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="#home"     class="hover:text-yellow-400 transition">Home</a></li>
                    <li><a href="#programs" class="hover:text-yellow-400 transition">Programs</a></li>
                    <li><a href="#news"     class="hover:text-yellow-400 transition">News &amp; Events</a></li>
                    <li><a href="#gallery"  class="hover:text-yellow-400 transition">Gallery</a></li>
                    <li><a href="#faq"      class="hover:text-yellow-400 transition">FAQ</a></li>
                    <li><a href="/login"    class="hover:text-yellow-400 transition">Student Portal</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="font-bold text-lg mb-4">Contact</h4>
                <ul class="space-y-3 text-gray-400">
                    <li class="grid grid-cols-1 sm:grid-cols-[5.5rem_1fr] gap-1 sm:gap-3"><span class="font-semibold text-white">Address:</span><span class="min-w-0 leading-6">No. 2 Airport Road, By Kosini Junction, Warri, Delta State, Nigeria</span></li>
                    <li class="grid grid-cols-1 sm:grid-cols-[5.5rem_1fr] gap-1 sm:gap-3"><span class="font-semibold text-white">Email:</span><a href="mailto:info@cambridgeinternationalschoolwarri.com" class="min-w-0 break-anywhere leading-6 hover:text-yellow-400 transition">info@cambridgeinternationalschoolwarri.com</a></li>
                    <li class="grid grid-cols-1 sm:grid-cols-[5.5rem_1fr] gap-1 sm:gap-3"><span class="font-semibold text-white">Website:</span><a href="https://www.cambridgeinternationalschoolwarri.com" target="_blank" rel="noopener noreferrer" class="min-w-0 break-anywhere leading-6 hover:text-yellow-400 transition">www.cambridgeinternationalschoolwarri.com</a></li>
                    <li class="grid grid-cols-1 sm:grid-cols-[5.5rem_1fr] gap-1 sm:gap-3"><span class="font-semibold text-white">Phone:</span><span class="min-w-0 leading-6"><a href="https://wa.me/2348032897744" target="_blank" rel="noopener noreferrer" class="hover:text-yellow-400 transition">WhatsApp</a> / <a href="tel:+2348032897744" class="hover:text-yellow-400 transition">Call</a> <span class="text-gray-400">(+234) 803-289-7744</span></span></li>
                    <li class="grid grid-cols-1 sm:grid-cols-[5.5rem_1fr] gap-1 sm:gap-3"><span class="font-semibold text-white">Hours:</span><span class="min-w-0 leading-6">Mon-Fri: 7:30am-4:00pm</span></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-8 text-center text-gray-400 text-sm">
            <p>&copy; 2026 Cambridge International School. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     FLOATING BUTTONS
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<!-- WhatsApp float -->
<a href="https://wa.me/2348032897744" target="_blank" rel="noopener noreferrer"
   class="whatsapp-btn fixed bottom-24 right-6 z-50 w-14 h-14 bg-green-500 rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition"
   title="Chat on WhatsApp" aria-label="WhatsApp">
    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
        <path d="M20.52 3.48A11.93 11.93 0 0012.01 0C5.37 0 .01 5.37.01 12c0 2.11.55 4.16 1.6 5.97L0 24l6.19-1.62A11.95 11.95 0 0012.01 24c6.63 0 11.99-5.37 11.99-12 0-3.2-1.25-6.22-3.48-8.52zM12.01 21.9a9.88 9.88 0 01-5.04-1.38l-.36-.21-3.74.98 1-3.63-.24-.38A9.83 9.83 0 012.1 12c0-5.46 4.44-9.9 9.91-9.9 2.65 0 5.13 1.03 6.99 2.91a9.84 9.84 0 012.9 6.99c0 5.47-4.44 9.9-9.89 9.9zm5.44-7.4c-.3-.15-1.76-.87-2.03-.97-.28-.1-.48-.15-.68.15-.2.3-.76.97-.93 1.17-.17.2-.34.22-.64.07-.3-.15-1.25-.46-2.39-1.47-.88-.79-1.47-1.76-1.64-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.34.45-.51.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.68-1.63-.93-2.23-.24-.58-.49-.5-.68-.51-.18-.01-.37-.01-.57-.01-.2 0-.52.07-.79.37-.28.3-1.05 1.03-1.05 2.5s1.07 2.9 1.22 3.1c.15.2 2.11 3.22 5.1 4.51.71.31 1.27.49 1.7.62.72.23 1.37.2 1.89.12.57-.09 1.76-.72 2.01-1.42.24-.69.24-1.29.17-1.41-.07-.13-.27-.2-.57-.35z"/>
    </svg>
</a>

<!-- Back to top -->
<button id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})"
   class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-gradient-to-br from-blue-600 to-green-600 text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition"
   aria-label="Back to top">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
</button>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     JAVASCRIPT
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<script>
    /* â”€â”€ Mobile Menu â”€â”€ */
    function toggleMenu() {
        document.getElementById('mobileMenu').classList.toggle('hidden');
    }

    /* â”€â”€ Counter Animation â”€â”€ */
    function animateCounter(el) {
        const target = parseInt(el.getAttribute('data-target'));
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                el.textContent = target.toLocaleString();
                clearInterval(timer);
            } else {
                el.textContent = Math.floor(current).toLocaleString();
            }
        }, 16);
    }

    const counterObserver = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                animateCounter(e.target);
                counterObserver.unobserve(e.target);
            }
        });
    }, { threshold: 0.5 });
    document.querySelectorAll('.counter').forEach(c => counterObserver.observe(c));

    /* â”€â”€ Fade-in on scroll â”€â”€ */
    const fadeObserver = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                fadeObserver.unobserve(e.target);
            }
        });
    }, { threshold: 0.12 });
    document.querySelectorAll('.fade-in-up').forEach(el => fadeObserver.observe(el));

    /* â”€â”€ Smooth scroll â”€â”€ */
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function(e) {
            e.preventDefault();
            const t = document.querySelector(this.getAttribute('href'));
            if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    /* â”€â”€ Page progress bar â”€â”€ */
    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const pct = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
        document.getElementById('pageProgress').style.width = pct + '%';
    });

    /* â”€â”€ Nav active section highlight â”€â”€ */
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    const sectionObserver = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                navLinks.forEach(l => l.classList.remove('active'));
                const active = document.querySelector(`.nav-link[href="#${e.target.id}"]`);
                if (active) active.classList.add('active');
            }
        });
    }, { threshold: 0.4 });
    sections.forEach(s => sectionObserver.observe(s));

    /* â”€â”€ Nav shadow on scroll â”€â”€ */
    window.addEventListener('scroll', () => {
        document.getElementById('mainNav').classList.toggle('nav-scrolled', window.scrollY > 20);
        document.getElementById('backToTop').classList.toggle('visible', window.scrollY > 400);
    });

    /* â”€â”€ Testimonial Slider â”€â”€ */
    let currentSlide = 0;
    function setSlide(n) {
        const slides = document.querySelectorAll('.testimonial-slide');
        const dots   = document.querySelectorAll('.testimonial-dot');
        slides.forEach((s,i) => s.classList.toggle('active', i === n));
        dots.forEach((d,i) => {
            d.classList.toggle('bg-blue-600', i === n);
            d.classList.toggle('bg-gray-300', i !== n);
        });
        currentSlide = n;
    }
    setInterval(() => setSlide((currentSlide + 1) % 3), 5000);

    /* â”€â”€ Gallery Lightbox â”€â”€ */
    function openLightbox(el) {
        const src = el.querySelector('img').src;
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

    /* â”€â”€ FAQ Accordion â”€â”€ */
    function toggleFaq(btn) {
        const body = btn.nextElementSibling;
        const icon = btn.querySelector('.faq-icon');
        const isOpen = body.classList.contains('open');
        // close all
        document.querySelectorAll('.faq-body').forEach(b => b.classList.remove('open'));
        document.querySelectorAll('.faq-icon').forEach(i => i.style.transform = '');
        // open clicked if it was closed
        if (!isOpen) {
            body.classList.add('open');
            icon.style.transform = 'rotate(180deg)';
        }
    }

    /* â”€â”€ Contact Form (demo) â”€â”€ */
    async function submitForm(event) {
        event.preventDefault();

        const form = document.getElementById('admissionForm');
        const button = document.getElementById('submitEnquiryButton');
        const successBox = document.getElementById('formSuccess');
        const errorBox = document.getElementById('formError');
        const whatsappButton = document.getElementById('whatsappFollowUp');

        const payload = {
            parent_name: document.getElementById('parentName').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            email: document.getElementById('email').value.trim(),
            student_name: document.getElementById('studentName').value.trim(),
            class_level: document.getElementById('classLevel').value,
            message: document.getElementById('message').value.trim(),
        };

        if (!payload.parent_name || !payload.phone || !payload.student_name || !payload.class_level) {
            errorBox.textContent = 'Please fill in all required fields (*).';
            errorBox.classList.remove('hidden');
            successBox.classList.add('hidden');
            return;
        }

        errorBox.classList.add('hidden');
        successBox.classList.add('hidden');
        whatsappButton.classList.add('hidden');
        form.style.opacity = '0.6';
        form.style.pointerEvents = 'none';
        button.textContent = 'Sending...';

        try {
            const response = await fetch("{{ route('admission-enquiries.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    const firstError = Object.values(data.errors).flat()[0];
                    throw new Error(firstError || 'Unable to submit your enquiry right now.');
                }

                throw new Error(data.message || 'Unable to submit your enquiry right now.');
            }

            successBox.textContent = data.message;
            successBox.classList.remove('hidden');
            whatsappButton.href = data.whatsapp_url;
            whatsappButton.classList.remove('hidden');
            form.reset();
        } catch (error) {
            errorBox.textContent = error.message || 'Something went wrong while sending your enquiry.';
            errorBox.classList.remove('hidden');
        } finally {
            form.style.opacity = '1';
            form.style.pointerEvents = 'auto';
            button.textContent = 'Send Enquiry';
            window.scrollTo({ top: document.getElementById('contact').offsetTop - 80, behavior: 'smooth' });
        }
    }

</script>
</body>
</html>


