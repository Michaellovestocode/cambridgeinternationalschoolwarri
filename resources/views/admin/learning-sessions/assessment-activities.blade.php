@extends('layouts.app')

@section('title', 'My Classroom Activities')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-cyan-700">Teacher Workspace</p>
            <h1 class="mt-1 text-2xl font-black text-gray-900">My Classroom Activities</h1>
            <p class="mt-1 text-sm text-gray-600">Open, edit, and monitor the classwork, assignments, quizzes, and tests you have created.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('teacher.assessment-studio') }}" class="rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-700">Assessment Studio</a>
            <a href="{{ route('admin.learning-sessions.create') }}" class="rounded-xl bg-cyan-600 px-4 py-3 text-center text-sm font-bold text-white">Create Activity</a>
        </div>
    </div>

    <div class="grid gap-4">
        @forelse($sessions as $session)
            <div class="rounded-2xl bg-white p-5 shadow">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-bold uppercase text-cyan-800">{{ $session->assessment_type }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase text-slate-700">{{ $session->assessment_format }}</span>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $session->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $session->is_published ? 'Published' : 'Draft' }}</span>
                        </div>
                        <h2 class="mt-3 truncate text-xl font-black text-gray-900">{{ $session->title }}</h2>
                        <p class="mt-1 text-sm font-semibold text-emerald-700">{{ $session->targetClasses->pluck('display_name')->join(', ') ?: ($session->schoolClass->display_name ?? 'Class') }} · {{ $session->subject->name ?? 'Subject' }} · {{ $session->topic }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-center text-sm sm:min-w-[240px]">
                        <div class="rounded-xl bg-gray-50 p-3"><strong class="block text-lg text-gray-900">{{ $session->questions_count }}</strong><span class="text-gray-500">Questions</span></div>
                        <div class="rounded-xl bg-gray-50 p-3"><strong class="block text-lg text-gray-900">{{ $session->estimated_minutes }}</strong><span class="text-gray-500">Minutes</span></div>
                    </div>
                    <div class="flex flex-col gap-2 sm:min-w-[150px]">
                        <a href="{{ route('admin.learning-sessions.edit', $session) }}" class="rounded-xl bg-cyan-600 px-5 py-3 text-center text-sm font-bold text-white">Open / Edit</a>
                        @if(! $session->is_published)
                            <form action="{{ route('admin.learning-sessions.publish', $session) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-5 py-3 text-center text-sm font-bold text-white">Publish for Students</button>
                            </form>
                        @else
                            <span class="rounded-xl bg-emerald-100 px-5 py-3 text-center text-sm font-bold text-emerald-800">Published</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-white p-10 text-center shadow">
                <h2 class="text-xl font-bold text-gray-900">No classroom activities yet</h2>
                <p class="mt-2 text-sm text-gray-500">Create your first classwork, assignment, quiz, or test from Assessment Studio.</p>
                <a href="{{ route('teacher.assessment-studio') }}" class="mt-5 inline-block rounded-xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white">Open Assessment Studio</a>
            </div>
        @endforelse
    </div>

    @if($sessions->hasPages())
        <div>{{ $sessions->links() }}</div>
    @endif
</div>
@endsection
