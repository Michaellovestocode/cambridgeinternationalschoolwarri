@extends('layouts.blog-admin')

@section('title', 'Homepage Notice')
@section('page_heading', 'Homepage Notice')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_.75fr]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-600">Hero Placard</p>
                <h1 class="mt-2 text-2xl font-black text-slate-950">Edit Admission Notice</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">This controls the small notice button above the homepage hero headline.</p>
            </div>

            <form method="POST" action="{{ route('admin.homepage-notice.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm">
                    <input type="checkbox" name="homepage_notice_enabled" value="1" @checked(old('homepage_notice_enabled', $settings->homepage_notice_enabled)) class="mt-1 rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    <span>
                        <span class="block font-black text-slate-900">Show notice on homepage</span>
                        <span class="mt-1 block text-slate-500">Turn this off when admissions or urgent notices should be hidden.</span>
                    </span>
                </label>

                <div>
                    <label for="homepage_notice_label" class="block text-sm font-bold text-slate-700">Small Label</label>
                    <input id="homepage_notice_label" name="homepage_notice_label" value="{{ old('homepage_notice_label', $settings->homepage_notice_label ?: 'Admissions Notice') }}" maxlength="80" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100">
                    @error('homepage_notice_label')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="homepage_notice_text" class="block text-sm font-bold text-slate-700">Main Notice Text</label>
                    <input id="homepage_notice_text" name="homepage_notice_text" value="{{ old('homepage_notice_text', $settings->homepage_notice_text ?: '2026/2027 admission still ongoing') }}" maxlength="140" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100">
                    @error('homepage_notice_text')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="homepage_notice_url" class="block text-sm font-bold text-slate-700">Button Link</label>
                    <input id="homepage_notice_url" name="homepage_notice_url" value="{{ old('homepage_notice_url', $settings->homepage_notice_url ?: route('apply.create', [], false)) }}" maxlength="255" placeholder="/apply" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100">
                    <p class="mt-2 text-xs text-slate-500">Use a local path like /apply or a full website link.</p>
                    @error('homepage_notice_url')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="rounded-2xl bg-slate-950 px-6 py-3 text-sm font-black text-white shadow-lg hover:bg-slate-800">Save Notice</button>
                    <a href="{{ url('/') }}" target="_blank" class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-black text-slate-700 hover:bg-slate-50">Preview Homepage</a>
                </div>
            </form>
        </section>

        <aside class="rounded-3xl border border-amber-100 bg-amber-50 p-6">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">Current Preview</p>
            <div class="mt-5 inline-flex max-w-full items-center gap-2 rounded-2xl border border-amber-200 bg-white px-4 py-3 text-left text-sm font-black text-slate-900 shadow-xl shadow-amber-100/60 sm:rounded-full sm:px-5">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-xs font-black text-white shadow-md">AD</span>
                <span class="min-w-0">
                    <span class="block truncate text-[11px] uppercase tracking-[0.18em] text-amber-700">{{ $settings->homepage_notice_label ?: 'Admissions Notice' }}</span>
                    <span class="block text-sm leading-snug sm:text-base">{{ $settings->homepage_notice_text ?: '2026/2027 admission still ongoing' }}</span>
                </span>
            </div>
            <p class="mt-5 text-sm leading-6 text-amber-900/75">After saving, reload the homepage to see the updated placard above “Education for Excellence.”</p>
        </aside>
    </div>
@endsection
