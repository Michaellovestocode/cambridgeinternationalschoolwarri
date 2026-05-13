@extends('layouts.app')

@section('title', 'Blog Moderation')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Blog Moderation</h1>
            <p class="text-gray-500 mt-1">Review teacher submissions, edit articles, replace images, and publish approved posts.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="border border-gray-200 text-gray-700 px-5 py-3 rounded-xl font-semibold">Back to Dashboard</a>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <form method="GET" action="{{ route('admin.blog.index') }}" class="grid md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Search</label>
                <input name="search" value="{{ $filters['search'] }}" class="w-full border border-gray-200 rounded-xl px-4 py-3" placeholder="Title or article text">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-xl px-4 py-3">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Teacher</label>
                <select name="author_id" class="w-full border border-gray-200 rounded-xl px-4 py-3">
                    <option value="">All teachers</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string) $teacher->id === $filters['author_id'])>{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold">Filter</button>
                <a href="{{ route('admin.blog.index') }}" class="w-full text-center border border-gray-200 text-gray-700 py-3 rounded-xl">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid xl:grid-cols-2 gap-6">
        @forelse($posts as $post)
            <article class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100">
                <div class="grid sm:grid-cols-[180px_1fr]">
                    <div>
                        @if($post->image_url)
                            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="w-full h-full min-h-48 object-cover">
                        @else
                            <div class="h-full min-h-48 bg-gradient-to-br from-indigo-100 via-cyan-100 to-amber-100"></div>
                        @endif
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">{{ $post->status_label }}</span>
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">{{ \Illuminate\Support\Str::headline($post->category) }}</span>
                        </div>

                        <div>
                            <h2 class="text-xl font-black text-gray-900">{{ $post->title }}</h2>
                            <p class="text-sm text-gray-500 mt-1">By {{ $post->author?->name ?? 'Unknown teacher' }}</p>
                        </div>

                        <p class="text-sm text-gray-600 leading-6">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 150) }}</p>

                        <div class="grid sm:grid-cols-2 gap-2 text-xs text-gray-500">
                            <p>Submitted: {{ $post->submitted_at?->format('d M Y, H:i') ?? '-' }}</p>
                            <p>Published: {{ $post->published_at?->format('d M Y, H:i') ?? '-' }}</p>
                            <p>Reviewed by: {{ $post->reviewer?->name ?? '-' }}</p>
                            <p>Updated: {{ $post->updated_at->format('d M Y') }}</p>
                        </div>

                        @if($post->admin_note)
                            <div class="rounded-2xl bg-rose-50 border border-rose-100 p-3 text-sm text-rose-700">
                                {{ $post->admin_note }}
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.blog.edit', $post) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl font-semibold">Review/Edit</a>
                            @if($post->status === \App\Models\BlogPost::STATUS_PUBLISHED)
                                <a href="{{ route('blog.show', $post) }}" target="_blank" class="border border-gray-200 text-gray-700 px-4 py-2 rounded-xl font-semibold">View Public</a>
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
            <div class="xl:col-span-2 bg-white rounded-3xl shadow p-12 text-center text-gray-500">
                <p class="text-lg font-bold text-gray-900">No blog submissions yet.</p>
                <p class="mt-2">Teacher articles will appear here when submitted.</p>
            </div>
        @endforelse
    </div>

    <div class="bg-white rounded-2xl shadow px-6 py-4">
        {{ $posts->links() }}
    </div>
</div>
@endsection
