@extends('layouts.app')
@section('title', 'Developmental Reports')
@section('content')
<main class="mx-auto max-w-5xl space-y-5">
    <header class="rounded-2xl bg-gradient-to-r from-teal-700 to-emerald-700 p-5 text-white shadow-lg sm:p-7"><a href="{{ route('student.dashboard') }}" class="text-sm font-bold text-white/80">← Learner home</a><h1 class="mt-3 text-2xl font-black sm:text-3xl">Developmental reports</h1><p class="mt-1 text-sm text-white/80">View reports published by your teachers.</p></header>
    <section class="space-y-3">@forelse($developmentalReports as $report)<article class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"><div><h2 class="font-black text-gray-900">{{ $report->session?->name ?? 'Session' }} · {{ $report->term?->name ?? 'Term' }}</h2><p class="mt-1 text-sm text-gray-500">{{ $report->class?->display_name ?? 'Class' }}</p></div><div class="flex flex-wrap gap-2"><a href="{{ route('student.developmental-reports.preview', $report) }}" class="rounded-xl bg-emerald-700 px-4 py-3 text-center text-sm font-bold text-white">View report</a><a href="{{ route('student.developmental-reports.download', $report) }}" class="rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-bold text-gray-800">Download</a></div></article>@empty<div class="rounded-2xl bg-white p-8 text-center text-sm text-gray-500 shadow-sm">No published developmental reports are available.</div>@endforelse</section>
</main>
@endsection
