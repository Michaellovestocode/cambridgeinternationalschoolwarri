@php
    $isEdit = $announcement->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label for="title" class="block text-sm font-semibold text-gray-700">Title</label>
            <input id="title" name="title" type="text" value="{{ old('title', $announcement->title) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500" required>
            @error('title')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2">
            <label for="category" class="block text-sm font-semibold text-gray-700">Category</label>
            <select id="category" name="category" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500" required>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(old('category', $announcement->category) === $category)>{{ ucfirst($category) }}</option>
                @endforeach
            </select>
            @error('category')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2 lg:col-span-2">
            <label for="summary" class="block text-sm font-semibold text-gray-700">Summary</label>
            <textarea id="summary" name="summary" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500" required>{{ old('summary', $announcement->summary) }}</textarea>
            @error('summary')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2 lg:col-span-2">
            <label for="body" class="block text-sm font-semibold text-gray-700">Body</label>
            <textarea id="body" name="body" rows="5" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">{{ old('body', $announcement->body) }}</textarea>
            @error('body')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2">
            <label for="ticker_text" class="block text-sm font-semibold text-gray-700">Ticker Text</label>
            <input id="ticker_text" name="ticker_text" type="text" value="{{ old('ticker_text', $announcement->ticker_text) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            <p class="text-xs text-gray-500">Leave blank to reuse the title in the top ticker.</p>
            @error('ticker_text')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2">
            <label for="location" class="block text-sm font-semibold text-gray-700">Location</label>
            <input id="location" name="location" type="text" value="{{ old('location', $announcement->location) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            @error('location')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2">
            <label for="event_date" class="block text-sm font-semibold text-gray-700">Event Date</label>
            <input id="event_date" name="event_date" type="date" value="{{ old('event_date', optional($announcement->event_date)->format('Y-m-d')) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            @error('event_date')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2">
            <label for="published_at" class="block text-sm font-semibold text-gray-700">Publish At</label>
            <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', optional($announcement->published_at)->format('Y-m-d\\TH:i')) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            @error('published_at')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2">
            <label for="expires_at" class="block text-sm font-semibold text-gray-700">Expires At</label>
            <input id="expires_at" name="expires_at" type="datetime-local" value="{{ old('expires_at', optional($announcement->expires_at)->format('Y-m-d\\TH:i')) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            @error('expires_at')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2">
            <label for="sort_order" class="block text-sm font-semibold text-gray-700">Sort Order</label>
            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $announcement->sort_order ?? 0) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            <p class="text-xs text-gray-500">Lower numbers appear first. Use this to control the order of pinned items on the homepage.</p>
            @error('sort_order')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2">
            <label for="button_label" class="block text-sm font-semibold text-gray-700">Button Label</label>
            <input id="button_label" name="button_label" type="text" value="{{ old('button_label', $announcement->button_label) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            @error('button_label')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2 lg:col-span-2">
            <label for="button_url" class="block text-sm font-semibold text-gray-700">Button URL</label>
            <input id="button_url" name="button_url" type="text" value="{{ old('button_url', $announcement->button_url) }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="/apply or https://...">
            @error('button_url')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-2 lg:col-span-2">
            <label for="image" class="block text-sm font-semibold text-gray-700">Cover Image</label>
            <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.gif,.webp" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            <div id="coverImagePreviewWrap" class="{{ $announcement->image_url ? '' : 'hidden' }} mt-3 space-y-2">
                <img
                    id="coverImagePreview"
                    src="{{ $announcement->image_url }}"
                    alt="{{ $announcement->title ?: 'Cover image preview' }}"
                    class="h-32 w-48 rounded-2xl object-cover border border-gray-200"
                >
                <p id="coverImagePreviewText" class="text-xs text-gray-500">
                    {{ $announcement->image_url ? 'Current cover image' : 'Selected cover image preview' }}
                </p>
            </div>
            @error('image')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 rounded-2xl bg-slate-50 border border-slate-200 p-5">
        <label class="flex items-start gap-3">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $announcement->is_published)) class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span>
                <span class="block font-semibold text-gray-900">Published</span>
                <span class="block text-sm text-gray-500">Show this update on the website.</span>
            </span>
        </label>
        <label class="flex items-start gap-3">
            <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $announcement->is_pinned)) class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span>
                <span class="block font-semibold text-gray-900">Pinned</span>
                <span class="block text-sm text-gray-500">Keep this near the top of the homepage list. When multiple posts are pinned, `Sort Order` decides which one stays first.</span>
            </span>
        </label>
        <label class="flex items-start gap-3">
            <input type="checkbox" name="show_in_ticker" value="1" @checked(old('show_in_ticker', $announcement->show_in_ticker)) class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span>
                <span class="block font-semibold text-gray-900">Show In Ticker</span>
                <span class="block text-sm text-gray-500">Include this update in the moving top banner.</span>
            </span>
        </label>
        <label class="flex items-start gap-3">
            @if($announcement->parent_messages_sent_at)
                <input type="hidden" name="send_to_parent_dashboard" value="1">
            @endif
            <input type="checkbox" name="send_to_parent_dashboard" value="1" @checked(old('send_to_parent_dashboard', $announcement->send_to_parent_dashboard)) @disabled($announcement->parent_messages_sent_at) class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span>
                <span class="block font-semibold text-gray-900">Send To Parents</span>
                <span class="block text-sm text-gray-500">
                    Add this update to parent inboxes and trigger WhatsApp delivery when configured.
                    @if($announcement->parent_messages_sent_at)
                        Sent {{ $announcement->parent_messages_sent_at->format('M j, Y g:i A') }}.
                    @endif
                </span>
            </span>
        </label>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg">
            {{ $isEdit ? 'Save Update' : 'Publish Update' }}
        </button>
        <a href="{{ route('admin.announcements.index') }}" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold">
            Cancel
        </a>
    </div>
</form>

<script>
    document.getElementById('image')?.addEventListener('change', function () {
        const file = this.files?.[0];
        const previewWrap = document.getElementById('coverImagePreviewWrap');
        const preview = document.getElementById('coverImagePreview');
        const previewText = document.getElementById('coverImagePreviewText');

        if (!file || !previewWrap || !preview || !previewText) {
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.alt = file.name;
        previewText.textContent = `New selected image: ${file.name}`;
        previewWrap.classList.remove('hidden');
    });
</script>
