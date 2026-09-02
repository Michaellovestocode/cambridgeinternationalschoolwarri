@extends('layouts.app')

@section('title', 'Assessment Studio')

@push('styles')
<style>
    @media (max-width: 767px) {
        .assessment-studio-shell {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .assessment-hero {
            border-radius: 1.25rem;
        }

        .assessment-hero .assessment-hero-content {
            padding: 1.25rem;
        }

        .assessment-grid,
        .assessment-builder-grid,
        .assessment-lower-grid {
            grid-template-columns: 1fr;
        }

        .assessment-card,
        .assessment-option-card,
        .assessment-panel {
            border-radius: 1.25rem;
        }

        .assessment-card a,
        .assessment-option-card a,
        .assessment-option-card button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="assessment-studio-shell max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div class="assessment-hero mb-6 overflow-hidden rounded-[2rem] border border-sky-100 bg-gradient-to-r from-sky-50 via-white to-indigo-50 shadow-[0_20px_60px_-25px_rgba(14,116,144,0.25)]">
        <div class="assessment-hero-content flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between lg:p-8">
            <div>
                <span class="inline-flex items-center rounded-full border border-sky-200 bg-sky-100 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-sky-700">
                    Teacher workspace
                </span>
                <h1 class="mt-3 text-2xl font-black text-slate-900 sm:text-3xl lg:text-4xl">Assessment Studio</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600 sm:text-base">
                    Create classroom assessments for classwork, assignments, quizzes, and tests while keeping official school examinations separate.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-100">
                    <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Today</div>
                    <div class="mt-1 text-xl font-black text-slate-800">4 tasks</div>
                </div>
                <a href="{{ route('teacher.scores.dashboard') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-800">
                    View score dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="assessment-grid grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-3">
        <div class="assessment-card group rounded-[1.75rem] border border-sky-100 bg-gradient-to-br from-sky-500 via-cyan-500 to-sky-700 p-5 text-white shadow-[0_18px_40px_-20px_rgba(14,116,144,0.7)] transition hover:-translate-y-1">
            <div class="mb-4 flex items-center justify-between">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-2xl shadow-inner">✍️</span>
                <span class="rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-sky-50">Classwork</span>
            </div>
            <h2 class="text-xl font-black">Classwork</h2>
            <p class="mt-2 text-sm text-sky-50/90">Short classroom exercises for quick checks, recap, and immediate reinforcement.</p>
            <a href="{{ route('admin.learning-sessions.create', ['assessment_type' => 'classwork']) }}" class="mt-5 block w-full rounded-2xl bg-white/15 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-white/20">
                Create classwork
            </a>
        </div>

        <div class="group rounded-[1.75rem] border border-violet-100 bg-gradient-to-br from-violet-500 via-indigo-500 to-indigo-700 p-5 text-white shadow-[0_18px_40px_-20px_rgba(79,70,229,0.7)] transition hover:-translate-y-1">
            <div class="mb-4 flex items-center justify-between">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-2xl shadow-inner">📝</span>
                <span class="rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-violet-50">Assignment</span>
            </div>
            <h2 class="text-xl font-black">Assignment</h2>
            <p class="mt-2 text-sm text-violet-50/90">Take-home, project-based, or written tasks with deadlines and teacher feedback.</p>
            <a href="{{ route('admin.learning-sessions.create', ['assessment_type' => 'assignment']) }}" class="mt-5 block w-full rounded-2xl bg-white/15 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-white/20">
                Create assignment
            </a>
        </div>

        <div class="group rounded-[1.75rem] border border-emerald-100 bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-700 p-5 text-white shadow-[0_18px_40px_-20px_rgba(13,148,136,0.7)] transition hover:-translate-y-1">
            <div class="mb-4 flex items-center justify-between">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-2xl shadow-inner">❓</span>
                <span class="rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-emerald-50">Quiz</span>
            </div>
            <h2 class="text-xl font-black">Quiz</h2>
            <p class="mt-2 text-sm text-emerald-50/90">Objective or mixed-format checks for revision, practice, and instant feedback.</p>
            <a href="{{ route('admin.learning-sessions.create', ['assessment_type' => 'quiz']) }}" class="mt-5 block w-full rounded-2xl bg-white/15 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-white/20">
                Create quiz
            </a>
        </div>
    </div>

    <div class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-4 shadow-[0_15px_35px_-25px_rgba(15,23,42,0.35)] sm:p-6 lg:p-7">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-900">Assessment Builder</h2>
                <p class="text-sm text-slate-600">Choose the format that best matches your lesson objective.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.15em] text-slate-600">
                built for classroom use
            </span>
        </div>

        <div class="assessment-builder-grid mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="assessment-option-card rounded-[1.4rem] border border-cyan-200 bg-cyan-50 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-lg font-black text-cyan-800">Objective</span>
                    <span class="text-2xl">A–D</span>
                </div>
                <p class="text-sm text-cyan-700">Short, multiple-choice prompts with fast marking and immediate feedback.</p>
                <a href="{{ route('admin.learning-sessions.create', ['assessment_type' => 'quiz', 'assessment_format' => 'objective']) }}" class="mt-4 block w-full rounded-xl bg-cyan-600 px-3 py-2.5 text-center text-sm font-bold text-white transition hover:bg-cyan-700">Use objective</a>
            </div>

            <div class="assessment-option-card rounded-[1.4rem] border border-violet-200 bg-violet-50 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-lg font-black text-violet-800">Theory</span>
                    <span class="text-2xl">✍️</span>
                </div>
                <p class="text-sm text-violet-700">Essay, structured, and written responses for deeper thinking and expression.</p>
                <a href="{{ route('admin.learning-sessions.create', ['assessment_type' => 'assignment', 'assessment_format' => 'theory']) }}" class="mt-4 block w-full rounded-xl bg-violet-600 px-3 py-2.5 text-center text-sm font-bold text-white transition hover:bg-violet-700">Use theory</a>
            </div>

            <div class="assessment-option-card rounded-[1.4rem] border border-emerald-200 bg-emerald-50 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-lg font-black text-emerald-800">Mixed</span>
                    <span class="text-2xl">⚖️</span>
                </div>
                <p class="text-sm text-emerald-700">Combine objective sections with theory questions in one complete task.</p>
                <a href="{{ route('admin.learning-sessions.create', ['assessment_type' => 'test', 'assessment_format' => 'mixed']) }}" class="mt-4 block w-full rounded-xl bg-emerald-600 px-3 py-2.5 text-center text-sm font-bold text-white transition hover:bg-emerald-700">Use mixed</a>
            </div>

            <div class="assessment-option-card rounded-[1.4rem] border border-amber-200 bg-amber-50 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-lg font-black text-amber-800">Official exam</span>
                    <span class="text-2xl">🧾</span>
                </div>
                <p class="text-sm text-amber-700">Formal school exam flow remains separate for administrative and section-level use.</p>
                <a href="{{ route('admin.exam.create') }}" class="mt-4 block w-full rounded-xl bg-amber-600 px-3 py-2.5 text-center text-sm font-bold text-white transition hover:bg-amber-700">Open official exam</a>
            </div>
        </div>
    </div>

    <div class="assessment-lower-grid mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="assessment-panel rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-[0_15px_35px_-25px_rgba(15,23,42,0.35)] sm:p-6">
            <h3 class="text-lg font-black text-slate-900">Recommended flow</h3>
            <ul class="mt-4 space-y-3 text-sm text-slate-700">
                <li class="flex items-start gap-3"><span class="mt-0.5 text-cyan-600">•</span><span>Classwork for quick checks during lesson delivery.</span></li>
                <li class="flex items-start gap-3"><span class="mt-0.5 text-violet-600">•</span><span>Assignments for take-home learning and deeper understanding.</span></li>
                <li class="flex items-start gap-3"><span class="mt-0.5 text-emerald-600">•</span><span>Quizzes for revision and objective practice before formal testing.</span></li>
                <li class="flex items-start gap-3"><span class="mt-0.5 text-amber-600">•</span><span>Official exams stay under the school exam module, not the classroom studio.</span></li>
            </ul>
        </div>

        <div class="assessment-panel rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-[0_15px_35px_-25px_rgba(15,23,42,0.35)] sm:p-6">
            <h3 class="text-lg font-black text-slate-900">Suggested metadata</h3>
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm text-slate-700">
                <div class="rounded-2xl bg-slate-50 p-3"><strong class="text-slate-900">Subject:</strong> English, Maths, Biology</div>
                <div class="rounded-2xl bg-slate-50 p-3"><strong class="text-slate-900">Class:</strong> JSS 1, SS 2</div>
                <div class="rounded-2xl bg-slate-50 p-3"><strong class="text-slate-900">Type:</strong> Quiz / Assignment / Test</div>
                <div class="rounded-2xl bg-slate-50 p-3"><strong class="text-slate-900">Format:</strong> Objective / Theory / Mixed</div>
                <div class="rounded-2xl bg-slate-50 p-3"><strong class="text-slate-900">Deadline:</strong> 24 hours or 7 days</div>
                <div class="rounded-2xl bg-slate-50 p-3"><strong class="text-slate-900">Marks:</strong> 10, 20, 50, 100</div>
            </div>
        </div>
    </div>
</div>
@endsection
