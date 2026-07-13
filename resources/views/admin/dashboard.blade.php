@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
    @media (max-width: 767px) {
        .admin-dashboard-shell {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .admin-dashboard-shell > * {
            margin-top: 0 !important;
        }

        .admin-mobile-form-banner { order: 0; }
        .admin-mobile-hero { order: 1; }
        .admin-mobile-actions { order: 2; }
        .admin-mobile-stats { order: 3; }
        .admin-mobile-attempts { order: 4; }
        .admin-mobile-class { order: 5; }

        .admin-mobile-hero {
            border-radius: 1.25rem;
        }

        .admin-mobile-hero .admin-hero-inner {
            padding: 1.25rem;
        }

        .admin-mobile-hero h1 {
            font-size: 1.75rem;
            line-height: 2.25rem;
        }

        .admin-date-card {
            width: 100%;
            padding: 1rem;
        }

        .admin-date-card .admin-date-day {
            font-size: 2.25rem;
            line-height: 2.5rem;
        }

        .admin-mobile-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .admin-stat-card {
            border-radius: 1.25rem;
            padding: 1rem;
            transform: none !important;
        }

        .admin-stat-card svg {
            width: 1.5rem;
            height: 1.5rem;
        }

        .admin-stat-number {
            font-size: 1.875rem;
            line-height: 2.25rem;
        }

        .admin-stat-title {
            font-size: .875rem;
            line-height: 1.25rem;
        }

        .admin-stat-secondary {
            display: none;
        }

        .admin-mobile-actions {
            display: block;
        }

        .admin-quick-card,
        .admin-section-card {
            border-radius: 1.25rem;
        }

        .admin-quick-card > div:first-child,
        .admin-section-card > div:first-child {
            padding: 1rem 1.125rem;
        }

        .admin-quick-list,
        .admin-section-body {
            padding: 1rem;
        }

        .admin-quick-list a {
            min-height: 4rem;
            padding: 1rem;
            border-radius: 1rem;
            transform: none !important;
        }

        .admin-quick-list a span:first-child {
            align-items: flex-start;
            line-height: 1.3;
        }

        .admin-mobile-recent-exams {
            margin-top: 1rem;
        }

        .admin-section-body {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-dashboard-shell space-y-6">
    @if(($birthdayLearners ?? collect())->isNotEmpty())
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-r from-amber-50 via-pink-50 to-blue-50 p-5 shadow-lg">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-black uppercase text-amber-700" style="letter-spacing:.14em;">
                        {{ $birthdayLearners->count() > 1 ? 'Birthdays Today' : 'Birthday Today' }}
                    </p>
                    <h2 class="mt-1 text-2xl font-black text-gray-900">
                        Celebrate {{ $birthdayLearners->count() > 1 ? 'our learners' : $birthdayLearners->first()->name }} today
                    </h2>
                    <p class="mt-1 text-sm font-semibold text-gray-600">
                        This reminder appears only on the learner's birthday.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @foreach($birthdayLearners as $learner)
                        <div class="flex min-w-0 items-center gap-3 rounded-2xl bg-white/85 px-4 py-3 shadow-sm ring-1 ring-white">
                            @if($learner->photo)
                                <img src="{{ asset('storage/' . $learner->photo) }}" alt="{{ $learner->name }}" class="h-12 w-12 rounded-2xl object-cover">
                            @else
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-lg font-black text-amber-700">
                                    {{ strtoupper(substr($learner->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="break-words text-sm font-black text-gray-900">{{ $learner->name }}</p>
                                <p class="text-xs font-semibold text-gray-500">
                                    {{ $learner->class?->display_name ?? 'No class' }}
                                    @if($learner->age)
                                        <span class="text-amber-700">| {{ $learner->age }} today</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Form Teacher Banner -->
    @if($isFormTeacher ?? false)
        <div class="admin-mobile-form-banner bg-gradient-to-r from-green-600 to-indigo-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-bold mb-2">Welcome, Form Teacher! 👨‍🏫</h3>
                <p class="text-white/90">You are assigned as a form teacher. Access score entry and report cards for your class from here.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 md:ml-4">
                    <a href="{{ route('admin.form-teacher.learners') }}"
                       class="bg-emerald-300 text-indigo-900 hover:bg-emerald-200 px-6 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all whitespace-nowrap">
                        My Learners
                    </a>
                    <a href="{{ route('teacher.scores.dashboard') }}" 
                       class="bg-white text-indigo-600 hover:bg-indigo-50 px-6 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all whitespace-nowrap">
                        📚 Score Entry
                    </a>
                    <a href="{{ route('admin.report-cards') }}" 
                       class="bg-yellow-300 text-indigo-900 hover:bg-yellow-200 px-6 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all whitespace-nowrap">
                        📄 Report Cards
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if(! $isFormTeacher)
    <!-- Welcome Header with Background -->
    <div class="admin-mobile-hero relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-3xl shadow-2xl overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl"></div>
        
        <div class="admin-hero-inner relative p-8 text-white">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-bold mb-2">Welcome, {{ Auth::user()->name }}! 👨‍💼</h1>
                    <p class="text-white/90 text-lg">Administrator Dashboard</p>
                    <p class="text-white/70 text-sm mt-2 italic">Cambridge International School - CBT System</p>
                </div>
                <div class="admin-date-card bg-white/20 backdrop-blur-md rounded-2xl p-6 border border-white/30">
                    <div class="text-center">
                        <div class="admin-date-day text-5xl font-bold">{{ \Carbon\Carbon::now()->format('d') }}</div>
                        <div class="text-sm mt-1">{{ \Carbon\Carbon::now()->format('F Y') }}</div>
                        <div class="text-xs mt-2 opacity-80">{{ \Carbon\Carbon::now()->format('l') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Stats -->
    <div class="admin-mobile-stats grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- Total Exams -->
        <div class="admin-stat-card bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="admin-stat-number text-4xl font-bold">{{ $examsCount }}</div>
                </div>
            </div>
            <div class="admin-stat-title text-white/90 font-semibold text-lg">Total Exams</div>
            <div class="text-white/70 text-sm mt-1">All exams in system</div>
        </div>

        @if(Auth::user()->isAdmin())
        <!-- Total Students -->
        <div class="admin-stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="admin-stat-number text-4xl font-bold">{{ $studentsCount }}</div>
                </div>
            </div>
            <div class="admin-stat-title text-white/90 font-semibold text-lg">{{ $isFormTeacher ? 'Class Students' : 'Total Students' }}</div>
            <div class="text-white/70 text-sm mt-1">
                {{ $isFormTeacher ? ($formTeacherAssignment?->schoolClass?->display_name ?? 'Assigned class') : 'Registered students' }}
            </div>
        </div>
        @endif

        <!-- Recent Attempts -->
        <div class="admin-stat-card admin-stat-secondary bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="admin-stat-number text-4xl font-bold">{{ $recentAttemptsCount ?? 0 }}</div>
                </div>
            </div>
            <div class="admin-stat-title text-white/90 font-semibold text-lg">Recent Attempts</div>
            <div class="text-white/70 text-sm mt-1">Latest submissions</div>
        </div>
        <div class="admin-stat-card admin-stat-secondary bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8m-2 10H5a2 2 0 01-2-2V8a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="admin-stat-number text-4xl font-bold">{{ $unreadMessagesCount }}</div>
                </div>
            </div>
            <div class="admin-stat-title text-white/90 font-semibold text-lg">Unread Messages</div>
            <div class="text-white/70 text-sm mt-1">Parents waiting for a response</div>
        </div>
        @if(auth()->user()->isAdmin())
        <div class="admin-stat-card admin-stat-secondary bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l5.447 5.447a2 2 0 002.828 0L21 3"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17h18M3 12h18"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="admin-stat-number text-4xl font-bold">{{ $newEnquiriesCount }}</div>
                </div>
            </div>
            <div class="admin-stat-title text-white/90 font-semibold text-lg">New Admissions</div>
            <p class="text-white/70 text-sm mt-1">Awaiting admissions review</p>
            <a href="{{ route('admin.enquiries.index', ['status' => \App\Models\AdmissionEnquiry::STATUS_NEW]) }}" class="mt-4 inline-flex items-center text-white text-sm font-semibold hover:underline">
                View inbox
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        @endif
    </div>

    <!-- Quick Actions & Recent Exams -->
    <div class="admin-mobile-actions grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Quick Actions -->
        <div class="admin-quick-card bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-blue-50 px-6 py-4 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <span class="mr-2">⚡</span> Quick Actions
                </h3>
            </div>
            <div class="admin-quick-list p-6 space-y-3">
                <a href="{{ route('admin.exam.create') }}" 
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

                <a href="{{ route('admin.exams') }}" 
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

                <a href="{{ route('admin.results.index') }}" 
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

                @if($canUsePaperScores ?? false)
                <a href="{{ route('teacher.scores.select', ['score_source' => 'paper']) }}"
                   class="flex items-center justify-between bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"></path>
                        </svg>
                        Fill Paper Scores
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @endif

                @if(auth()->user()->isTeacher())
                <a href="{{ route('admin.subjects.my') }}"
                   class="flex items-center justify-between bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        My Assigned Subjects
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="bg-white/20 text-white text-xs px-2 py-1 rounded-full">{{ $assignedSubjectCount ?? auth()->user()->subjects()->count() }} subjects</span>
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                </a>

                <a href="{{ route('admin.teaching-learners.index') }}"
                   class="flex items-center justify-between bg-gradient-to-r from-emerald-500 to-cyan-600 hover:from-emerald-600 hover:to-cyan-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 11-8 0 4 4 0 018 0zm8 0a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Teaching Learners
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="bg-white/20 text-white text-xs px-2 py-1 rounded-full">{{ ($teachingClasses ?? collect())->count() }} classes</span>
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                </a>
                @endif

                @if($isFormTeacher && in_array($formTeacherAssignment?->schoolClass?->section_key, ['creche', 'other'], true))
                <a href="{{ route('admin.developmental-reports.index') }}"
                   class="flex items-center justify-between bg-gradient-to-r from-teal-500 to-cyan-600 hover:from-teal-600 hover:to-cyan-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M8 4h8a2 2 0 012 2v14l-4-2-4 2-4-2V6a2 2 0 012-2z"></path>
                        </svg>
                        Fill Developmental Reports
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                @if($hasFormTeacherClassScoreEntry)
                <a href="{{ route('admin.report-cards.class-score-entry') }}"
                   class="flex items-center justify-between bg-gradient-to-r from-pink-500 to-indigo-600 hover:from-pink-600 hover:to-indigo-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M4 20h4.586a1 1 0 00.707-.293l9.414-9.414a2 2 0 00-2.828-2.828L6.465 16.879a1 1 0 00-.293.707V20z"></path>
                        </svg>
                        Fill Class Scores
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @endif

                <a href="{{ route('admin.form-teacher.learners') }}"
                   class="flex items-center justify-between bg-gradient-to-r from-lime-500 to-emerald-600 hover:from-lime-600 hover:to-emerald-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 14a4 4 0 10-8 0M4 20a8 8 0 0116 0M12 10a4 4 0 100-8 4 4 0 000 8z"></path>
                        </svg>
                        My Class Students
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="bg-white/20 text-white text-xs px-2 py-1 rounded-full">{{ $classStudents->count() }} students</span>
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                </a>

                <a href="{{ route('teacher.scores.class-rankings') }}"
                   class="flex items-center justify-between bg-gradient-to-r from-amber-500 to-yellow-600 hover:from-amber-600 hover:to-yellow-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17l4 4 4-4m-4 4V3M4 7h4m8 0h4M4 12h3m10 0h3"></path>
                        </svg>
                        Class Rankings
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @endif

                @if(auth()->user()->isAdmin() || $isFormTeacher)
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.developmental-reports.index') }}"
                   class="flex items-center justify-between bg-gradient-to-r from-teal-500 to-cyan-600 hover:from-teal-600 hover:to-cyan-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M8 4h8a2 2 0 012 2v14l-4-2-4 2-4-2V6a2 2 0 012-2z"></path>
                        </svg>
                        Developmental Reports
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @endif

                <a href="{{ route('admin.report-cards') }}" 
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
                @endif

                <a href="{{ route('admin.messages.index') }}"
                   class="flex items-center justify-between bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8m-2 10H5a2 2 0 01-2-2V8a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z"></path>
                        </svg>
                        Messages
                    </span>
                    <span class="inline-flex items-center gap-2">
                        @if($unreadMessagesCount > 0)
                            <span class="bg-white/20 text-white text-xs px-2 py-1 rounded-full">{{ $unreadMessagesCount }}</span>
                        @endif
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                </a>

                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.enquiries.index') }}"
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

                <a href="{{ route('admin.admission-form-payments.index') }}"
                   class="flex items-center justify-between bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 7v12h14V7M8 11h8"></path>
                        </svg>
                        Form Payment Requests
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                @if(auth()->user()->canManageAttendance())
                <a href="{{ route('admin.attendance.scanner') }}"
                   class="flex items-center justify-between bg-gradient-to-r from-teal-500 to-cyan-600 hover:from-teal-600 hover:to-cyan-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V5a1 1 0 011-1h2M4 17v2a1 1 0 001 1h2M17 4h2a1 1 0 011 1v2M17 20h2a1 1 0 001-1v-2M8 12h8M8 9h8M8 15h5"></path>
                        </svg>
                        Attendance Scanner
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @endif

                <a href="{{ route('admin.announcements.index') }}"
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

                <a href="{{ route('admin.blog.index') }}"
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

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.gallery.index') }}"
                       class="flex items-center justify-between bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.5-4.5a2 2 0 012.8 0L16 16"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 14l1.5-1.5a2 2 0 012.8 0L20 14"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path>
                            </svg>
                            Gallery Manager
                        </span>
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                @endif

                <a href="{{ route('admin.students') }}" 
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

                <a href="{{ route('admin.teachers') }}" 
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

                <a href="{{ route('admin.teachers') }}" 
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

                <a href="{{ route('admin.parents.index') }}"
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

                <a href="{{ route('admin.form-teachers.index') }}" 
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

                <a href="{{ route('admin.classes') }}" 
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

                <a href="{{ route('admin.classes') }}"
                   class="flex items-center justify-between bg-gradient-to-r from-amber-500 to-lime-600 hover:from-amber-600 hover:to-lime-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M7 14h10M9 18h6"></path>
                        </svg>
                        Assign Subjects to Classes
                    </span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="{{ route('admin.blog-managers.index') }}"
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

                 <a href="{{ route('admin.subjects.index') }}" 
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
                 
                 <a href="{{ route('admin.academic-sessions.index') }}" 
                    class="flex items-center justify-between bg-gradient-to-r from-cyan-500 to-teal-600 hover:from-cyan-600 hover:to-teal-700 text-white px-6 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all group">
                     <span class="flex items-center">
                         <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                         </svg>
                         Manage Sessions & Terms
                     </span>
                     <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                     </svg>
                 </a>
                 @endif
             </div>
        </div>

        <!-- Recent Exams -->
        <div class="admin-mobile-recent-exams admin-section-card bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <span class="mr-2">📚</span> Recent Exams
                </h3>
            </div>
            <div class="admin-section-body p-6">
                @forelse($recentExams as $exam)
                <div class="border-l-4 border-green-500 bg-green-50 rounded-r-xl pl-4 py-3 mb-3 hover:bg-green-100 transition-colors">
                    <p class="font-bold text-gray-800">{{ $exam->title }}</p>
                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-semibold">
                            {{ $exam->subject }}
                        </span>
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full font-semibold">
                            📝 {{ $exam->questions->count() }} questions
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <div class="text-5xl mb-3">📭</div>
                    <p class="text-gray-500">No exams yet</p>
                    <a href="{{ route('admin.exam.create') }}" class="text-green-600 hover:text-green-700 font-semibold text-sm mt-2 inline-block">
                        Create your first exam →
                    </a>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Student Attempts Grouped by Class -->
    <div class="admin-mobile-attempts admin-section-card bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-4 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <span class="mr-2">📊</span> Recent Student Attempts by Class
            </h3>
        </div>
        <div class="admin-section-body p-6">
            @if($groupedAttempts->isNotEmpty())
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($groupedAttempts as $classGroup)
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3">
                            <h4 class="text-white font-bold text-lg flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                {{ $classGroup['class_name'] }}
                                <span class="ml-auto bg-white/20 text-white text-sm px-2 py-1 rounded-full">
                                    {{ $classGroup['attempts_count'] }}
                                </span>
                            </h4>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-3 text-center">
                                <div class="rounded-xl bg-white p-3 shadow-sm">
                                    <p class="text-2xl font-black text-yellow-700">{{ $classGroup['pending_count'] }}</p>
                                    <p class="text-xs font-semibold text-gray-500">Pending</p>
                                </div>
                                <div class="rounded-xl bg-white p-3 shadow-sm">
                                    <p class="text-2xl font-black text-green-700">{{ $classGroup['graded_count'] }}</p>
                                    <p class="text-xs font-semibold text-gray-500">Graded</p>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-gray-500">
                                Latest attempt:
                                {{ $classGroup['latest_attempt_at'] ? \Carbon\Carbon::parse($classGroup['latest_attempt_at'])->format('d M, H:i') : 'N/A' }}
                            </p>
                            @if($classGroup['class'])
                                <a href="{{ route('admin.attempts.class', $classGroup['class']) }}"
                                   class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white hover:bg-blue-700">
                                    View Class Attempts
                                </a>
                            @else
                                <div class="mt-4 rounded-xl bg-gray-200 px-4 py-3 text-center text-sm font-bold text-gray-500">
                                    No class assigned
                                </div>
                            @endif
                            {{--
                            <div class="hidden">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-800 text-sm">{{ $attempt->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $attempt->user->registration_number }}</div>
                                    </div>
                                    <div class="text-right">
                                        @if($attempt->status === 'graded')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full font-semibold">✓ Graded</span>
                                        @elseif($attempt->status === 'submitted')
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full font-semibold">⏳ Pending</span>
                                        @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full font-semibold">⚪ In Progress</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-xs text-gray-600 mb-2">
                                    <div class="font-medium">{{ $attempt->exam->title }}</div>
                                    <div class="text-gray-500">{{ $attempt->exam->subject }}</div>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <div>
                                        @if($attempt->total_score !== null)
                                        <span class="font-bold text-gray-800">{{ $attempt->total_score }}</span>
                                        <span class="text-gray-500">marks</span>
                                        @else
                                        <span class="text-gray-400">-</span>
                                        @endif
                                    </div>
                                    <div class="text-gray-500">
                                        {{ $attempt->created_at->format('d M') }}
                                        <div class="text-gray-400">{{ $attempt->created_at->format('H:i') }}</div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    @if($attempt->status === 'submitted')
                                    <a href="{{ route('admin.attempt.grade', $attempt->id) }}" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-semibold inline-flex items-center w-full justify-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Grade
                                    </a>
                                    @else
                                    <a href="{{ route('admin.exam.results', $attempt->exam_id) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-semibold text-xs text-center block">
                                        View Results →
                                    </a>
                                    @endif
                                </div>
                            </div>
                            --}}
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-5xl mb-3">📝</div>
                    <p class="text-gray-500 text-lg">No attempts yet</p>
                    <p class="text-gray-400 text-sm mt-1">Student submissions will appear here</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
