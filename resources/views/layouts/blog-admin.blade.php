<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon-48x48.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <title>@yield('title', 'Blog Studio')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; }
        body {
            background:
                radial-gradient(circle at top left, rgba(99, 102, 241, .14), transparent 28rem),
                radial-gradient(circle at top right, rgba(245, 158, 11, .13), transparent 26rem),
                #f7f8fc;
        }
        .studio-glass {
            background: rgba(255, 255, 255, .82);
            backdrop-filter: blur(18px);
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen text-slate-900">
    <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
        <aside class="bg-slate-950 text-white">
            <div class="sticky top-0 flex min-h-screen flex-col px-5 py-6">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/schoollogo.jpg') }}" alt="School Logo" class="h-12 w-12 rounded-2xl object-cover ring-2 ring-white/10">
                    <div>
                        <p class="text-xs font-black uppercase text-amber-300" style="letter-spacing:.14em;">Cambridge</p>
                        <h1 class="text-xl font-black">Blog Studio</h1>
                    </div>
                </div>

                <div class="mt-6 rounded-3xl border border-white/10 bg-white/[.06] p-4">
                    <p class="text-xs font-black uppercase text-white/45" style="letter-spacing:.14em;">Workspace</p>
                    <p class="mt-2 text-sm leading-6 text-white/75">Review, polish, schedule, and publish teacher-written stories for the public school blog.</p>
                </div>

                <nav class="mt-8 space-y-2">
                    <a href="{{ route('admin.blog.index') }}" class="flex items-center justify-between rounded-2xl bg-white px-4 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-black/10">
                        <span>Moderation Queue</span>
                        <span class="text-xs text-slate-400">Studio</span>
                    </a>
                    <a href="{{ route('admin.blog.create') }}" class="block rounded-2xl px-4 py-3 text-sm font-bold text-white/75 hover:bg-white/10 hover:text-white">New Post</a>
                    <a href="{{ route('blog.index') }}" target="_blank" class="block rounded-2xl px-4 py-3 text-sm font-bold text-white/75 hover:bg-white/10 hover:text-white">Public Blog</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.blog-managers.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-bold text-white/75 hover:bg-white/10 hover:text-white">Blog Managers</a>
                        <a href="{{ route('admin.dashboard') }}" class="block rounded-2xl px-4 py-3 text-sm font-bold text-white/75 hover:bg-white/10 hover:text-white">Main Admin</a>
                    @endif
                </nav>

                <div class="mt-auto rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs font-bold uppercase text-white/40">Signed in</p>
                    <p class="mt-1 text-sm font-bold">{{ auth()->user()->name }}</p>
                    <form action="{{ route('logout') }}" method="POST" class="mt-4">
                        @csrf
                        <button class="w-full rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-rose-600">Logout</button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="min-w-0">
            <header class="sticky top-0 z-20 border-b border-slate-200/80 studio-glass">
                <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8">
                    <div>
                        <p class="text-xs font-black uppercase text-indigo-600" style="letter-spacing:.16em;">Publishing</p>
                        <h2 class="text-2xl font-black">@yield('page_heading', 'Blog Studio')</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.blog.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Posts</a>
                        <a href="{{ url('/') }}" target="_blank" class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800">Visit Site</a>
                    </div>
                </div>
            </header>

            <div class="px-5 py-6 sm:px-8">
                @if(session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-bold text-rose-800">{{ session('error') }}</div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
