@extends('layouts.app')

@section('title', $post->title)

@push('styles')
<style>
    body {
        background: #f8fafc !important;
    }

    body::before {
        display: none !important;
    }

    main {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    .app-shell {
        max-width: none !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .blog-gist {
        min-height: 100vh;
        background:
            linear-gradient(180deg, rgba(239, 246, 255, .96), rgba(255, 255, 255, .98) 28rem),
            #f8fafc;
        color: #0f172a;
    }

    .gist-wrap {
        width: min(1060px, calc(100% - 2rem));
        margin-inline: auto;
    }

    .gist-logo {
        font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
        letter-spacing: .08em;
    }

    .gist-headline {
        font-family: Georgia, 'Times New Roman', serif;
        letter-spacing: -.02em;
    }

    .gist-card {
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, .1);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
    }

    .article-body p,
    .article-body {
        font-size: 1.075rem;
        line-height: 1.95;
        color: #334155;
    }
</style>
@endpush

@section('content')
@php
    $fallbackImage = asset('images/school life1.jpg');
@endphp

<div class="blog-gist">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
        <div class="gist-wrap flex h-[70px] items-center justify-between gap-4">
            <a href="{{ route('blog.index') }}" class="gist-logo text-3xl uppercase text-slate-950">
                Cambridge<span class="text-blue-700">Blog</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('blog.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-black text-slate-600 hover:text-slate-950">Blog</a>
                <a href="{{ url('/') }}" class="hidden rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white hover:bg-blue-600 sm:inline-flex">Home</a>
            </div>
        </div>
    </header>

    <article>
        <section class="relative min-h-[34rem] overflow-hidden border-b border-slate-200">
            <img src="{{ $post->image_url ?: $fallbackImage }}" alt="{{ $post->title }}" class="absolute inset-0 h-full w-full object-cover opacity-70">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-900/55 to-blue-950/20"></div>
            <div class="gist-wrap relative flex min-h-[34rem] items-end py-10">
                <div class="max-w-4xl">
                    <span class="inline-flex rounded-md bg-blue-700 px-3 py-2 text-[11px] font-black uppercase tracking-wider text-white">{{ \Illuminate\Support\Str::headline($post->category) }}</span>
                    <h1 class="gist-headline mt-5 text-4xl font-black leading-[1.02] text-white md:text-6xl">{{ $post->title }}</h1>
                    @if($post->excerpt)
                        <p class="mt-5 max-w-3xl text-lg leading-8 text-white/72">{{ $post->excerpt }}</p>
                    @endif
                    <div class="mt-6 flex flex-wrap gap-3 text-sm font-semibold text-white/65">
                        <span>By {{ $post->author?->name ?? 'Cambridge Teacher' }}</span>
                        <span>•</span>
                        <span>{{ $post->display_date }}</span>
                        <span>•</span>
                        <span>{{ $post->reading_minutes }} min read</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="gist-wrap grid gap-8 py-10 lg:grid-cols-[1fr_280px]">
            <div class="gist-card rounded-2xl p-6 md:p-9">
                <div class="article-body whitespace-pre-line">{{ $post->body }}</div>

                @if($post->gallery_image_urls)
                    <section class="mt-10 border-t border-slate-100 pt-8">
                        <h2 class="gist-headline text-3xl font-black text-slate-950">Photo Gallery</h2>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            @foreach($post->gallery_image_urls as $galleryImage)
                                <img src="{{ $galleryImage }}" alt="{{ $post->title }} photo" class="h-72 w-full rounded-xl object-cover">
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="space-y-5">
                <div class="gist-card rounded-2xl p-5">
                    <p class="text-xs font-black uppercase tracking-[.18em] text-blue-700">Share</p>
                    <button type="button" id="copyBlogLink" data-copy-url="{{ route('blog.show', $post) }}" class="mt-4 w-full rounded-xl bg-blue-700 px-4 py-3 text-sm font-black text-white hover:bg-blue-600">Copy Link</button>
                    <a href="{{ route('blog.index', ['category' => $post->category]) }}" class="mt-3 flex w-full justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-600 hover:text-slate-950">More In {{ \Illuminate\Support\Str::headline($post->category) }}</a>
                </div>

                @if($relatedPosts->isNotEmpty())
                    <div class="gist-card rounded-2xl p-5">
                        <p class="text-xs font-black uppercase tracking-[.18em] text-blue-700">Related</p>
                        <div class="mt-4 divide-y divide-slate-100">
                            @foreach($relatedPosts as $related)
                                <a href="{{ route('blog.show', $related) }}" class="grid grid-cols-[4.5rem_1fr] gap-3 py-4">
                                    <img src="{{ $related->image_url ?: $fallbackImage }}" alt="{{ $related->title }}" class="h-16 w-20 rounded-lg object-cover">
                                    <span>
                                        <span class="line-clamp-2 text-sm font-black leading-snug text-slate-800">{{ $related->title }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ $related->display_date }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </section>
    </article>
</div>

<script>
    document.getElementById('copyBlogLink')?.addEventListener('click', async function () {
        await navigator.clipboard.writeText(this.dataset.copyUrl);
        const originalText = this.textContent;
        this.textContent = 'Copied';
        setTimeout(() => this.textContent = originalText, 1600);
    });
</script>
@endsection
