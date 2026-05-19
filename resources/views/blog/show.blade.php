@extends('layouts.app')

@section('title', $post->title)

@section('content')
<article class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
    <div class="grid lg:grid-cols-[1.1fr_.9fr]">
        <div class="p-6 sm:p-10 lg:p-12 flex flex-col justify-center">
            <div class="flex flex-wrap items-center gap-2 text-xs font-black uppercase text-gray-500">
                <span class="bg-gray-950 text-white px-3 py-1.5 rounded-md">{{ \Illuminate\Support\Str::headline($post->category) }}</span>
                <span>{{ $post->display_date }}</span>
                <span>/</span>
                <span>{{ $post->reading_minutes }} min read</span>
            </div>
            <h1 class="mt-5 text-4xl sm:text-6xl font-black text-gray-950 leading-none">{{ $post->title }}</h1>
            @if($post->excerpt)
                <p class="mt-6 text-xl text-gray-600 leading-9">{{ $post->excerpt }}</p>
            @endif
            <p class="mt-6 text-sm font-bold text-gray-900">Written by {{ $post->author?->name ?? 'Cambridge Teacher' }}</p>
        </div>

        @if($post->image_url)
            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="w-full h-80 lg:h-full min-h-[420px] object-cover">
        @else
            <div class="h-80 lg:h-full min-h-[420px] bg-gradient-to-br from-slate-100 to-gray-300"></div>
        @endif
    </div>

    <div class="max-w-3xl mx-auto px-6 sm:px-8 py-10 sm:py-14">
        <div class="text-lg text-gray-800 leading-9 whitespace-pre-line">{{ $post->body }}</div>

        @if($post->gallery_image_urls)
            <section class="mt-10">
                <h2 class="text-2xl font-black text-gray-950">Photo Gallery</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach($post->gallery_image_urls as $galleryImage)
                        <img src="{{ $galleryImage }}" alt="{{ $post->title }} photo" class="h-72 w-full rounded-2xl object-cover shadow-sm">
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-12 pt-8 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('blog.index') }}" class="bg-gray-950 text-white px-5 py-3 rounded-lg font-bold hover:bg-gray-800">Back to Blog</a>
            <button type="button" id="copyBlogLink" data-copy-url="{{ route('blog.show', $post) }}" class="border border-emerald-200 text-emerald-700 px-5 py-3 rounded-lg font-bold hover:bg-emerald-50">Copy Link</button>
            <a href="{{ url('/') }}" class="text-gray-700 font-bold hover:underline">Cambridge International School</a>
        </div>
    </div>
</article>

@if($relatedPosts->isNotEmpty())
<section class="mt-8 bg-white rounded-2xl shadow border border-gray-100 p-6 sm:p-8">
    <div class="flex items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-black text-gray-950">More In {{ \Illuminate\Support\Str::headline($post->category) }}</h2>
        <a href="{{ route('blog.index', ['category' => $post->category]) }}" class="text-sm font-bold text-blue-700 hover:underline">View section</a>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
        @foreach($relatedPosts as $related)
            <a href="{{ route('blog.show', $related) }}" class="block border border-gray-100 rounded-xl overflow-hidden hover:shadow-lg transition">
                @if($related->image_url)
                    <img src="{{ $related->image_url }}" alt="{{ $related->title }}" class="w-full h-36 object-cover">
                @endif
                <div class="p-5">
                    <p class="text-xs font-black text-gray-500 uppercase">{{ $related->display_date }}</p>
                    <h3 class="font-black text-gray-950 mt-2 leading-snug">{{ $related->title }}</h3>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif
<script>
    document.getElementById('copyBlogLink')?.addEventListener('click', async function () {
        await navigator.clipboard.writeText(this.dataset.copyUrl);
        const originalText = this.textContent;
        this.textContent = 'Copied';
        setTimeout(() => this.textContent = originalText, 1600);
    });
</script>
@endsection
