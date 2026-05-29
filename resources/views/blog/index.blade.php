@extends('layouts.app')

@section('title', 'Cambridge Magazine')
@section('hideAuthNav', 'true')

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
        overflow-x: hidden;
    }

    .gist-wrap {
        width: min(1200px, 100%);
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

    .magazine-image {
        aspect-ratio: 16 / 10;
        height: auto;
        background: linear-gradient(135deg, #dbeafe, #f8fafc);
    }

    .school-accent {
        background: linear-gradient(90deg, #2563eb, #facc15 55%, #16a34a);
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

    @media (max-width: 767px) {
        .gist-wrap {
            padding-inline: 1rem;
        }

        .gist-logo {
            font-size: .78rem;
            line-height: 1;
            letter-spacing: .005em;
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

        .blog-mobile-muted {
            display: none;
        }

        .mobile-feature-card {
            min-height: 23rem;
        }

        .mobile-feature-title {
            font-size: 1.65rem;
            line-height: 1.08;
        }

        .magazine-image {
            aspect-ratio: 4 / 3;
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
    $postCollection = $posts->getCollection();
    $featuredPost = $postCollection->first();
    $sidePosts = $postCollection->skip(1)->take(3);
    $latestPosts = $postCollection->count() > 4
        ? $postCollection->skip(4)->take(3)
        : ($postCollection->count() > 1 ? $postCollection->skip(1)->take(3) : $postCollection->take(1));
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
            <div class="flex h-full shrink-0 items-center bg-slate-950 px-4 text-[10px] uppercase tracking-[.24em] text-amber-300">Magazine</div>
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

            <nav class="hidden items-center gap-2 xl:flex">
                <a href="{{ route('blog.index') }}" class="rounded-lg px-4 py-3 text-sm font-bold {{ $filters['category'] ? 'text-slate-500 hover:text-slate-950' : 'bg-blue-50 text-blue-700' }}">Home</a>
                @foreach($categories as $category)
                    <a href="{{ route('blog.index', ['category' => $category]) }}" class="rounded-lg px-3 py-3 text-xs font-bold {{ $filters['category'] === $category ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:text-slate-950' }}">{{ \Illuminate\Support\Str::headline($category) }}</a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('blog.index') }}" class="hidden w-52 items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 lg:flex">
                @if($filters['category'])
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                @endif
                <span class="mr-2 text-slate-400">⌕</span>
                <input name="search" value="{{ $filters['search'] }}" class="w-full bg-transparent text-sm text-slate-800 outline-none placeholder:text-slate-400" placeholder="Search blog...">
            </form>

            <div class="flex shrink-0 items-center gap-2 xl:hidden">
                @auth
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="rounded-full bg-rose-600 px-4 py-2 text-xs font-black text-white shadow-sm">Logout</button>
                    </form>
                @endauth
                <button type="button" class="grid h-11 w-11 place-items-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm" onclick="document.getElementById('mobileBlogSearch').classList.toggle('hidden')" aria-label="Search">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z"/>
                    </svg>
                </button>
                <button type="button" class="grid h-11 w-11 place-items-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm" onclick="toggleBlogMenu(true)" aria-label="Menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
            </div>
            @auth
                <form action="{{ route('logout') }}" method="POST" class="hidden xl:block">
                    @csrf
                    <button class="rounded-lg bg-rose-600 px-4 py-3 text-sm font-black text-white shadow-sm hover:bg-rose-700">Logout</button>
                </form>
            @endauth
        </div>

        <div id="mobileBlogSearch" class="hidden border-t border-slate-200 px-4 py-3 xl:hidden">
            <form method="GET" action="{{ route('blog.index') }}" class="flex items-center rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                @if($filters['category'])
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                @endif
                <input name="search" value="{{ $filters['search'] }}" class="w-full bg-transparent text-base text-slate-800 outline-none placeholder:text-slate-400" placeholder="Search articles">
                <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-black text-white">Go</button>
            </form>
        </div>

    </header>

    <div id="mobileBlogNav" class="blog-mobile-drawer fixed inset-0 z-[10000] bg-slate-950/45 backdrop-blur-sm xl:hidden" onclick="if (event.target.id === 'mobileBlogNav') toggleBlogMenu(false)">
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

                <div class="p-5">
                    <p class="mb-4 text-xs font-black uppercase tracking-[.18em] text-slate-500">Explore</p>
                    <div class="grid gap-3">
                        <a href="{{ route('blog.index') }}" class="rounded-2xl border border-slate-200 bg-blue-50 px-4 py-4 text-sm font-black text-blue-700">Home</a>
                        @foreach($categories as $category)
                            <a href="{{ route('blog.index', ['category' => $category]) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm font-black text-slate-700">{{ \Illuminate\Support\Str::headline($category) }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    <main class="gist-wrap py-8 md:py-10">
        @if($featuredPost)
            <section class="grid gap-5 lg:grid-cols-[1.9fr_.85fr]">
                <a href="{{ route('blog.show', $featuredPost) }}" class="mobile-feature-card group relative min-h-[27rem] overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 shadow-xl md:min-h-[31rem]">
                    <img src="{{ $featuredPost->image_url ?: $fallbackImage }}" alt="{{ $featuredPost->title }}" class="absolute inset-0 h-full w-full object-cover opacity-80 transition duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/35 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-5 md:p-8">
                        <span class="inline-flex rounded-md bg-blue-700 px-3 py-2 text-[11px] font-black uppercase tracking-wider text-white">{{ $featuredPost->is_featured ? 'Featured' : \Illuminate\Support\Str::headline($featuredPost->category) }}</span>
                        <h1 class="mobile-feature-title gist-headline mt-5 max-w-4xl text-3xl font-black leading-[1.03] text-white md:text-5xl">{{ $featuredPost->title }}</h1>
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
                            <img src="{{ $post->image_url ?: $fallbackImage }}" alt="{{ $post->title }}" loading="lazy" class="h-full w-full object-cover opacity-80 transition duration-500 group-hover:scale-105">
                            <div class="flex min-w-0 flex-col justify-center p-4">
                                <span class="w-max rounded bg-blue-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-blue-700">{{ \Illuminate\Support\Str::headline($post->category) }}</span>
                                <h2 class="gist-headline mt-3 line-clamp-3 text-base font-black leading-tight text-slate-950">{{ $post->title }}</h2>
                                <p class="mt-2 text-xs text-slate-500"><span class="blog-mobile-muted">{{ $post->author?->name ?? 'Cambridge Teacher' }} • </span>{{ $post->reading_minutes }} min read</p>
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
                                    <div class="magazine-image relative overflow-hidden">
                                        <img src="{{ $post->image_url ?: $fallbackImage }}" alt="{{ $post->title }}" loading="lazy" class="h-full w-full object-cover opacity-85 transition duration-700 group-hover:scale-105">
                                        <span class="absolute left-4 top-4 rounded-md bg-blue-700 px-3 py-2 text-[11px] font-black uppercase tracking-wider text-white">{{ \Illuminate\Support\Str::headline($post->category) }}</span>
                                    </div>
                                    <div class="p-5">
                                        <h3 class="gist-headline line-clamp-2 text-2xl font-black leading-tight text-slate-950">{{ $post->title }}</h3>
                                        <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 120) }}</p>
                                        <div class="mt-4 flex items-center justify-between gap-3 text-xs text-slate-500">
                                            <span>{{ $post->author?->name ?? 'Cambridge Teacher' }}</span>
                                            <span>{{ $post->reading_minutes }} min read</span>
                                        </div>
                                        <div class="school-accent mt-5 h-1 w-16 rounded-full"></div>
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
                                    <img src="{{ $post->image_url ?: $fallbackImage }}" alt="{{ $post->title }}" loading="lazy" class="h-14 w-16 rounded-lg object-cover">
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
            <div class="gist-card overflow-hidden rounded-3xl text-center">
                <div class="bg-gradient-to-r from-blue-700 to-blue-500 px-6 py-10 text-white">
                    <p class="text-xs font-black uppercase tracking-[.18em] text-white/70">Cambridge Magazine</p>
                    <p class="mt-3 text-3xl font-black">Stories are coming soon.</p>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-white/75">Approved teacher articles, school updates, study tips, and classroom stories will appear here once published.</p>
                </div>
                <div class="grid gap-3 p-5 sm:grid-cols-3">
                    @foreach($categories as $category)
                        <a href="{{ route('blog.index', ['category' => $category]) }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 hover:border-blue-400 hover:text-blue-700">{{ \Illuminate\Support\Str::headline($category) }}</a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-10 rounded-xl border border-slate-200 bg-white px-5 py-4 text-slate-800 shadow-sm">
            {{ $posts->links() }}
        </div>
    </main>

    <a href="#" class="fixed bottom-5 right-5 z-40 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-700 text-2xl font-black text-white shadow-xl hover:bg-blue-600" aria-label="Back to top">⌃</a>
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
</script>
@endsection
