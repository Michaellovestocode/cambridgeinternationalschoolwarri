@extends('layouts.blog-admin')

@section('title', 'Blog Moderation')
@section('page_heading', 'Moderation Queue')

@section('content')
@php
    $statusStyles = [
        \App\Models\BlogPost::STATUS_PENDING => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'ring-amber-200', 'label' => 'Needs Review'],
        \App\Models\BlogPost::STATUS_PUBLISHED => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-200', 'label' => 'Published'],
        \App\Models\BlogPost::STATUS_DRAFT => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'ring' => 'ring-slate-200', 'label' => 'Drafts'],
        \App\Models\BlogPost::STATUS_REJECTED => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'ring' => 'ring-rose-200', 'label' => 'Needs Revision'],
    ];
    $pendingCount = $statusCounts[\App\Models\BlogPost::STATUS_PENDING] ?? 0;
@endphp

<div class="space-y-8">
    <section class="overflow-hidden rounded-[2rem] bg-slate-950 text-white shadow-2xl">
        <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
            <div class="p-6 sm:p-8 lg:p-10">
                <div class="inline-flex rounded-full border border-white/10 bg-white/10 px-4 py-2 text-xs font-black uppercase text-amber-300" style="letter-spacing:.14em;">Blog Studio</div>
                <h1 class="mt-5 max-w-3xl text-4xl font-black leading-tight sm:text-5xl">Publish stories with confidence.</h1>
                <p class="mt-4 max-w-2xl text-base leading-8 text-white/70">Review teacher submissions, polish article copy, manage cover images, and decide what appears on the public Cambridge blog.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('admin.blog.create') }}" class="rounded-2xl bg-white px-5 py-3 text-sm font-black text-slate-950 hover:bg-slate-100">New Post</a>
                    <a href="{{ route('admin.blog.index', ['status' => \App\Models\BlogPost::STATUS_PENDING]) }}" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-black text-slate-950 hover:bg-amber-300">Review Pending ({{ $pendingCount }})</a>
                    <a href="{{ route('admin.announcements.index') }}" class="rounded-2xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 hover:bg-cyan-300">Website News & Events</a>
                    <a href="{{ route('blog.index') }}" target="_blank" class="rounded-2xl border border-white/15 px-5 py-3 text-sm font-bold text-white hover:bg-white/10">View Public Blog</a>
                </div>
            </div>
            <div class="border-t border-white/10 bg-white/[.06] p-6 sm:p-8 lg:border-l lg:border-t-0">
                <p class="text-xs font-black uppercase text-white/40" style="letter-spacing:.14em;">Today’s Focus</p>
                <div class="mt-5 rounded-3xl bg-white p-5 text-slate-950">
                    <p class="text-4xl font-black">{{ number_format($pendingCount) }}</p>
                    <p class="mt-2 text-sm font-bold text-slate-500">article{{ $pendingCount === 1 ? '' : 's' }} waiting for editorial review</p>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-2xl bg-white/10 p-4">
                        <p class="font-black">{{ number_format($posts->total()) }}</p>
                        <p class="mt-1 text-white/50">Shown results</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4">
                        <p class="font-black">{{ now()->format('M j') }}</p>
                        <p class="mt-1 text-white/50">Studio date</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($statuses as $status)
            @php($style = $statusStyles[$status] ?? ['bg' => 'bg-white', 'text' => 'text-slate-700', 'ring' => 'ring-slate-200', 'label' => \Illuminate\Support\Str::headline($status)])
            <a href="{{ route('admin.blog.index', ['status' => $status]) }}" class="{{ $style['bg'] }} {{ $style['ring'] }} group rounded-3xl p-5 shadow-sm ring-1 transition hover:-translate-y-0.5 hover:shadow-xl">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-xs font-black uppercase {{ $style['text'] }}" style="letter-spacing:.12em;">{{ $style['label'] }}</p>
                    <span class="rounded-full bg-white/80 px-3 py-1 text-xs font-black text-slate-500 group-hover:text-slate-900">Open</span>
                </div>
                <p class="mt-4 text-4xl font-black text-slate-950">{{ number_format($statusCounts[$status] ?? 0) }}</p>
            </a>
        @endforeach
    </div>

    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.blog.index') }}" class="grid md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-black uppercase text-slate-400 mb-2" style="letter-spacing:.1em;">Search</label>
                <input name="search" value="{{ $filters['search'] }}" class="w-full border border-slate-200 rounded-2xl px-4 py-3 focus:border-slate-950 focus:ring-slate-950" placeholder="Title, summary, or article text">
            </div>
            <div>
                <label class="block text-xs font-black uppercase text-slate-400 mb-2" style="letter-spacing:.1em;">Status</label>
                <select name="status" class="w-full border border-slate-200 rounded-2xl px-4 py-3 focus:border-slate-950 focus:ring-slate-950">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-black uppercase text-slate-400 mb-2" style="letter-spacing:.1em;">Writer</label>
                <select name="author_id" class="w-full border border-slate-200 rounded-2xl px-4 py-3 focus:border-slate-950 focus:ring-slate-950">
                    <option value="">All teachers</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string) $teacher->id === $filters['author_id'])>{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button class="w-full bg-slate-950 text-white py-3 rounded-2xl font-bold hover:bg-slate-800">Filter</button>
                <a href="{{ route('admin.blog.index') }}" class="w-full text-center border border-slate-200 text-slate-700 py-3 rounded-2xl font-bold hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid xl:grid-cols-2 gap-5">
        @forelse($posts as $post)
            @php($style = $statusStyles[$post->status] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'ring' => 'ring-slate-200', 'label' => $post->status_label])
            <article class="group overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-2xl">
                <div class="grid sm:grid-cols-[210px_1fr]">
                    <div class="relative">
                        @if($post->image_url)
                            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="w-full h-full min-h-48 object-cover">
                        @else
                            <div class="h-full min-h-48 bg-gradient-to-br from-indigo-100 via-cyan-100 to-amber-100"></div>
                        @endif
                        <div class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-black text-slate-700 shadow">{{ $post->reading_minutes }} min</div>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-black {{ $style['bg'] }} {{ $style['text'] }}">{{ $post->status_label }}</span>
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-700">{{ \Illuminate\Support\Str::headline($post->category) }}</span>
                        </div>

                        <div class="mt-4">
                            <h2 class="text-xl font-black leading-snug text-slate-950 group-hover:text-indigo-700">{{ $post->title }}</h2>
                            <p class="text-sm text-slate-500 mt-1">By {{ $post->author?->name ?? 'Unknown teacher' }}</p>
                        </div>

                        <p class="mt-3 text-sm text-slate-600 leading-6">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 150) }}</p>

                        <div class="mt-4 grid sm:grid-cols-2 gap-2 text-xs text-slate-500">
                            <p>Submitted: {{ $post->submitted_at?->format('d M Y, H:i') ?? '-' }}</p>
                            <p>Published: {{ $post->published_at?->format('d M Y, H:i') ?? '-' }}</p>
                            <p>Reviewed by: {{ $post->reviewer?->name ?? '-' }}</p>
                            <p>Updated: {{ $post->updated_at->format('d M Y') }}</p>
                        </div>

                        @if($post->admin_note)
                            <div class="mt-4 rounded-2xl bg-rose-50 border border-rose-100 p-3 text-sm text-rose-700">
                                {{ $post->admin_note }}
                            </div>
                        @endif

                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('admin.blog.edit', $post) }}" class="bg-slate-950 hover:bg-slate-800 text-white px-4 py-2 rounded-xl font-bold">Review/Edit</a>
                            @if($post->status === \App\Models\BlogPost::STATUS_PUBLISHED)
                                <a href="{{ route('blog.show', $post) }}" target="_blank" class="border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold hover:bg-slate-50">View Public</a>
                            @endif
                            <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" onsubmit="return confirm('Delete this blog post?');">
                                @csrf
                                @method('DELETE')
                                <button class="border border-rose-200 text-rose-700 px-4 py-2 rounded-xl font-semibold hover:bg-rose-50">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="xl:col-span-2 bg-white rounded-[2rem] border border-dashed border-slate-300 p-14 text-center text-slate-500">
                <p class="text-xl font-black text-slate-900">No articles match this view.</p>
                <p class="mt-2">Try clearing filters or wait for new teacher submissions.</p>
            </div>
        @endforelse
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-4">
        {{ $posts->links() }}
    </div>
</div>
@endsection
