<?php

namespace App\Http\Controllers;

use App\Models\GalleryAlbum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $albums = GalleryAlbum::withCount('images')
            ->homepageOrder()
            ->paginate(12);

        return view('admin.gallery.index', compact('albums'));
    }

    public function create()
    {
        return view('admin.gallery.create', [
            'album' => new GalleryAlbum([
                'category' => 'campus life',
                'is_published' => true,
                'sort_order' => 0,
            ]),
            'categories' => GalleryAlbum::categories(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedAlbumData($request);
        $data['slug'] = GalleryAlbum::uniqueSlug($data['title']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')->store('gallery/covers', 'public');
        }

        $album = GalleryAlbum::create($data);
        $this->storeImages($request, $album);

        return redirect()->route('admin.gallery.index')->with('success', 'Gallery album created successfully.');
    }

    public function edit(GalleryAlbum $album)
    {
        $album->load('images');

        return view('admin.gallery.edit', [
            'album' => $album,
            'categories' => GalleryAlbum::categories(),
        ]);
    }

    public function update(Request $request, GalleryAlbum $album)
    {
        $data = $this->validatedAlbumData($request);
        $data['slug'] = GalleryAlbum::uniqueSlug($data['title'], $album);

        if ($request->hasFile('cover_image')) {
            $this->deletePublicFile($album->cover_image_path);
            $data['cover_image_path'] = $request->file('cover_image')->store('gallery/covers', 'public');
        }

        $album->update($data);
        $this->storeImages($request, $album);
        $this->syncImageDetails($request, $album);

        return redirect()->route('admin.gallery.edit', $album)->with('success', 'Gallery album updated successfully.');
    }

    public function destroy(GalleryAlbum $album)
    {
        $album->load('images');
        $this->deletePublicFile($album->cover_image_path);

        foreach ($album->images as $image) {
            $this->deletePublicFile($image->image_path);
        }

        $album->delete();

        return redirect()->route('admin.gallery.index')->with('success', 'Gallery album deleted successfully.');
    }

    private function validatedAlbumData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:' . implode(',', GalleryAlbum::categories())],
            'description' => ['nullable', 'string', 'max:1000'],
            'event_date' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
            'image_captions' => ['nullable', 'array'],
            'image_orders' => ['nullable', 'array'],
            'featured_image_id' => ['nullable', 'integer'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        unset($data['cover_image'], $data['images'], $data['image_captions'], $data['image_orders'], $data['featured_image_id']);

        return $data;
    }

    private function storeImages(Request $request, GalleryAlbum $album): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        foreach ($request->file('images') as $index => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $album->images()->create([
                'image_path' => $file->store('gallery/images', 'public'),
                'caption' => $request->input("new_image_captions.{$index}"),
                'sort_order' => (int) $request->input("new_image_orders.{$index}", 0),
                'is_featured' => false,
            ]);
        }
    }

    private function syncImageDetails(Request $request, GalleryAlbum $album): void
    {
        $album->load('images');
        $featuredImageId = (int) $request->input('featured_image_id');

        foreach ($album->images as $image) {
            $image->update([
                'caption' => $request->input("image_captions.{$image->id}", $image->caption),
                'sort_order' => (int) $request->input("image_orders.{$image->id}", $image->sort_order),
                'is_featured' => $featuredImageId === (int) $image->id,
            ]);
        }
    }

    private function deletePublicFile(?string $path): void
    {
        if (!$path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete(ltrim(str_replace('\\', '/', $path), '/'));
    }
}
