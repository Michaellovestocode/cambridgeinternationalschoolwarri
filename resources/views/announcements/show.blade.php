@extends('layouts.app')

@section('title', $announcement->title)

@section('content')
<article class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-xl">
    <div class="grid lg:grid-cols-[1.05fr_.95fr]">
        <div class="p-6 sm:p-10 lg:p-12">
            <div class="flex flex-wrap items-center gap-2 text-xs font-black uppercase text-slate-500">
                <span class="rounded-lg bg-slate-950 px-3 py-1.5 text-white">{{ $announcement->category_label }}</span>
                <span>{{ $announcement->display_date }}</span>
                @if($announcement->location)
                    <span>/</span>
                    <span>{{ $announcement->location }}</span>
                @endif
            </div>

            <h1 class="mt-5 text-4xl font-black leading-tight text-slate-950 sm:text-6xl">{{ $announcement->title }}</h1>
            <p class="mt-6 text-xl leading-9 text-slate-600">{{ $announcement->summary }}</p>

            @if($announcement->button_url)
                <a href="{{ $announcement->button_url }}" class="mt-8 inline-flex rounded-xl bg-blue-700 px-6 py-3 font-black text-white hover:bg-blue-800">
                    {{ $announcement->button_label ?: 'Open link' }}
                </a>
            @endif
        </div>

        @if($announcement->image_url)
            <img src="{{ $announcement->image_url }}" alt="{{ $announcement->title }}" class="h-80 min-h-[420px] w-full object-cover lg:h-full">
        @else
            <div class="h-80 min-h-[420px] bg-gradient-to-br from-blue-100 via-amber-100 to-emerald-100 lg:h-full"></div>
        @endif
    </div>

    <div class="mx-auto max-w-4xl px-6 py-10 sm:px-8 sm:py-14">
        @if($announcement->body)
            <div class="whitespace-pre-line text-lg leading-9 text-slate-800">{{ $announcement->body }}</div>
        @endif

        @if($announcement->video_embed_url || $announcement->video_file_url)
            <section class="mt-10 overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-lg">
                @if($announcement->video_embed_url)
                    <iframe src="{{ $announcement->video_embed_url }}" title="{{ $announcement->title }} video" class="aspect-video w-full" allowfullscreen></iframe>
                @elseif($announcement->video_file_url)
                    <video src="{{ $announcement->video_file_url }}" class="aspect-video w-full bg-black" controls></video>
                @endif
            </section>
        @endif

        @if($announcement->gallery_image_urls)
            <section class="mt-10">
                <h2 class="text-2xl font-black text-slate-950">Photo Highlights</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach($announcement->gallery_image_urls as $image)
                        <img src="{{ $image }}" alt="{{ $announcement->title }} photo" class="h-72 w-full rounded-2xl object-cover shadow-sm">
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-12 flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-8">
            <a href="{{ url('/#news') }}" class="rounded-xl bg-slate-950 px-5 py-3 font-bold text-white hover:bg-slate-800">Back to News</a>
            <a href="{{ url('/') }}" class="font-bold text-slate-700 hover:underline">Cambridge International School</a>
        </div>
    </div>
</article>

@if($relatedAnnouncements->isNotEmpty())
    <section class="mt-8 rounded-2xl border border-slate-100 bg-white p-6 shadow sm:p-8">
        <h2 class="text-2xl font-black text-slate-950">More {{ $announcement->category_label }} Updates</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-3">
            @foreach($relatedAnnouncements as $related)
                <a href="{{ route('announcements.show', $related) }}" class="overflow-hidden rounded-xl border border-slate-100 transition hover:shadow-lg">
                    @if($related->image_url)
                        <img src="{{ $related->image_url }}" alt="{{ $related->title }}" class="h-36 w-full object-cover">
                    @endif
                    <div class="p-5">
                        <p class="text-xs font-black uppercase text-slate-500">{{ $related->display_date }}</p>
                        <h3 class="mt-2 font-black leading-snug text-slate-950">{{ $related->title }}</h3>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
@endsection
