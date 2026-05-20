@extends('layouts.app')

@section('title', 'News And Events')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Website News And Events</h1>
            <p class="text-sm text-gray-500 mt-1">Manage homepage cards, ticker headlines, and admissions announcements from one place.</p>
            <p class="text-xs text-gray-400 mt-1">Pinned updates appear first on the homepage, and lower sort-order numbers win when multiple pinned updates exist.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('admin.blog.index') }}" class="text-sm text-blue-600 hover:underline">
                {{ auth()->user()->isAdmin() ? 'Back to dashboard' : 'Back to Blog Studio' }}
            </a>
            <a href="{{ route('admin.announcements.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-semibold shadow-lg">Create update</a>
        </div>
    </div>

    <div class="bg-white shadow rounded-2xl p-6">
        <form method="GET" action="{{ route('admin.announcements.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Search</label>
                <input name="search" value="{{ $filters['search'] }}" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500" placeholder="Title, summary, ticker...">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Category</label>
                <select name="category" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ ucfirst($category) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    <option value="published" @selected($filters['status'] === 'published')>Published</option>
                    <option value="draft" @selected($filters['status'] === 'draft')>Draft</option>
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-xl font-semibold hover:bg-indigo-700 transition">Filter</button>
                <a href="{{ route('admin.announcements.index') }}" class="w-full border border-gray-200 text-gray-700 py-2 rounded-xl text-center">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        @forelse($announcements as $announcement)
            <article class="bg-white shadow rounded-3xl overflow-hidden border border-gray-100">
                @if($announcement->image_url)
                    <img src="{{ $announcement->image_url }}" alt="{{ $announcement->title }}" class="w-full h-56 object-cover">
                @endif
                <div class="p-6 space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ ucfirst($announcement->category) }}</span>
                        @if($announcement->is_published)
                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Published</span>
                        @else
                            <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Draft</span>
                        @endif
                        @if($announcement->is_pinned)
                            <span class="inline-flex rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">Pinned</span>
                        @endif
                        @if($announcement->show_in_ticker)
                            <span class="inline-flex rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">Ticker</span>
                        @endif
                        @if($announcement->parent_messages_sent_at)
                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Sent to parents</span>
                        @elseif($announcement->send_to_parent_dashboard)
                            <span class="inline-flex rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700">Parent delivery pending</span>
                        @endif
                        @if($announcement->gallery_image_urls)
                            <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ count($announcement->gallery_image_urls) }} extra image{{ count($announcement->gallery_image_urls) === 1 ? '' : 's' }}</span>
                        @endif
                        @if($announcement->video_url)
                            <span class="inline-flex rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">Video Link</span>
                        @endif
                    </div>

                    <div>
                        <h2 class="text-xl font-black text-gray-900">{{ $announcement->title }}</h2>
                        <p class="text-sm text-gray-500 mt-2">{{ $announcement->summary }}</p>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3 text-sm text-gray-500">
                        <div>Display date: {{ $announcement->display_date ?? 'Not set' }}</div>
                        <div>Location: {{ $announcement->location ?: 'Not set' }}</div>
                        <div>Published at: {{ $announcement->published_at?->format('d M Y, H:i') ?? 'Immediate' }}</div>
                        <div>Expires at: {{ $announcement->expires_at?->format('d M Y, H:i') ?? 'No expiry' }}</div>
                        <div>Sort order: {{ $announcement->sort_order }}</div>
                        <div>Parent delivery: {{ $announcement->parent_messages_sent_at?->format('d M Y, H:i') ?? ($announcement->send_to_parent_dashboard ? 'Pending' : 'Off') }}</div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 text-sm text-slate-700">
                        <p class="font-semibold text-slate-900">Ticker headline</p>
                        <p class="mt-1">{{ $announcement->ticker_text }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('admin.announcements.edit', $announcement) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl font-semibold">Edit</a>
                        @if($announcement->is_published)
                            <a href="{{ route('announcements.show', $announcement) }}" target="_blank" class="border border-slate-200 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-xl font-semibold">View Public</a>
                        @endif
                        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this website update?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="border border-rose-200 text-rose-700 hover:bg-rose-50 px-4 py-2 rounded-xl font-semibold">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="xl:col-span-2 bg-white rounded-3xl shadow p-12 text-center text-gray-500">
                <p class="text-lg font-semibold text-gray-800">No website updates yet.</p>
                <p class="mt-2">Create your first announcement to power the homepage ticker and news cards.</p>
            </div>
        @endforelse
    </div>

    <div class="bg-white rounded-2xl shadow px-6 py-4">
        {{ $announcements->links() }}
    </div>
</div>
@endsection
