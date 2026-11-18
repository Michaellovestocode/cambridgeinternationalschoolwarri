@extends('layouts.app')
@section('title', 'Exam Results')
@section('content')
<main class="mx-auto max-w-5xl space-y-5">
    <header class="rounded-2xl bg-gradient-to-r from-emerald-700 to-cyan-700 p-5 text-white shadow-lg sm:p-7"><a href="{{ route('student.dashboard') }}" class="text-sm font-bold text-white/80">← Learner home</a><h1 class="mt-3 text-2xl font-black sm:text-3xl">Exam results</h1><p class="mt-1 text-sm text-white/80">Your submitted and marked exam attempts.</p></header>
    <section class="space-y-3">@forelse($completedAttempts as $attempt)<article class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"><div><h2 class="font-black text-gray-900">{{ $attempt->exam->title }}</h2><p class="mt-1 text-sm text-gray-500">{{ $attempt->submitted_at?->format('j M Y, g:i A') ?? $attempt->updated_at->format('j M Y, g:i A') }}</p><p class="mt-1 text-sm font-bold {{ $attempt->status === 'graded' ? 'text-emerald-700' : 'text-amber-700' }}">{{ ucfirst($attempt->status) }}@if($attempt->status === 'graded' && $attempt->exam->show_results_to_students) · {{ $attempt->total_score }}/{{ $attempt->exam->total_marks }}@endif</p></div><a href="{{ route('student.view-result', $attempt) }}" class="rounded-xl bg-emerald-700 px-4 py-3 text-center text-sm font-bold text-white">View result</a></article>@empty<div class="rounded-2xl bg-white p-8 text-center text-gray-500 shadow-sm">Your completed exams will appear here.</div>@endforelse</section>
</main>
@endsection
