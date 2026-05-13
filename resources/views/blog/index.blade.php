@extends('layouts.app')

@section('title', 'Education Blog')

@section('content')
@php
    $featuredPost = $posts->first();
    $remainingPosts = $posts->getCollection()->skip(1);
@endphp

<div class="space-y-8">
    <header class="relative overflow-hidden rounded-2xl bg-gray-950 text-white shadow-2xl">
        <div class="absolute inset-0 opacity-20">
            <img src="{{ $featuredPost?->image_url ?: asset('images/school life1.jpg') }}" alt="" class="h-full w-full object-cover">
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-gray-950 via-gray-950/95 to-gray-950/45"></div>

        <div class="relative border-b border-white/10 px-5 sm:px-8 py-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="nav-logo-mark w-14 h-14 bg-gradient-to-br from-blue-600 via-yellow-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg transform rotate-3 shrink-0">
                    <span class="text-white font-black text-2xl -rotate-1"><img src="{{ asset('images/schoollogo.jpg') }}" alt="Vice Principal" class="w-full h-full object-cover rounded-2xl"></span>
                </div>
                <div>
                    <p class="text-xs font-black uppercase text-amber-300" style="letter-spacing:.16em;">Cambridge Bulletin</p>
                    <p class="text-sm text-white/70">Teacher-led education features</p>
                </div>
            </div>
            <div class="text-sm text-white/70">{{ now()->format('l, F j, Y') }}</div>
        </div>

        <div class="relative grid lg:grid-cols-[1.08fr_.92fr] gap-8 px-5 sm:px-8 py-8 sm:py-12 items-end">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/15 px-4 py-2 text-xs font-black uppercase text-white/80" style="letter-spacing:.12em;">
                    School Publication
                </div>
                <h1 class="mt-5 text-4xl sm:text-5xl lg:text-6xl font-black leading-tight">Ideas that help children learn better.</h1>
                <p class="mt-5 max-w-2xl text-base sm:text-lg text-white/75 leading-8">Clear, practical writing from Cambridge teachers on study habits, character, exams, parenting, and everyday classroom growth.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="#top-stories" class="inline-flex items-center justify-center rounded-lg bg-amber-400 px-5 py-3 font-black text-gray-950 hover:bg-amber-300">Top Stories</a>
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-lg border border-white/20 px-5 py-3 font-bold text-white hover:bg-white/10">Back to Home</a>
                </div>
            </div>

            @if($featuredPost)
                <a href="{{ route('blog.show', $featuredPost) }}" class="hidden lg:block rounded-2xl overflow-hidden border border-white/15 bg-white/10 shadow-2xl group">
                    <img src="{{ $featuredPost->image_url }}" alt="{{ $featuredPost->title }}" class="h-72 w-full object-cover group-hover:scale-105 transition duration-500">
                    <div class="p-5">
                        <p class="text-xs font-black uppercase text-amber-300">{{ \Illuminate\Support\Str::headline($featuredPost->category) }}</p>
                        <h2 class="mt-2 text-2xl font-black leading-tight">{{ $featuredPost->title }}</h2>
                        <p class="mt-2 text-sm text-white/60">{{ $featuredPost->display_date }} / {{ $featuredPost->reading_minutes }} min read</p>
                    </div>
                </a>
            @endif
        </div>
    </header>

    <section class="bg-white rounded-2xl shadow border border-gray-100 p-4 sm:p-5">
        <form method="GET" action="{{ route('blog.index') }}" class="grid grid-cols-1 md:grid-cols-[1fr_220px_auto_auto] gap-3">
            <input name="search" value="{{ $filters['search'] }}" class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-gray-900" placeholder="Search education articles">
            <select name="category" class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-gray-900">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ \Illuminate\Support\Str::headline($category) }}</option>
                @endforeach
            </select>
            <button class="bg-gray-950 text-white px-6 py-3 rounded-lg font-bold hover:bg-gray-800">Search</button>
            <a href="{{ route('blog.index') }}" class="border border-gray-200 text-gray-700 px-6 py-3 rounded-lg font-bold text-center hover:bg-gray-50">Reset</a>
        </form>
    </section>

    @if($featuredPost)
        <section id="top-stories" class="grid lg:grid-cols-[1.55fr_.9fr] gap-6 scroll-mt-24">
            <article class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <a href="{{ route('blog.show', $featuredPost) }}" class="block group">
                    <div class="relative">
                        @if($featuredPost->image_url)
                            <img src="{{ $featuredPost->image_url }}" alt="{{ $featuredPost->title }}" class="w-full h-80 sm:h-[470px] object-cover">
                        @else
                            <div class="h-80 sm:h-[470px] bg-gradient-to-br from-slate-100 to-gray-300"></div>
                        @endif
                        <div class="absolute left-5 top-5 bg-white text-gray-950 px-4 py-2 rounded-lg text-xs font-black uppercase shadow">{{ \Illuminate\Support\Str::headline($featuredPost->category) }}</div>
                    </div>
                    <div class="p-6 sm:p-8">
                        <p class="text-sm font-bold text-gray-500">{{ $featuredPost->display_date }} / {{ $featuredPost->reading_minutes }} min read</p>
                        <h2 class="mt-3 text-3xl sm:text-5xl font-black text-gray-950 leading-tight group-hover:text-blue-700 transition">{{ $featuredPost->title }}</h2>
                        <p class="mt-4 text-lg text-gray-600 leading-8">{{ $featuredPost->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($featuredPost->body), 180) }}</p>
                        <p class="mt-5 text-sm font-bold text-gray-900">By {{ $featuredPost->author?->name ?? 'Cambridge Teacher' }}</p>
                    </div>
                </a>
            </article>

            <aside class="bg-gray-950 text-white rounded-2xl shadow-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-white/10">
                    <p class="text-xs font-black uppercase text-amber-300" style="letter-spacing:.16em;">Latest Insight</p>
                    <h3 class="text-2xl font-black mt-2">For Parents And Teachers</h3>
                </div>
                <div class="divide-y divide-white/10">
                    @foreach($remainingPosts->take(4) as $post)
                        <a href="{{ route('blog.show', $post) }}" class="grid grid-cols-[88px_1fr] gap-4 p-5 hover:bg-white/10 transition">
                            @if($post->image_url)
                                <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="h-20 w-22 rounded-lg object-cover">
                            @else
                                <div class="h-20 w-22 rounded-lg bg-white/10"></div>
                            @endif
                            <div>
                                <p class="text-xs font-bold text-amber-300 uppercase">{{ \Illuminate\Support\Str::headline($post->category) }}</p>
                                <h4 class="font-black leading-snug mt-1">{{ $post->title }}</h4>
                                <p class="text-xs text-white/60 mt-2">{{ $post->display_date }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </aside>
        </section>

        <section class="grid lg:grid-cols-[1fr_280px] gap-6">
            <div class="grid md:grid-cols-2 gap-6">
                @foreach($remainingPosts->skip(4) as $post)
                    <article class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden hover:shadow-xl transition">
                        <a href="{{ route('blog.show', $post) }}" class="block group">
                            @if($post->image_url)
                                <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="w-full h-56 object-cover">
                            @else
                                <div class="h-56 bg-gray-100"></div>
                            @endif
                            <div class="p-6">
                                <div class="flex flex-wrap items-center gap-2 text-xs font-black uppercase text-gray-500">
                                    <span>{{ \Illuminate\Support\Str::headline($post->category) }}</span>
                                    <span>/</span>
                                    <span>{{ $post->reading_minutes }} min read</span>
                                </div>
                                <h3 class="mt-3 text-2xl font-black text-gray-950 leading-tight group-hover:text-blue-700 transition">{{ $post->title }}</h3>
                                <p class="mt-3 text-gray-600 leading-7">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 130) }}</p>
                                <p class="mt-5 text-sm font-bold text-gray-500">{{ $post->display_date }}</p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            <aside class="space-y-5">
                <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">
                    <h3 class="text-lg font-black text-gray-950">Sections</h3>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($categories as $category)
                            <a href="{{ route('blog.index', ['category' => $category]) }}" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-bold hover:bg-gray-950 hover:text-white transition">{{ \Illuminate\Support\Str::headline($category) }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-6">
                    <p class="text-xs font-black uppercase text-amber-700" style="letter-spacing:.14em;">Teacher Writers</p>
                    <h3 class="mt-2 text-xl font-black text-gray-950">Share practical classroom wisdom.</h3>
                    <p class="mt-3 text-sm text-gray-600 leading-6">Approved articles appear here for parents, students, and the wider school community.</p>
                </div>
            </aside>
        </section>
    @else
        <div class="bg-white rounded-2xl shadow p-12 text-center">
            <p class="text-xl font-black text-gray-950">No published articles yet.</p>
            <p class="text-gray-500 mt-2">Approved teacher articles will appear here.</p>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow px-6 py-4">
        {{ $posts->links() }}
    </div>
</div>
@endsection
