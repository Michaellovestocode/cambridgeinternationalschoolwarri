@extends('layouts.app')

@section('title', 'School Gallery')

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
    }

    .app-shell {
        max-width: none !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .gallery-page {
        min-height: 100vh;
        background: linear-gradient(180deg, #eff6ff 0, #ffffff 24rem, #f8fafc 100%);
        color: #0f172a;
    }

    .gallery-wrap {
        width: min(1180px, 100%);
        margin-inline: auto;
        padding-inline: 1rem;
    }

    .gallery-lightbox {
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease;
    }

    .gallery-lightbox.open {
        opacity: 1;
        pointer-events: auto;
    }
</style>
@endpush

@section('content')
@php
    $fallbackImage = asset('images/school life1.jpg');
    $albumImages = function ($album) use ($fallbackImage) {
        $images = collect($album->images ?? [])
            ->map(fn ($image) => [
                'src' => $image->image_url,
                'caption' => $image->caption ?: $album->title,
            ])
            ->filter(fn ($image) => filled($image['src']))
            ->values();

        if ($images->isEmpty()) {
            $images->push([
                'src' => $album->cover_image_url ?: $fallbackImage,
                'caption' => $album->title,
            ]);
        }

        return $images;
    };
@endphp

<div class="gallery-page">
    <header class="border-b border-slate-200 bg-white/90 shadow-sm backdrop-blur">
        <div class="gallery-wrap flex min-h-20 items-center justify-between gap-4 py-4">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="h-12 w-12 overflow-hidden rounded-2xl bg-white shadow">
                    <img src="{{ asset('images/schoollogo.jpg') }}" alt="Cambridge International School logo" class="h-full w-full object-cover">
                </span>
                <span>
                    <span class="block text-lg font-black text-slate-950">Cambridge Gallery</span>
                    <span class="block text-xs font-bold uppercase tracking-[.14em] text-blue-700">School Life</span>
                </span>
            </a>
            <a href="{{ url('/') }}" class="rounded-xl bg-blue-700 px-4 py-3 text-sm font-black text-white shadow hover:bg-blue-600">Home</a>
        </div>
    </header>

    <main class="gallery-wrap py-10">
        <section class="mb-8">
            <p class="text-xs font-black uppercase tracking-[.18em] text-blue-700">Albums</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl">School Gallery</h1>
            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">Browse published albums from school events, classroom moments, sports, clubs, awards, and everyday learning.</p>
        </section>

        @if($albums->isNotEmpty())
            <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($albums as $album)
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <button type="button" class="group block w-full text-left" onclick='openPublicGallery(@js($albumImages($album)))'>
                            <div class="relative h-64 overflow-hidden bg-slate-100">
                                <img src="{{ $album->cover_image_url ?: $fallbackImage }}" alt="{{ $album->title }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"></div>
                                <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-black uppercase text-blue-700">{{ ucfirst($album->category) }}</span>
                                <span class="absolute bottom-4 left-4 rounded-full bg-slate-950/75 px-3 py-1 text-xs font-bold text-white">{{ $albumImages($album)->count() }} photo{{ $albumImages($album)->count() === 1 ? '' : 's' }}</span>
                            </div>
                            <div class="p-5">
                                <h2 class="text-xl font-black leading-tight text-slate-950">{{ $album->title }}</h2>
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $album->description ?: 'View moments from this school album.' }}</p>
                                @if($album->display_date)
                                    <p class="mt-4 text-xs font-black uppercase tracking-[.12em] text-slate-400">{{ $album->display_date }}</p>
                                @endif
                            </div>
                        </button>
                    </article>
                @endforeach
            </section>

            <div class="mt-8 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                {{ $albums->links() }}
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                <p class="text-2xl font-black text-slate-950">No published albums yet.</p>
                <p class="mt-2 text-slate-500">School albums will appear here once published.</p>
            </div>
        @endif
    </main>
</div>

<div id="publicGalleryLightbox" class="gallery-lightbox fixed inset-0 z-[10000] flex items-center justify-center bg-slate-950/90 p-4" onclick="if (event.target.id === 'publicGalleryLightbox') closePublicGallery()">
    <button type="button" onclick="closePublicGallery()" class="absolute right-5 top-5 rounded-full bg-white/10 px-4 py-3 text-2xl font-black text-white backdrop-blur hover:bg-white/20" aria-label="Close gallery">&times;</button>
    <button type="button" onclick="movePublicGallery(-1, event)" id="publicGalleryPrev" class="absolute left-4 top-1/2 hidden -translate-y-1/2 rounded-full bg-white/10 px-4 py-3 text-3xl text-white backdrop-blur hover:bg-white/20" aria-label="Previous photo">&lsaquo;</button>
    <figure class="m-0 flex max-h-[92vh] max-w-[92vw] flex-col items-center gap-4">
        <img id="publicGalleryImage" src="" alt="Gallery photo" class="max-h-[82vh] max-w-[92vw] rounded-2xl object-contain shadow-2xl">
        <figcaption id="publicGalleryCaption" class="max-w-3xl text-center text-sm font-semibold text-white/85"></figcaption>
    </figure>
    <button type="button" onclick="movePublicGallery(1, event)" id="publicGalleryNext" class="absolute right-4 top-1/2 hidden -translate-y-1/2 rounded-full bg-white/10 px-4 py-3 text-3xl text-white backdrop-blur hover:bg-white/20" aria-label="Next photo">&rsaquo;</button>
</div>

<script>
    let publicGalleryImages = [];
    let publicGalleryIndex = 0;

    function openPublicGallery(images) {
        publicGalleryImages = Array.isArray(images) ? images.filter(image => image && image.src) : [];
        publicGalleryIndex = 0;

        if (!publicGalleryImages.length) {
            return;
        }

        renderPublicGallery();
        document.getElementById('publicGalleryLightbox').classList.add('open');
        document.body.classList.add('overflow-hidden');
    }

    function renderPublicGallery() {
        const image = publicGalleryImages[publicGalleryIndex];
        const hasMultiple = publicGalleryImages.length > 1;

        document.getElementById('publicGalleryImage').src = image.src;
        document.getElementById('publicGalleryImage').alt = image.caption || 'Gallery photo';
        document.getElementById('publicGalleryCaption').textContent = image.caption || '';
        document.getElementById('publicGalleryPrev').classList.toggle('hidden', !hasMultiple);
        document.getElementById('publicGalleryNext').classList.toggle('hidden', !hasMultiple);
    }

    function movePublicGallery(direction, event) {
        event?.stopPropagation();
        publicGalleryIndex = (publicGalleryIndex + direction + publicGalleryImages.length) % publicGalleryImages.length;
        renderPublicGallery();
    }

    function closePublicGallery() {
        document.getElementById('publicGalleryLightbox').classList.remove('open');
        document.body.classList.remove('overflow-hidden');
    }
</script>
@endsection
