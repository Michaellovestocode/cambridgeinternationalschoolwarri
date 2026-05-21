@extends('layouts.blog-admin')

@section('title', 'Gallery Manager')
@section('page_heading', 'Gallery Manager')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-950">School Gallery</h1>
            <p class="mt-1 text-sm text-slate-500">Create albums, upload photos, add captions, and control what appears on the homepage.</p>
        </div>
        <a href="{{ route('admin.gallery.create') }}" class="rounded-2xl bg-indigo-600 px-5 py-3 font-bold text-white shadow-lg hover:bg-indigo-700">Create Album</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        @forelse($albums as $album)
            <article class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                @if($album->cover_image_url)
                    <img src="{{ $album->cover_image_url }}" alt="{{ $album->title }}" class="h-64 w-full object-cover">
                @else
                    <div class="h-64 bg-gradient-to-br from-blue-100 via-amber-100 to-emerald-100"></div>
                @endif
                <div class="space-y-4 p-6">
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">{{ ucfirst($album->category) }}</span>
                        <span class="rounded-full {{ $album->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} px-3 py-1 text-xs font-bold">
                            {{ $album->is_published ? 'Published' : 'Draft' }}
                        </span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $album->images_count }} photo{{ $album->images_count === 1 ? '' : 's' }}</span>
                    </div>

                    <div>
                        <h2 class="text-xl font-black text-slate-950">{{ $album->title }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $album->description ?: 'No description yet.' }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('admin.gallery.edit', $album) }}" class="rounded-xl bg-slate-950 px-4 py-2 font-bold text-white hover:bg-slate-800">Edit</a>
                        <form method="POST" action="{{ route('admin.gallery.destroy', $album) }}" onsubmit="return confirm('Delete this gallery album and its photos?');">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-xl border border-rose-200 px-4 py-2 font-bold text-rose-700 hover:bg-rose-50">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <p class="text-xl font-black text-slate-950">No gallery albums yet.</p>
                <p class="mt-2 text-slate-500">Create the first album to replace the homepage fallback gallery.</p>
            </div>
        @endforelse
    </div>

    <div class="rounded-2xl bg-white px-6 py-4 shadow-sm">
        {{ $albums->links() }}
    </div>
</div>
@endsection
