@extends('layouts.app')

@section('title', 'Review Blog Post')

@section('content')
<div class="bg-white rounded-3xl shadow-lg p-8">
    <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Review Blog Post</h1>
            <p class="text-gray-500 mt-1">Edit the teacher's article, replace the image, then approve, reject, or keep pending.</p>
        </div>
        <a href="{{ route('admin.blog.index') }}" class="border border-gray-200 text-gray-700 px-5 py-3 rounded-xl font-semibold">Back to Posts</a>
    </div>

    <form method="POST" action="{{ route('admin.blog.update', $post) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label for="author_id" class="block text-sm font-semibold text-gray-700">Writer</label>
                <select id="author_id" name="author_id" class="w-full border border-gray-200 rounded-xl px-4 py-3" required>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((int) old('author_id', $post->author_id) === $teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </select>
                @error('author_id')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label for="status" class="block text-sm font-semibold text-gray-700">Status</label>
                <select id="status" name="status" class="w-full border border-gray-200 rounded-xl px-4 py-3" required>
                    @foreach(\App\Models\BlogPost::statuses() as $status)
                        <option value="{{ $status }}" @selected(old('status', $post->status) === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                    @endforeach
                </select>
                @error('status')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label for="title" class="block text-sm font-semibold text-gray-700">Title</label>
                <input id="title" name="title" value="{{ old('title', $post->title) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3" required>
                @error('title')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label for="category" class="block text-sm font-semibold text-gray-700">Category</label>
                <select id="category" name="category" class="w-full border border-gray-200 rounded-xl px-4 py-3" required>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" @selected(old('category', $post->category) === $category)>{{ \Illuminate\Support\Str::headline($category) }}</option>
                    @endforeach
                </select>
                @error('category')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label for="published_at" class="block text-sm font-semibold text-gray-700">Published At</label>
                <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\\TH:i')) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3">
                <p class="text-xs text-gray-500">Leave empty when publishing to use the current time.</p>
                @error('published_at')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label for="image" class="block text-sm font-semibold text-gray-700">Cover Image</label>
                <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.gif,.webp" class="w-full border border-gray-200 rounded-xl px-4 py-3">
                @if($post->image_url)
                    <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="mt-3 h-32 w-52 rounded-2xl object-cover border border-gray-200">
                @endif
                @error('image')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2 lg:col-span-2">
                <label for="excerpt" class="block text-sm font-semibold text-gray-700">Short Summary</label>
                <textarea id="excerpt" name="excerpt" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-3">{{ old('excerpt', $post->excerpt) }}</textarea>
                @error('excerpt')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2 lg:col-span-2">
                <label for="body" class="block text-sm font-semibold text-gray-700">Article</label>
                <textarea id="body" name="body" rows="14" class="w-full border border-gray-200 rounded-xl px-4 py-3" required>{{ old('body', $post->body) }}</textarea>
                @error('body')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2 lg:col-span-2">
                <label for="admin_note" class="block text-sm font-semibold text-gray-700">Admin Note</label>
                <textarea id="admin_note" name="admin_note" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-3">{{ old('admin_note', $post->admin_note) }}</textarea>
                <p class="text-xs text-gray-500">Use this when rejecting or asking the teacher to revise the article.</p>
                @error('admin_note')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-semibold shadow">Save Review</button>
            @if($post->status === \App\Models\BlogPost::STATUS_PUBLISHED)
                <a href="{{ route('blog.show', $post) }}" target="_blank" class="border border-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold">View Public</a>
            @endif
            <a href="{{ route('admin.blog.index') }}" class="text-gray-600 hover:underline font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection
