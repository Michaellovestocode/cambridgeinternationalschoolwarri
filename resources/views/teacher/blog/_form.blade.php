@php
    $isEdit = $post->exists;
    $canEdit = !$isEdit || $post->status !== \App\Models\BlogPost::STATUS_PUBLISHED;
@endphp

<form method="POST" action="{{ $isEdit ? route('teacher.blog.update', $post) : route('teacher.blog.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    @if(!$canEdit)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-amber-800 font-semibold">
            This article is already published. Please contact admin for any change.
        </div>
    @endif

    @if($post->admin_note)
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5">
            <p class="font-bold text-rose-800">Admin note</p>
            <p class="text-sm text-rose-700 mt-1">{{ $post->admin_note }}</p>
        </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label for="title" class="block text-sm font-semibold text-gray-700">Title</label>
            <input id="title" name="title" value="{{ old('title', $post->title) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500" required @disabled(!$canEdit)>
            @error('title')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2">
            <label for="category" class="block text-sm font-semibold text-gray-700">Category</label>
            <select id="category" name="category" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500" required @disabled(!$canEdit)>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(old('category', $post->category) === $category)>{{ \Illuminate\Support\Str::headline($category) }}</option>
                @endforeach
            </select>
            @error('category')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2 lg:col-span-2">
            <label for="excerpt" class="block text-sm font-semibold text-gray-700">Short Summary</label>
            <textarea id="excerpt" name="excerpt" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500" @disabled(!$canEdit)>{{ old('excerpt', $post->excerpt) }}</textarea>
            @error('excerpt')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2 lg:col-span-2">
            <label for="body" class="block text-sm font-semibold text-gray-700">Article</label>
            <textarea id="body" name="body" rows="14" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500" required @disabled(!$canEdit)>{{ old('body', $post->body) }}</textarea>
            @error('body')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2 lg:col-span-2">
            <label for="image" class="block text-sm font-semibold text-gray-700">Cover Image</label>
            <p class="text-xs text-gray-500">JPEG, PNG, GIF, or WEBP, up to 10MB.</p>
            <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.gif,.webp" class="w-full border border-gray-200 rounded-xl px-4 py-3" @disabled(!$canEdit)>
            @if($post->image_url)
                <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="mt-3 h-32 w-52 rounded-2xl object-cover border border-gray-200">
            @endif
            @error('image')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2 lg:col-span-2">
            <label for="gallery_images" class="block text-sm font-semibold text-gray-700">Article Gallery Images</label>
            <p class="text-xs text-gray-500">Optional extra images that appear inside the blog post.</p>
            <input id="gallery_images" name="gallery_images[]" type="file" accept=".jpg,.jpeg,.png,.gif,.webp" multiple class="w-full border border-gray-200 rounded-xl px-4 py-3" @disabled(!$canEdit)>
            @if($post->gallery_image_urls)
                <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($post->gallery_image_urls as $galleryImage)
                        <img src="{{ $galleryImage }}" alt="{{ $post->title }} gallery image" class="h-24 w-full rounded-xl object-cover border border-gray-200">
                    @endforeach
                </div>
            @endif
            @error('gallery_images')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            @error('gallery_images.*')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        @if($canEdit)
            <button name="action" value="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-semibold shadow">Submit for Approval</button>
            <button name="action" value="draft" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold">Save Draft</button>
        @endif
        <a href="{{ route('teacher.blog.index') }}" class="text-gray-600 hover:underline font-semibold">Cancel</a>
    </div>
</form>
