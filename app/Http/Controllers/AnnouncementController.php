<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function __construct(private MessageNotificationService $messageNotificationService)
    {
        $this->middleware('blog.studio')->except('show');
    }

    public function index(Request $request)
    {
        $query = Announcement::query();

        if ($request->filled('status')) {
            $query->where('is_published', $request->status === 'published');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('ticker_text', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $announcements = $query
            ->homepageOrder()
            ->paginate(12)
            ->withQueryString();

        return view('admin.announcements.index', [
            'announcements' => $announcements,
            'categories' => Announcement::categories(),
            'filters' => [
                'search' => $request->string('search')->value(''),
                'category' => $request->string('category')->value(''),
                'status' => $request->string('status')->value(''),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.announcements.create', [
            'announcement' => new Announcement([
                'category' => Announcement::CATEGORY_ANNOUNCEMENT,
                'is_published' => true,
                'show_in_ticker' => true,
                'send_to_parent_dashboard' => false,
            ]),
            'categories' => Announcement::categories(),
        ]);
    }

    public function show(Announcement $announcement)
    {
        abort_unless(
            $announcement->is_published
                && (!$announcement->published_at || $announcement->published_at->lte(now()))
                && (!$announcement->expires_at || $announcement->expires_at->gte(now())),
            404
        );

        $relatedAnnouncements = Announcement::published()
            ->whereKeyNot($announcement->id)
            ->where('category', $announcement->category)
            ->homepageOrder()
            ->take(3)
            ->get();

        return view('announcements.show', compact('announcement', 'relatedAnnouncements'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['image_path'] = $this->storeImage($request);
        $data['gallery_images'] = $this->storeGalleryImages($request);

        $announcement = Announcement::create($data);
        $this->deliverToParentsIfRequested($announcement);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Update published successfully.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', [
            'announcement' => $announcement,
            'categories' => Announcement::categories(),
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $this->validatedData($request, $announcement);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request);
        }

        if ($request->hasFile('gallery_images')) {
            $data['gallery_images'] = array_values(array_merge(
                $announcement->gallery_images ?? [],
                $this->storeGalleryImages($request)
            ));
        }

        $announcement->update($data);
        $this->deliverToParentsIfRequested($announcement->fresh());

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Update saved successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->deleteStoredMedia($announcement);
        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Update deleted successfully.');
    }

    private function validatedData(Request $request, ?Announcement $announcement = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:' . implode(',', Announcement::categories())],
            'summary' => ['required', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:5000'],
            'ticker_text' => ['nullable', 'string', 'max:255'],
            'button_label' => ['nullable', 'string', 'max:50'],
            'button_url' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
            'gallery_images' => ['nullable', 'array', 'max:8'],
            'gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
            'video_url' => ['nullable', 'url', 'max:1000'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:published_at'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_pinned'] = $request->boolean('is_pinned');
        $validated['show_in_ticker'] = $request->boolean('show_in_ticker');
        $validated['send_to_parent_dashboard'] = Auth::user()?->isAdmin()
            ? $request->boolean('send_to_parent_dashboard')
            : (bool) ($announcement?->send_to_parent_dashboard ?? false);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if (!$validated['ticker_text']) {
            $validated['ticker_text'] = $validated['title'];
        }

        return $validated;
    }

    private function storeImage(Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('announcements', 'public');
    }

    private function storeGalleryImages(Request $request): array
    {
        if (!$request->hasFile('gallery_images')) {
            return [];
        }

        return collect($request->file('gallery_images'))
            ->filter(fn ($file) => $file && $file->isValid())
            ->map(fn ($file) => $file->store('announcements/gallery', 'public'))
            ->values()
            ->all();
    }

    private function deleteStoredMedia(Announcement $announcement): void
    {
        $paths = collect([
            $announcement->image_path,
        ])->merge($announcement->gallery_images ?? []);

        $paths->each(fn ($path) => $this->deletePublicFile($path));
    }

    private function deletePublicFile(?string $path): void
    {
        if (!$path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (!str_starts_with($path, 'announcements/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function deliverToParentsIfRequested(Announcement $announcement): void
    {
        if (!$announcement->is_published || !$announcement->send_to_parent_dashboard || $announcement->parent_messages_sent_at) {
            return;
        }

        if ($announcement->published_at && $announcement->published_at->isFuture()) {
            return;
        }

        $parents = User::where('role', 'parent')->get();

        if ($parents->isEmpty()) {
            return;
        }

        $batchId = (string) Str::uuid();
        $subject = 'Announcement: ' . $announcement->title;
        $body = $this->announcementMessageBody($announcement);

        foreach ($parents as $parent) {
            $message = Message::create([
                'batch_id' => $batchId,
                'announcement_id' => $announcement->id,
                'sender_id' => Auth::id(),
                'recipient_id' => $parent->id,
                'subject' => $subject,
                'body' => $body,
            ]);

            $message->loadMissing(['sender', 'recipient']);
            $this->messageNotificationService->send($message);
        }

        $announcement->forceFill([
            'parent_messages_sent_at' => now(),
        ])->save();
    }

    private function announcementMessageBody(Announcement $announcement): string
    {
        $parts = [
            $announcement->summary,
            $announcement->body,
        ];

        if ($announcement->event_date) {
            $parts[] = 'Date: ' . $announcement->event_date->format('F j, Y');
        }

        if ($announcement->location) {
            $parts[] = 'Location: ' . $announcement->location;
        }

        if ($announcement->button_url) {
            $label = $announcement->button_label ?: 'Open link';
            $parts[] = $label . ': ' . $announcement->button_url;
        }

        return collect($parts)
            ->filter(fn ($part) => filled($part))
            ->implode("\n\n");
    }
}
