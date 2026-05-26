<form method="POST" action="{{ $isEdit ? route('admin.gallery.update', $album) : route('admin.gallery.store') }}" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="grid gap-6 lg:grid-cols-[1.2fr_.8fr]">
        <section class="rounded-3xl bg-white p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-950">Album Details</h2>
            <div class="mt-6 space-y-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700">Title</label>
                    <input name="title" value="{{ old('title', $album->title) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" required>
                    @error('title')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-bold text-slate-700">Category</label>
                        <select name="category" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                            @foreach($categories as $category)
                                <option value="{{ $category }}" @selected(old('category', $album->category) === $category)>{{ ucfirst($category) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700">Event Date</label>
                        <input type="date" name="event_date" value="{{ old('event_date', $album->event_date?->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700">Description</label>
                    <textarea name="description" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('description', $album->description) }}</textarea>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $album->is_published))>
                        <span class="font-bold text-slate-700">Show on website</span>
                    </label>
                    <div>
                        <label class="block text-sm font-bold text-slate-700">Sort Order</label>
                        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $album->sort_order ?? 0) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-950">Cover & Photos</h2>
            <div class="mt-6 space-y-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700">Cover Image</label>
                    <input type="file" name="cover_image" accept="image/*" class="mt-2 w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm">
                    @if($album->cover_image_url)
                        <img src="{{ $album->cover_image_url }}" alt="{{ $album->title }}" class="mt-3 h-32 w-full rounded-2xl object-cover">
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700">Add Photos</label>
                    <input id="galleryImagesInput" type="file" name="images[]" accept="image/*" multiple class="mt-2 w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm">
                    <p class="mt-2 text-xs text-slate-500">You can upload up to 20 photos at once. Each image should be below 4MB.</p>
                    <div id="newGalleryImageDetails" class="mt-4 space-y-3"></div>
                </div>
            </div>
        </section>
    </div>

    @if($isEdit && $album->images->isNotEmpty())
        <section class="rounded-3xl bg-white p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-950">Existing Photos</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach($album->images as $image)
                    <div class="rounded-2xl border border-slate-200 p-3">
                        <img src="{{ $image->image_url }}" alt="{{ $image->caption ?: $album->title }}" class="h-40 w-full rounded-xl object-cover">
                        <div class="mt-3 space-y-3">
                            <input name="image_captions[{{ $image->id }}]" value="{{ old("image_captions.{$image->id}", $image->caption) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Caption">
                            <div class="flex items-center gap-3">
                                <input type="number" name="image_orders[{{ $image->id }}]" value="{{ old("image_orders.{$image->id}", $image->sort_order) }}" class="w-24 rounded-xl border border-slate-200 px-3 py-2 text-sm" min="0">
                                <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                    <input type="radio" name="featured_image_id" value="{{ $image->id }}" @checked(old('featured_image_id', $album->images->firstWhere('is_featured', true)?->id) == $image->id)>
                                    Featured
                                </label>
                            </div>
                            <label class="flex items-center gap-2 rounded-xl bg-rose-50 px-3 py-2 text-sm font-bold text-rose-700">
                                <input type="checkbox" name="delete_image_ids[]" value="{{ $image->id }}">
                                Delete this photo
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="flex flex-wrap justify-end gap-3">
        <a href="{{ route('admin.gallery.index') }}" class="rounded-2xl border border-slate-200 px-6 py-3 font-bold text-slate-700 hover:bg-slate-50">Cancel</a>
        <button class="rounded-2xl bg-indigo-600 px-6 py-3 font-bold text-white hover:bg-indigo-700">
            {{ $isEdit ? 'Save Album' : 'Create Album' }}
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('galleryImagesInput');
        const details = document.getElementById('newGalleryImageDetails');

        if (!input || !details) {
            return;
        }

        input.addEventListener('change', () => {
            details.innerHTML = '';

            Array.from(input.files || []).forEach((file, index) => {
                const row = document.createElement('div');
                row.className = 'rounded-2xl border border-slate-200 bg-white p-3';

                const name = document.createElement('p');
                name.className = 'truncate text-sm font-black text-slate-800';
                name.textContent = file.name;

                const fields = document.createElement('div');
                fields.className = 'mt-3 grid gap-3 sm:grid-cols-[1fr_7rem]';

                const caption = document.createElement('input');
                caption.name = `new_image_captions[${index}]`;
                caption.className = 'rounded-xl border border-slate-200 px-3 py-2 text-sm';
                caption.placeholder = 'Caption';

                const order = document.createElement('input');
                order.type = 'number';
                order.min = '0';
                order.name = `new_image_orders[${index}]`;
                order.value = index;
                order.className = 'rounded-xl border border-slate-200 px-3 py-2 text-sm';
                order.setAttribute('aria-label', 'Sort order');

                fields.append(caption, order);
                row.append(name, fields);
                details.appendChild(row);
            });
        });
    });
</script>
