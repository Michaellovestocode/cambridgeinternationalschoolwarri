@extends('layouts.app')
@section('title', 'Exams')
@section('content')
<main class="mx-auto max-w-6xl space-y-6">
    <header class="rounded-2xl bg-gradient-to-r from-blue-700 to-violet-700 p-5 text-white shadow-lg sm:p-7"><a href="{{ route('student.dashboard') }}" class="text-sm font-bold text-white/80">← Learner home</a><h1 class="mt-3 text-2xl font-black sm:text-3xl">Exams</h1><p class="mt-1 text-sm text-white/80">Continue an exam or see what is available for your class.</p></header>
    @if($inProgressAttempts->isNotEmpty())
        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5"><h2 class="text-lg font-black text-amber-900">Continue an exam</h2><div class="mt-3 grid gap-3 sm:grid-cols-2">@foreach($inProgressAttempts as $attempt)<a href="{{ route('student.take-exam', $attempt) }}" class="rounded-xl bg-white p-4 font-bold text-gray-900 shadow-sm">{{ $attempt->exam->title }}<span class="mt-2 block text-sm font-semibold text-amber-800">Continue exam →</span></a>@endforeach</div></section>
    @endif
    <section class="space-y-3"><h2 class="text-xl font-black text-gray-900">Available exams</h2>@forelse($availableExams as $exam)<article class="rounded-2xl bg-white p-5 shadow-sm"><div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><h3 class="text-lg font-black text-gray-900">{{ $exam->title }}</h3><p class="mt-1 text-sm text-gray-600">{{ $exam->subject }} · {{ $exam->duration_minutes }} minutes · {{ $exam->questions->count() }} questions</p><p class="mt-1 text-sm font-semibold text-blue-700">Available until {{ $exam->end_date->format('j M Y, g:i A') }}</p></div><a href="{{ route('student.start-exam', $exam) }}" class="rounded-xl bg-blue-700 px-5 py-3 text-center font-bold text-white">Start exam</a></div></article>@empty<div class="rounded-2xl bg-white p-8 text-center text-gray-500 shadow-sm">No exams are available right now.</div>@endforelse</section>
</main>
@endsection
