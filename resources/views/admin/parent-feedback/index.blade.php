@extends('layouts.app')

@section('title', 'Parent Feedback')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <header class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-blue-900 to-teal-800 text-white shadow-xl">
        <div class="flex flex-col gap-5 p-6 sm:p-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[.2em] text-blue-200">Parent relations</p>
                <h1 class="mt-2 text-3xl font-black sm:text-4xl">Feedback &amp; Suggestions</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-blue-100">Review parent concerns and ideas, record internal follow-up notes, and track each submission through resolution.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-white/25 bg-white/10 px-4 py-3 text-center text-sm font-bold text-white hover:bg-white/20">Admin dashboard</a>
        </div>
    </header>

    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif

    <section class="grid gap-4 sm:grid-cols-3">
        @foreach([
            ['label' => 'New submissions', 'value' => $summary['new'], 'tone' => 'bg-blue-50 text-blue-900'],
            ['label' => 'Under review', 'value' => $summary['under_review'], 'tone' => 'bg-amber-50 text-amber-900'],
            ['label' => 'Resolved', 'value' => $summary['resolved'], 'tone' => 'bg-emerald-50 text-emerald-900'],
        ] as $stat)
            <div class="rounded-2xl {{ $stat['tone'] }} p-5 shadow-sm ring-1 ring-black/5"><p class="text-sm font-bold opacity-75">{{ $stat['label'] }}</p><p class="mt-2 text-3xl font-black">{{ number_format($stat['value']) }}</p></div>
        @endforeach
    </section>

    <form method="GET" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm lg:grid-cols-[minmax(0,1fr)_auto_auto_auto]">
        <label class="sr-only" for="feedback-search">Search feedback</label>
        <input id="feedback-search" name="search" value="{{ $filters['search'] }}" class="min-w-0 rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Search reference, parent, contact, or message">
        <label class="sr-only" for="feedback-category">Category</label>
        <select id="feedback-category" name="category" class="rounded-xl border border-slate-200 px-4 py-3 text-sm"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category }}" @selected($filters['category'] === $category)>{{ ucfirst($category) }}</option>@endforeach</select>
        <label class="sr-only" for="feedback-status">Status</label>
        <select id="feedback-status" name="status" class="rounded-xl border border-slate-200 px-4 py-3 text-sm"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select>
        <div class="flex gap-2"><button class="flex-1 rounded-xl bg-blue-700 px-5 py-3 text-sm font-black text-white hover:bg-blue-800">Filter</button><a href="{{ route('admin.parent-feedback.index') }}" class="rounded-xl bg-slate-100 px-4 py-3 text-center text-sm font-bold text-slate-700">Reset</a></div>
    </form>

    <section class="space-y-3">
        @forelse($feedback as $item)
            @php
                $statusTone = match($item->status) {
                    'resolved' => 'bg-emerald-100 text-emerald-800',
                    'under_review' => 'bg-amber-100 text-amber-800',
                    default => 'bg-blue-100 text-blue-800',
                };
                $categoryTone = match($item->category) {
                    'complaint' => 'bg-rose-50 text-rose-800',
                    'suggestion' => 'bg-violet-50 text-violet-800',
                    'compliment' => 'bg-teal-50 text-teal-800',
                    default => 'bg-slate-100 text-slate-700',
                };
            @endphp
            <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:shadow-md sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $categoryTone }}">{{ ucfirst($item->category) }}</span>
                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $statusTone }}">{{ ucfirst(str_replace('_', ' ', $item->status)) }}</span>
                            @if($item->is_anonymous)<span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Anonymous</span>@endif
                        </div>
                        <h2 class="mt-3 text-lg font-black text-slate-900">{{ $item->is_anonymous ? 'Anonymous parent' : $item->parent_name }}</h2>
                        <p class="mt-1 text-xs font-bold tracking-wide text-slate-400">{{ $item->reference_code }} <span class="font-normal">· {{ $item->created_at->format('j M Y, g:i A') }}</span></p>
                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ \Illuminate\Support\Str::limit($item->message, 260) }}</p>
                        @if($item->reviewedBy)<p class="mt-3 text-xs text-slate-400">Last handled by {{ $item->reviewedBy->name }}</p>@endif
                    </div>
                    <a href="{{ route('admin.parent-feedback.show', $item) }}" class="shrink-0 rounded-xl bg-slate-900 px-4 py-3 text-center text-sm font-bold text-white hover:bg-slate-700">Review submission</a>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm">
                <h2 class="text-lg font-black text-slate-900">No feedback submissions found</h2>
                <p class="mt-1 text-sm text-slate-500">Adjust the filters or check back when a parent submits feedback.</p>
            </div>
        @endforelse
    </section>

    <div>{{ $feedback->links() }}</div>
</div>
@endsection
