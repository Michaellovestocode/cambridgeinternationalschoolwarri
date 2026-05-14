@extends('layouts.app')

@section('title', 'My Blog Articles')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900">My Blog Articles</h1>
            <p class="text-gray-500 mt-1">Write education articles and submit them for admin approval.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if(auth()->user()->canManageBlogStudio())
                <a href="{{ route('admin.blog.index') }}" class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-3 rounded-xl font-semibold shadow">Blog Studio</a>
            @endif
            <a href="{{ route('admin.dashboard') }}" class="border border-gray-200 text-gray-700 px-5 py-3 rounded-xl font-semibold">Dashboard</a>
            <a href="{{ route('teacher.blog.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-semibold shadow">New Article</a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <form method="GET" action="{{ route('teacher.blog.index') }}" class="flex flex-wrap gap-3">
            <select name="status" class="border border-gray-200 rounded-xl px-4 py-3">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                @endforeach
            </select>
            <button class="bg-indigo-600 text-white px-5 py-3 rounded-xl font-semibold">Filter</button>
            <a href="{{ route('teacher.blog.index') }}" class="border border-gray-200 text-gray-700 px-5 py-3 rounded-xl font-semibold">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-3xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Article</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Submitted</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-gray-500">Published</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($posts as $post)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900">{{ $post->title }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ \Illuminate\Support\Str::headline($post->category) }}</p>
                                @if($post->admin_note)
                                    <p class="text-xs text-rose-600 mt-2">Admin note: {{ $post->admin_note }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">{{ $post->status_label }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $post->submitted_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $post->published_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('teacher.blog.edit', $post) }}" class="text-indigo-600 font-bold hover:underline">{{ $post->status === \App\Models\BlogPost::STATUS_PUBLISHED ? 'View' : 'Edit' }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">No blog articles yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow px-6 py-4">
        {{ $posts->links() }}
    </div>
</div>
@endsection
