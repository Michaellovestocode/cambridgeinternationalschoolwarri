@extends('layouts.blog-admin')

@section('title', 'New Blog Post')
@section('page_heading', 'New Post')

@section('content')
<form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
    @csrf

    <section class="overflow-hidden rounded-[2rem] bg-slate-950 text-white shadow-2xl">
        <div class="grid gap-8 lg:grid-cols-[1fr_340px]">
            <div class="p-6 sm:p-8 lg:p-10">
                <div class="inline-flex rounded-full border border-white/10 bg-white/10 px-4 py-2 text-xs font-black uppercase text-emerald-300" style="letter-spacing:.14em;">Compose</div>
                <h1 class="mt-5 max-w-3xl text-4xl font-black leading-tight sm:text-5xl">Write or paste a complete school story.</h1>
                <p class="mt-4 max-w-2xl text-base leading-8 text-white/70">Create a fresh article, attach a cover image, choose the writer, and decide whether it stays as a draft, goes for review, or publishes immediately.</p>
            </div>
            <div class="border-t border-white/10 bg-white/[.06] p-6 sm:p-8 lg:border-l lg:border-t-0">
                <label class="block">
                    <span class="text-xs font-black uppercase text-white/45" style="letter-spacing:.14em;">Status</span>
                    <select name="status" class="mt-3 w-full rounded-2xl border-0 bg-white px-4 py-3 text-sm font-bold text-slate-950 focus:ring-4 focus:ring-emerald-300/40">
                        @foreach($statuses ?? \App\Models\BlogPost::statuses() as $status)
                            <option value="{{ $status }}" @selected(old('status', $post->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    @error('status') <span class="mt-2 block text-sm text-rose-200">{{ $message }}</span> @enderror
                </label>

                <label class="mt-5 block">
                    <span class="text-xs font-black uppercase text-white/45" style="letter-spacing:.14em;">Publish Date</span>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at') }}" class="mt-3 w-full rounded-2xl border-0 bg-white px-4 py-3 text-sm font-bold text-slate-950 focus:ring-4 focus:ring-emerald-300/40">
                    @error('published_at') <span class="mt-2 block text-sm text-rose-200">{{ $message }}</span> @enderror
                </label>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="text-xs font-black uppercase text-slate-400" style="letter-spacing:.12em;">Writer</span>
                    <select name="author_id" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                        @foreach($authors as $author)
                            <option value="{{ $author->id }}" @selected((int) old('author_id', $post->author_id) === $author->id)>{{ $author->name }} · {{ ucfirst(str_replace('_', ' ', $author->role)) }}</option>
                        @endforeach
                    </select>
                    @error('author_id') <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="text-xs font-black uppercase text-slate-400" style="letter-spacing:.12em;">Category</span>
                    <select name="category" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(old('category', $post->category) === $category)>{{ ucfirst(str_replace('-', ' ', $category)) }}</option>
                        @endforeach
                    </select>
                    @error('category') <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span> @enderror
                </label>
            </div>

            <label class="mt-5 block">
                <span class="text-xs font-black uppercase text-slate-400" style="letter-spacing:.12em;">Title</span>
                <input type="text" name="title" value="{{ old('title') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-lg font-black text-slate-950 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Enter article headline">
                @error('title') <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span> @enderror
            </label>

            <label class="mt-5 block">
                <span class="text-xs font-black uppercase text-slate-400" style="letter-spacing:.12em;">Short Summary</span>
                <textarea name="excerpt" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm leading-7 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500" placeholder="A short introduction for the blog card and search result.">{{ old('excerpt') }}</textarea>
                @error('excerpt') <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span> @enderror
            </label>

            <label class="mt-5 block">
                <span class="text-xs font-black uppercase text-slate-400" style="letter-spacing:.12em;">Article Body</span>
                <textarea name="body" rows="20" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-4 text-base leading-8 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Paste or write the full blog article here.">{{ old('body') }}</textarea>
                @error('body') <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span> @enderror
            </label>
        </section>

        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">Cover Image</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Upload a clear school photo for the article card and public blog page.</p>
                <input type="file" name="image" accept="image/*" class="mt-5 w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">
                @error('image') <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span> @enderror
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">Editorial Note</h2>
                <textarea name="admin_note" rows="6" class="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm leading-7 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500" placeholder="Optional private note for this post.">{{ old('admin_note') }}</textarea>
                @error('admin_note') <span class="mt-2 block text-sm text-rose-600">{{ $message }}</span> @enderror
            </section>

            <div class="sticky top-28 rounded-[2rem] bg-slate-950 p-5 text-white shadow-xl">
                <p class="text-sm leading-6 text-white/65">Save as draft while writing, set to pending for editorial review, or publish when the article is ready.</p>
                <button type="submit" class="mt-5 w-full rounded-2xl bg-emerald-400 px-5 py-3 text-sm font-black text-slate-950 hover:bg-emerald-300">Save Blog Post</button>
                <a href="{{ route('admin.blog.index') }}" class="mt-3 block rounded-2xl border border-white/10 px-5 py-3 text-center text-sm font-bold text-white/75 hover:bg-white/10">Cancel</a>
            </div>
        </aside>
    </div>
</form>
@endsection
