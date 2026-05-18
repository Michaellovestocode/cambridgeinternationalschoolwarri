<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    private const IMAGE_MAX_KB = 10240;

    public function publicIndex(Request $request)
    {
        $query = BlogPost::with('author')->published();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        return view('blog.index', [
            'posts' => $query->publicOrder()->paginate(9)->withQueryString(),
            'categories' => BlogPost::categories(),
            'filters' => [
                'category' => $request->string('category')->value(''),
                'search' => $request->string('search')->value(''),
            ],
        ]);
    }

    public function publicShow(BlogPost $post)
    {
        abort_unless($post->status === BlogPost::STATUS_PUBLISHED && (!$post->published_at || $post->published_at->lte(now())), 404);

        $post->load('author');

        $relatedPosts = BlogPost::with('author')
            ->published()
            ->whereKeyNot($post->id)
            ->where('category', $post->category)
            ->publicOrder()
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'relatedPosts'));
    }

    public function teacherIndex(Request $request)
    {
        $query = BlogPost::where('author_id', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('teacher.blog.index', [
            'posts' => $query->latest()->paginate(10)->withQueryString(),
            'statuses' => BlogPost::statuses(),
            'filters' => [
                'status' => $request->string('status')->value(''),
            ],
        ]);
    }

    public function teacherCreate()
    {
        return view('teacher.blog.create', [
            'post' => new BlogPost([
                'category' => 'education',
                'status' => BlogPost::STATUS_PENDING,
            ]),
            'categories' => BlogPost::categories(),
        ]);
    }

    public function teacherStore(Request $request)
    {
        $this->logImageUploadFailure($request, 'teacherStore');

        $data = $this->validatedTeacherData($request);
        $data['author_id'] = Auth::id();
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['status'] = $request->input('action') === 'draft' ? BlogPost::STATUS_DRAFT : BlogPost::STATUS_PENDING;
        $data['submitted_at'] = $data['status'] === BlogPost::STATUS_PENDING ? now() : null;
        $data['image_path'] = $this->storeImage($request);

        BlogPost::create($data);

        return redirect()
            ->route('teacher.blog.index')
            ->with('success', $data['status'] === BlogPost::STATUS_DRAFT ? 'Blog draft saved.' : 'Blog post submitted for admin approval.');
    }

    public function teacherEdit(BlogPost $post)
    {
        $this->authorizeTeacherPost($post);

        return view('teacher.blog.edit', [
            'post' => $post,
            'categories' => BlogPost::categories(),
        ]);
    }

    public function teacherUpdate(Request $request, BlogPost $post)
    {
        $this->authorizeTeacherPost($post);

        abort_if($post->status === BlogPost::STATUS_PUBLISHED, 403, 'Published posts can only be edited by admin.');

        $this->logImageUploadFailure($request, 'teacherUpdate');

        $data = $this->validatedTeacherData($request);
        $data['status'] = $request->input('action') === 'draft' ? BlogPost::STATUS_DRAFT : BlogPost::STATUS_PENDING;
        $data['submitted_at'] = $data['status'] === BlogPost::STATUS_PENDING ? now() : $post->submitted_at;
        $data['admin_note'] = null;

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request);
        }

        if ($post->title !== $data['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }

        $post->update($data);

        return redirect()
            ->route('teacher.blog.index')
            ->with('success', $data['status'] === BlogPost::STATUS_DRAFT ? 'Blog draft updated.' : 'Blog post submitted for admin approval.');
    }

    public function adminIndex(Request $request)
    {
        $query = BlogPost::with(['author', 'reviewer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        return view('admin.blog.index', [
            'posts' => $query->latest()->paginate(12)->withQueryString(),
            'statuses' => BlogPost::statuses(),
            'teachers' => $this->editorialAuthors(),
            'statusCounts' => BlogPost::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'filters' => [
                'status' => $request->string('status')->value(''),
                'author_id' => $request->string('author_id')->value(''),
                'search' => $request->string('search')->value(''),
            ],
        ]);
    }

    public function adminEdit(BlogPost $post)
    {
        $post->load(['author', 'reviewer']);

        return view('admin.blog.edit', [
            'post' => $post,
            'categories' => BlogPost::categories(),
            'teachers' => $this->editorialAuthors(),
        ]);
    }

    public function adminCreate()
    {
        return view('admin.blog.create', [
            'post' => new BlogPost([
                'author_id' => Auth::id(),
                'category' => 'education',
                'status' => BlogPost::STATUS_DRAFT,
            ]),
            'categories' => BlogPost::categories(),
            'authors' => $this->editorialAuthors(),
        ]);
    }

    public function adminStore(Request $request)
    {
        $this->logImageUploadFailure($request, 'adminStore');

        $data = $this->validatedAdminData($request);
        $status = $data['status'];

        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['reviewed_by'] = Auth::id();
        $data['submitted_at'] = in_array($status, [BlogPost::STATUS_PENDING, BlogPost::STATUS_PUBLISHED], true) ? now() : null;
        $data['image_path'] = $this->storeImage($request);

        if ($status === BlogPost::STATUS_PUBLISHED && !$request->filled('published_at')) {
            $data['published_at'] = now();
        }

        if ($status !== BlogPost::STATUS_PUBLISHED) {
            $data['published_at'] = null;
        }

        $post = BlogPost::create($data);

        return redirect()
            ->route('admin.blog.edit', $post)
            ->with('success', 'Blog post created successfully.');
    }

    public function adminUpdate(Request $request, BlogPost $post)
    {
        $this->logImageUploadFailure($request, 'adminUpdate');

        $data = $this->validatedAdminData($request);
        $status = $data['status'];
        $data['reviewed_by'] = Auth::id();

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request);
        }

        if ($post->title !== $data['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }

        if ($status === BlogPost::STATUS_PUBLISHED && !$request->filled('published_at')) {
            $data['published_at'] = now();
        }

        if ($status !== BlogPost::STATUS_PUBLISHED) {
            $data['published_at'] = null;
        }

        if ($status === BlogPost::STATUS_PENDING && !$post->submitted_at) {
            $data['submitted_at'] = now();
        }

        $post->update($data);

        return redirect()
            ->route('admin.blog.index')
            ->with('success', 'Blog post updated successfully.');
    }

    public function adminDestroy(BlogPost $post)
    {
        $post->delete();

        return redirect()
            ->route('admin.blog.index')
            ->with('success', 'Blog post deleted successfully.');
    }

    private function validatedTeacherData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:' . implode(',', BlogPost::categories())],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'min:50'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:' . self::IMAGE_MAX_KB],
        ], $this->imageValidationMessages());
    }

    private function validatedAdminData(Request $request): array
    {
        return $request->validate([
            'author_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:' . implode(',', BlogPost::categories())],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'min:50'],
            'status' => ['required', 'string', 'in:' . implode(',', BlogPost::statuses())],
            'published_at' => ['nullable', 'date'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:' . self::IMAGE_MAX_KB],
        ], $this->imageValidationMessages());
    }

    private function imageValidationMessages(): array
    {
        return [
            'image.uploaded' => 'The cover image is larger than the server upload limit. Please use an image under 10MB or increase PHP upload_max_filesize and post_max_size on the server.',
            'image.max' => 'The cover image must not be larger than 10MB.',
            'image.image' => 'The cover file must be a valid image.',
            'image.mimes' => 'The cover image must be a JPEG, PNG, GIF, or WEBP file.',
        ];
    }

    private function logImageUploadFailure(Request $request, string $context): void
    {
        if (!$request->files->has('image')) {
            return;
        }

        $file = $request->file('image');

        if (!$file || $file->isValid()) {
            return;
        }

        Log::warning('Blog cover image upload failed before validation.', [
            'context' => $context,
            'error_code' => $file->getError(),
            'error_message' => $file->getErrorMessage(),
            'client_name' => $file->getClientOriginalName(),
            'client_mime' => $file->getClientMimeType(),
            'php_upload_max_filesize' => ini_get('upload_max_filesize'),
            'php_post_max_size' => ini_get('post_max_size'),
            'php_upload_tmp_dir' => ini_get('upload_tmp_dir'),
            'php_file_uploads' => ini_get('file_uploads'),
        ]);
    }

    private function storeImage(Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('blog', 'public');
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'blog-post';
        $slug = $base;
        $count = 2;

        while (BlogPost::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base . '-' . $count;
            $count++;
        }

        return $slug;
    }

    private function authorizeTeacherPost(BlogPost $post): void
    {
        abort_unless($post->author_id === Auth::id(), 403);
    }

    private function editorialAuthors()
    {
        return User::query()
            ->whereIn('role', ['teacher', 'admin', 'blog_manager'])
            ->orWhere('can_manage_blog', true)
            ->orderBy('name')
            ->get();
    }
}
