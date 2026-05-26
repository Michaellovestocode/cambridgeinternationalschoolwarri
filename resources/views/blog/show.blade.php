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
        overflow-x: hidden;
    }

    .gist-wrap {
        width: min(1060px, 100%);
        margin-inline: auto;
        padding-inline: 1rem;
        box-sizing: border-box;
    }

    .gist-logo {
        font-family: 'Sora', system-ui, sans-serif;
        font-size: 1.32rem;
        font-weight: 900;
        letter-spacing: .02em;
        line-height: 1;
        white-space: nowrap;
    }

    .gist-headline {
        font-family: 'Sora', system-ui, sans-serif;
        letter-spacing: -.03em;
    }

    .gist-card {
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, .1);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
    }

    .blog-nav-shell {
        background:
            linear-gradient(135deg, rgba(255, 255, 255, .98), rgba(239, 246, 255, .94)),
            #ffffff;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .10), 0 1px 0 rgba(37, 99, 235, .08);
    }

    .blog-nav-shell.drawer-open {
        z-index: 9999;
    }

    .blog-mobile-brand {
        font-family: 'Sora', system-ui, sans-serif;
        letter-spacing: .018em;
        font-size: .95rem;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    .blog-logo-mark {
        background: linear-gradient(135deg, #2563eb, #facc15 52%, #16a34a);
    }

    .blog-nav-shell::after {
        content: '';
        display: block;
        height: 3px;
        background: linear-gradient(90deg, #2563eb, #facc15 55%, #16a34a);
    }

    .school-accent {
        background: linear-gradient(90deg, #2563eb, #facc15 55%, #16a34a);
    }

    .blog-mobile-drawer {
        opacity: 0;
        pointer-events: none;
        transition: opacity .28s ease;
    }

    .blog-mobile-drawer.open {
        opacity: 1;
        pointer-events: auto;
    }

    .blog-mobile-panel {
        transform: translateX(-105%);
        transition: transform .42s cubic-bezier(.22, 1, .36, 1);
    }

    .blog-mobile-drawer.open .blog-mobile-panel {
        transform: translateX(0);
    }

    .article-body p,
    .article-body {
        font-size: 1.075rem;
        line-height: 1.95;
        color: #334155;
    }

    @media (max-width: 767px) {
        .gist-logo {
            font-size: .78rem;
            line-height: 1;
            letter-spacing: .005em;
        }

        .blog-mobile-muted {
            display: none;
        }

        .mobile-article-title {
            font-size: 2rem;
            line-height: 1.08;
        }
    }

    @media (max-width: 380px) {
        .gist-wrap {
            padding-inline: .75rem;
        }

        .gist-logo {
            font-size: .72rem;
        }

        .blog-logo-mark {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: .85rem;
        }

        .blog-mobile-panel {
            width: 90vw;
        }
    }
</style>
@endpush

@section('content')
@php
    $fallbackImage = asset('images/school life1.jpg');
@endphp

<div class="blog-gist">
    <header class="blog-nav-shell sticky top-0 z-40 border-b border-slate-200 shadow-sm backdrop-blur">
        <div class="gist-wrap flex h-[72px] items-center justify-between gap-3">
            <a href="{{ route('blog.index') }}" class="flex min-w-0 items-center gap-3">
                <span class="blog-logo-mark flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-2xl shadow-md sm:h-11 sm:w-11">
                    <img src="{{ asset('images/schoollogo.jpg') }}" alt="Cambridge International School logo" class="h-full w-full object-cover">
                </span>
                <span class="gist-logo text-slate-950">
                    Cambridge<span class="text-blue-700">Magazine</span>
                </span>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('blog.index') }}" class="hidden rounded-lg border border-slate-200 px-4 py-2 text-sm font-black text-slate-600 hover:text-slate-950 sm:inline-flex">Blog</a>
                <a href="{{ url('/') }}" class="hidden rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white hover:bg-blue-600 sm:inline-flex">Home</a>
                <button type="button" class="grid h-11 w-11 place-items-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm md:hidden" onclick="toggleBlogMenu(true)" aria-label="Menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
            </div>
        </div>

    </header>

    <div id="mobileBlogNav" class="blog-mobile-drawer fixed inset-0 z-[10000] bg-slate-950/45 backdrop-blur-sm md:hidden" onclick="if (event.target.id === 'mobileBlogNav') toggleBlogMenu(false)">
            <div class="blog-mobile-panel h-full w-[86vw] max-w-sm overflow-y-auto bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 p-5">
                    <a href="{{ route('blog.index') }}" class="flex min-w-0 items-center gap-3">
                        <span class="blog-logo-mark flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-2xl shadow-md">
                            <img src="{{ asset('images/schoollogo.jpg') }}" alt="Cambridge International School logo" loading="lazy" class="h-full w-full object-cover">
                        </span>
                        <span class="blog-mobile-brand text-slate-950">Cambridge <span class="text-blue-700">Magazine</span></span>
                    </a>
                    <button type="button" onclick="toggleBlogMenu(false)" class="rounded-full bg-slate-100 px-4 py-3 text-xl font-black text-slate-700" aria-label="Close menu">&times;</button>
                </div>
                <div class="grid gap-3 p-5">
                    <a href="{{ route('blog.index') }}" class="rounded-2xl border border-slate-200 bg-blue-50 px-4 py-4 text-sm font-black text-blue-700">Blog Home</a>
                    <a href="{{ url('/') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm font-black text-slate-700">School Website</a>
                    @foreach(\App\Models\BlogPost::categories() as $category)
                        <a href="{{ route('blog.index', ['category' => $category]) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm font-black text-slate-700">{{ \Illuminate\Support\Str::headline($category) }}</a>
                    @endforeach
                </div>
            </div>
        </div>

    <article>
        <section class="relative min-h-[34rem] overflow-hidden border-b border-slate-200">
            <img src="{{ $post->image_url ?: $fallbackImage }}" alt="{{ $post->title }}" class="absolute inset-0 h-full w-full object-cover opacity-70">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-900/55 to-blue-950/20"></div>
            <div class="gist-wrap relative flex min-h-[34rem] items-end py-10">
                <div class="max-w-4xl">
                    <span class="inline-flex rounded-md bg-blue-700 px-3 py-2 text-[11px] font-black uppercase tracking-wider text-white">{{ \Illuminate\Support\Str::headline($post->category) }}</span>
                    <h1 class="mobile-article-title gist-headline mt-5 text-4xl font-black leading-[1.02] text-white md:text-6xl">{{ $post->title }}</h1>
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
                <div class="school-accent mb-7 h-1 w-20 rounded-full"></div>
                <div class="article-body whitespace-pre-line">{{ $post->body }}</div>

                @if($post->gallery_image_urls)
                    <section class="mt-10 border-t border-slate-100 pt-8">
                        <h2 class="gist-headline text-3xl font-black text-slate-950">Photo Gallery</h2>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            @foreach($post->gallery_image_urls as $galleryImage)
                                <img src="{{ $galleryImage }}" alt="{{ $post->title }} photo" loading="lazy" class="h-72 w-full rounded-xl object-cover">
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
                                    <img src="{{ $related->image_url ?: $fallbackImage }}" alt="{{ $related->title }}" loading="lazy" class="h-16 w-20 rounded-lg object-cover">
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
    function toggleBlogMenu(forceOpen = null) {
        const menu = document.getElementById('mobileBlogNav');
        const header = document.querySelector('.blog-nav-shell');
        const open = forceOpen === null ? !menu.classList.contains('open') : forceOpen;

        menu.classList.toggle('open', open);
        header?.classList.toggle('drawer-open', open);
        document.body.classList.toggle('overflow-hidden', open);
    }

    document.getElementById('copyBlogLink')?.addEventListener('click', async function () {
        await navigator.clipboard.writeText(this.dataset.copyUrl);
        const originalText = this.textContent;
        this.textContent = 'Copied';
        setTimeout(() => this.textContent = originalText, 1600);
    });
</script>
@endsection
