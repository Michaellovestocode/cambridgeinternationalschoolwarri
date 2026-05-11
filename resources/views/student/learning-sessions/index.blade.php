@extends('layouts.app')

@section('title', 'Learning Sessions')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-cyan-600 to-emerald-600 text-white rounded-2xl shadow-lg p-8">
        <h1 class="text-3xl font-bold">Learning Sessions</h1>
        <p class="text-cyan-50 mt-2">Study a topic, answer short practice questions, then review corrections immediately.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($sessions as $session)
        @php($attempt = $latestAttempts->get($session->id))
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 flex flex-col">
            <div class="flex-1">
                <div class="flex justify-between items-start gap-3 mb-4">
                    <span class="bg-cyan-100 text-cyan-800 text-xs font-bold px-3 py-1 rounded-full">{{ $session->subject->name ?? 'Subject' }}</span>
                    <span class="text-xs text-gray-500">{{ $session->estimated_minutes }} mins</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $session->title }}</h2>
                <p class="text-sm font-semibold text-emerald-700 mb-3">{{ $session->schoolClass->display_name ?? 'Your class' }} • {{ $session->topic }}</p>
                <p class="text-gray-600 text-sm leading-6">{{ Str::limit($session->description, 130) }}</p>

                <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <div class="text-gray-500">Questions</div>
                        <div class="font-bold text-gray-900">{{ $session->questions_count }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <div class="text-gray-500">Last Score</div>
                        <div class="font-bold text-gray-900">{{ $attempt ? $attempt->score . '/' . $attempt->total_questions : 'New' }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <a href="{{ route('student.learning.show', $session) }}" class="flex-1 text-center bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-3 rounded-xl font-bold">
                    Start Session
                </a>
                @if($attempt)
                    <a href="{{ route('student.learning.result', $attempt) }}" class="px-4 py-3 rounded-xl font-bold bg-gray-100 hover:bg-gray-200 text-gray-800">Review</a>
                @endif
            </div>
        </div>
        @empty
        <div class="md:col-span-2 xl:col-span-3 bg-white rounded-2xl shadow p-12 text-center">
            <h2 class="text-2xl font-bold text-gray-800">No learning sessions yet</h2>
            <p class="text-gray-500 mt-2">Your teachers will publish sessions here when they are ready.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
