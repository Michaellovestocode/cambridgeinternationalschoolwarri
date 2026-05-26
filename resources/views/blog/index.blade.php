@extends('layouts.app')

@section('title', 'Cambridge Blog')

@push('styles')
<style>
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
        width: min(1200px, calc(100% - 2rem));
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

    .gist-img {
        background: linear-gradient(135deg, #dbeafe, #f8fafc);
    }

    .gist-ticker-track {
        animation: gistTicker 28s linear infinite;
    }

    @keyframes gistTicker {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }

    .gist-line-title {
        display: flex;
        align-items: center;
        gap: .75rem;
        font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .gist-line-title::before {
        content: '';
        width: .45rem;
        height: .45rem;
        border-radius: 999px;
        background: #ef233c;
        flex: none;
    }

    .gist-line-title::after {
        content: '';
        height: 1px;
        flex: 1;
        background: rgba(15, 23, 42, .12);
    }

    @media (max-width: 767px) {
        .gist-wrap {
            width: 100%;
            padding-inline: 1rem;
        }

        .gist-mobile-scroll {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: minmax(15rem, 70vw);
            gap: .9rem;
            overflow-x: auto;
            padding-bottom: .35rem;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
        }

        .gist-mobile-scroll::-webkit-scrollbar {
            display: none;
        }

        .gist-mobile-scroll > * {
            scroll-snap-align: start;
        }
    }
</style>
@endpush

@section('content')
@php
    $postCollection = $posts->getCollection();
    $featuredPost = $postCollection->first();
    $sidePosts = $postCollection->skip(1)->take(3);
    $latestPosts = $postCollection->skip(4);
    $fallbackImage = asset('images/school life1.jpg');
    $categoryIcons = [
        'education' => 'ED',
        'study tips' => 'ST',
        'parenting' => 'PA',
        'exams' => 'EX',
        'school life' => 'SL',
        'leadership' => 'LD',
    ];
    $breakingPosts = ($trendingPosts ?? collect())->take(4);
@endphp

<div class="blog-gist">
    <div class="bg-blue-700 text-white">
        <div class="flex h-9 items-center overflow-hidden text-xs font-black">
            <div class="flex h-full shrink-0 items-center bg-slate-950 px-4 text-[10px] uppercase tracking-[.24em] text-amber-300">Notice</div>
            <div class="min-w-0 flex-1 overflow-hidden">
                <div class="gist-ticker-track flex w-max items-center gap-8 whitespace-nowrap px-4">
                    @forelse($breakingPosts->concat($breakingPosts) as $post)
                        <a href="{{ route('blog.show', $post) }}" class="hover:underline">{{ $post->title }}</a>
                        <span class="text-white/70">•</span>
                    @empty
                        <span>Latest school stories will appear here once published.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
        <div class="gist-wrap flex h-[70px] items-center justify-between gap-4">
            <a href="{{ route('blog.index') }}" class="gist-logo text-3xl uppercase text-slate-950">
                Cambridge<span class="text-blue-700">Blog</span>
            </a>

            <nav class="hidden items-center gap-3 md:flex">
                <a href="{{ route('blog.index') }}" class="rounded-lg px-4 py-3 text-sm font-bold {{ $filters['category'] ? 'text-slate-500 hover:text-slate-950' : 'bg-blue-50 text-blue-700' }}">Home</a>
                @foreach($categories as $category)
                    <a href="{{ route('blog.index', ['category' => $category]) }}" class="rounded-lg px-4 py-3 text-sm font-bold {{ $filters['category'] === $category ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:text-slate-950' }}">{{ \Illuminate\Support\Str::headline($category) }}</a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('blog.index') }}" class="hidden w-56 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 md:flex">
                @if($filters['category'])
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                @endif
                <span class="mr-2 text-slate-400">⌕</span>
                <input name="search" value="{{ $filters['search'] }}" class="w-full bg-transparent text-sm text-slate-800 outline-none placeholder:text-slate-400" placeholder="Search blog...">
            </form>

            <div class="flex items-center gap-4 md:hidden">
                <button type="button" class="text-2xl text-slate-700" onclick="document.getElementById('mobileBlogSearch').classList.toggle('hidden')" aria-label="Search">⌕</button>
                <button type="button" class="text-2xl text-slate-700" onclick="document.getElementById('mobileBlogNav').classList.toggle('hidden')" aria-label="Menu">☰</button>
            </div>
        </div>

        <div id="mobileBlogSearch" class="hidden border-t border-slate-200 px-4 py-3 md:hidden">
            <form method="GET" action="{{ route('blog.index') }}" class="flex items-center rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                @if($filters['category'])
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                @endif
                <input name="search" value="{{ $filters['search'] }}" class="w-full bg-transparent text-base text-slate-800 outline-none placeholder:text-slate-400" placeholder="Search articles">
                <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white">Go</button>
            </form>
        </div>

        <div id="mobileBlogNav" class="hidden border-t border-slate-200 px-4 py-3 md:hidden">
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('blog.index') }}" class="rounded-xl border border-slate-200 px-3 py-3 text-center text-sm font-bold text-slate-800">Home</a>
                @foreach($categories as $category)
                    <a href="{{ route('blog.index', ['category' => $category]) }}" class="rounded-xl border border-slate-200 px-3 py-3 text-center text-sm font-bold text-slate-600">{{ \Illuminate\Support\Str::headline($category) }}</a>
                @endforeach
            </div>
        </div>
    </header>

    <main class="gist-wrap py-8 md:py-10">
        @if($featuredPost)
            <section class="grid gap-5 lg:grid-cols-[1.9fr_.85fr]">
                <a href="{{ route('blog.show', $featuredPost) }}" class="group relative min-h-[27rem] overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 shadow-xl md:min-h-[31rem]">
                    <img src="{{ $featuredPost->image_url ?: $fallbackImage }}" alt="{{ $featuredPost->title }}" class="absolute inset-0 h-full w-full object-cover opacity-80 transition duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/35 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-5 md:p-8">
                        <span class="inline-flex rounded-md bg-blue-700 px-3 py-2 text-[11px] font-black uppercase tracking-wider text-white">{{ \Illuminate\Support\Str::headline($featuredPost->category) }}</span>
                        <h1 class="gist-headline mt-5 max-w-4xl text-3xl font-black leading-[1.03] text-white md:text-5xl">{{ $featuredPost->title }}</h1>
                        <div class="mt-4 flex flex-wrap gap-3 text-xs font-semibold text-white/75 md:text-sm">
                            <span>By {{ $featuredPost->author?->name ?? 'Cambridge Teacher' }}</span>
                            <span>•</span>
                            <span>{{ $featuredPost->display_date }}</span>
                            <span>•</span>
                            <span>{{ $featuredPost->reading_minutes }} min read</span>
                        </div>
                    </div>
                </a>

                <div class="gist-mobile-scroll lg:grid lg:auto-rows-fr lg:grid-flow-row lg:gap-3 lg:overflow-visible">
                    @foreach($sidePosts as $post)
                        <a href="{{ route('blog.show', $post) }}" class="gist-card group grid min-h-40 grid-cols-[7rem_1fr] overflow-hidden rounded-xl md:grid-cols-[8rem_1fr]">
                            <img src="{{ $post->image_url ?: $fallbackImage }}" alt="{{ $post->title }}" class="h-full w-full object-cover opacity-80 transition duration-500 group-hover:scale-105">
                            <div class="flex min-w-0 flex-col justify-center p-4">
                                <span class="w-max rounded bg-blue-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-blue-700">{{ \Illuminate\Support\Str::headline($post->category) }}</span>
                                <h2 class="gist-headline mt-3 line-clamp-3 text-base font-black leading-tight text-slate-950">{{ $post->title }}</h2>
                                <p class="mt-2 text-xs text-slate-500">{{ $post->reading_minutes }} min read</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="mt-10">
                <h2 class="gist-line-title text-2xl text-slate-950">Browse Categories</h2>
                <div class="mt-5 flex gap-3 overflow-x-auto pb-2">
                    @foreach($categories as $category)
                        <a href="{{ route('blog.index', ['category' => $category]) }}" class="flex shrink-0 items-center gap-3 rounded-full border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-800 shadow-sm transition hover:border-blue-500/70">
                            <span class="rounded bg-blue-50 px-2 py-1 text-[10px] text-blue-700">{{ $categoryIcons[$category] ?? 'BG' }}</span>
                            <span>{{ \Illuminate\Support\Str::headline($category) }}</span>
                            <span class="text-xs font-semibold text-slate-400">{{ number_format((int) ($categoryCounts[$category] ?? 0)) }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="mt-8 grid gap-8 lg:grid-cols-[1fr_320px]">
                <div>
                    <h2 class="gist-line-title text-2xl text-slate-950">Latest Articles</h2>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        @forelse($latestPosts as $post)
                            <article class="gist-card overflow-hidden rounded-xl transition hover:border-blue-500/50">
                                <a href="{{ route('blog.show', $post) }}" class="group block">
                                    <div class="relative h-64 overflow-hidden">
                                        <img src="{{ $post->image_url ?: $fallbackImage }}" alt="{{ $post->title }}" class="h-full w-full object-cover opacity-85 transition duration-700 group-hover:scale-105">
                                        <span class="absolute left-4 top-4 rounded-md bg-blue-700 px-3 py-2 text-[11px] font-black uppercase tracking-wider text-white">{{ \Illuminate\Support\Str::headline($post->category) }}</span>
                                    </div>
                                    <div class="p-5">
                                        <h3 class="gist-headline line-clamp-2 text-2xl font-black leading-tight text-slate-950">{{ $post->title }}</h3>
                                        <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 120) }}</p>
                                        <div class="mt-4 flex items-center justify-between gap-3 text-xs text-slate-500">
                                            <span>{{ $post->author?->name ?? 'Cambridge Teacher' }}</span>
                                            <span>{{ $post->reading_minutes }} min read</span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        @empty
                            <div class="gist-card rounded-xl p-8 text-slate-600">More articles will appear here as they are published.</div>
                        @endforelse
                    </div>
                </div>

                <aside class="space-y-6">
                    <section class="gist-card rounded-xl p-5">
                        <h2 class="gist-line-title text-xl text-slate-950">Trending Now</h2>
                        <div class="mt-5 divide-y divide-slate-100">
                            @foreach(($trendingPosts ?? collect())->take(5) as $index => $post)
                                <a href="{{ route('blog.show', $post) }}" class="grid grid-cols-[2rem_1fr_4rem] gap-3 py-4">
                                    <span class="text-2xl font-black text-slate-200">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="min-w-0">
                                        <span class="line-clamp-2 text-sm font-black leading-snug text-slate-800">{{ $post->title }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ \Illuminate\Support\Str::headline($post->category) }} · {{ $post->reading_minutes }} min read</span>
                                    </span>
                                    <img src="{{ $post->image_url ?: $fallbackImage }}" alt="{{ $post->title }}" class="h-14 w-16 rounded-lg object-cover">
                                </a>
                            @endforeach
                        </div>
                    </section>

                    <section class="gist-card rounded-xl p-5">
                        <h2 class="gist-line-title text-xl text-slate-950">Popular Tags</h2>
                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach($categories as $category)
                                <a href="{{ route('blog.index', ['category' => $category]) }}" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-bold text-slate-500 hover:border-blue-500 hover:text-blue-700">#{{ str_replace(' ', '', \Illuminate\Support\Str::headline($category)) }}</a>
                            @endforeach
                        </div>
                    </section>
                </aside>
            </section>
        @else
            <div class="gist-card rounded-2xl p-12 text-center">
                <p class="text-2xl font-black text-slate-950">No published articles yet.</p>
                <p class="mt-2 text-slate-500">Approved teacher articles will appear here.</p>
            </div>
        @endif

        <div class="mt-10 rounded-xl border border-slate-200 bg-white px-5 py-4 text-slate-800 shadow-sm">
            {{ $posts->links() }}
        </div>
    </main>

    <a href="#" class="fixed bottom-5 right-5 z-40 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-700 text-2xl font-black text-white shadow-xl hover:bg-blue-600" aria-label="Back to top">⌃</a>
</div>
@endsection
