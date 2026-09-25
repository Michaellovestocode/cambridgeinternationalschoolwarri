<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Parent Feedback | Cambridge International School Warri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> * { font-family: 'Sora', sans-serif; } </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3">
                <img src="{{ asset('images/schoollogo.jpg') }}" alt="Cambridge International School" class="h-11 w-11 rounded-xl object-cover shadow-sm">
                <span class="min-w-0"><span class="block truncate text-sm font-black text-slate-900 sm:text-base">Cambridge International School</span><span class="block text-xs font-semibold text-slate-500">Warri</span></span>
            </a>
            <a href="{{ url('/') }}" class="shrink-0 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-200">Back to website</a>
        </div>
    </header>

    <main class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 sm:py-12 lg:grid-cols-[.8fr_1.2fr] lg:px-8">
        <section class="h-fit rounded-3xl bg-gradient-to-br from-slate-950 via-blue-900 to-teal-800 p-6 text-white shadow-xl sm:p-8">
            <span class="inline-flex rounded-full bg-white/10 px-3 py-1.5 text-xs font-black uppercase tracking-[.16em] text-blue-100">Parent voice</span>
            <h1 class="mt-5 text-3xl font-black leading-tight sm:text-4xl">Feedback, complaints &amp; suggestions</h1>
            <p class="mt-4 text-sm leading-7 text-blue-50">We welcome honest feedback from our families. Share a concern, suggest an improvement, or tell us what is working well.</p>
            <div class="mt-7 space-y-3">
                <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                    <p class="text-sm font-bold">What happens next</p>
                    <p class="mt-1 text-sm leading-6 text-blue-50">Your message is recorded for the school team and a notification is sent to our office. We review each submission and track its progress.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                    <p class="text-sm font-bold">You can submit anonymously</p>
                    <p class="mt-1 text-sm leading-6 text-blue-50">If you choose anonymity, your name and contact details are not saved. We will not be able to contact you for clarification or an update.</p>
                </div>
                <p class="px-1 text-xs leading-5 text-blue-100">Feedback is reviewed by authorized school administrators. Please do not use this form for emergencies or urgent safeguarding matters; contact the school directly.</p>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-xl sm:p-8">
            @if(session('feedback_reference'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5" role="status">
                    <p class="text-lg font-black text-emerald-900">Thank you. Your feedback has been received.</p>
                    <p class="mt-1 text-sm leading-6 text-emerald-800">The school has saved your submission. Keep this reference for your records:</p>
                    <p class="mt-3 inline-flex rounded-xl bg-white px-4 py-2 text-lg font-black tracking-wide text-emerald-900 ring-1 ring-emerald-200">{{ session('feedback_reference') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                    <p class="font-bold">Please review the form:</p>
                    <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('feedback.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="category" class="mb-1.5 block text-sm font-bold text-slate-800">What would you like to share?</label>
                    <select id="category" name="category" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        <option value="">Choose a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(old('category') === $category)>{{ ucfirst($category) }}</option>
                        @endforeach
                    </select>
                    @error('category')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                </div>

                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 hover:border-blue-300">
                    <input id="is_anonymous" type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous')) class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                    <span><span class="block text-sm font-bold text-slate-900">Submit anonymously</span><span class="mt-1 block text-xs leading-5 text-slate-600">Your name, email, and phone number will not be attached to the feedback.</span></span>
                </label>

                <div id="contact_fields" class="space-y-4 rounded-2xl border border-slate-200 p-4 sm:p-5">
                    <div>
                        <h2 class="text-sm font-black text-slate-900">Your contact details</h2>
                        <p class="mt-1 text-xs text-slate-500">Provide your name and at least one way for us to reach you if you would like a follow-up.</p>
                    </div>
                    <div>
                        <label for="parent_name" class="mb-1 block text-sm font-semibold text-slate-700">Parent / guardian name</label>
                        <input id="parent_name" name="parent_name" value="{{ old('parent_name') }}" maxlength="255" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        @error('parent_name')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email address</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">
                            @error('email')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="mb-1 block text-sm font-semibold text-slate-700">Phone number</label>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="50" autocomplete="tel" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100">
                            @error('phone')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label for="message" class="mb-1.5 block text-sm font-bold text-slate-800">Your message</label>
                    <textarea id="message" name="message" rows="7" required minlength="15" maxlength="5000" class="w-full resize-y rounded-xl border border-slate-300 px-4 py-3 text-sm leading-6 focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100" placeholder="Tell us what happened or what you would like the school to consider…">{{ old('message') }}</textarea>
                    <div class="mt-1 flex justify-between gap-3 text-xs text-slate-500"><span>Please avoid including sensitive details about other students.</span><span id="message_count">0 / 5000</span></div>
                    @error('message')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                    <label for="website">Leave this field blank</label>
                    <input id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <button type="submit" class="flex min-h-12 w-full items-center justify-center rounded-xl bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-lg transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-200">Send feedback securely</button>
                <p class="text-center text-xs leading-5 text-slate-500">Submitting this form sends your feedback to the school administration. It is not an emergency reporting channel.</p>
            </form>
        </section>
    </main>

    <script>
        const anonymousInput = document.getElementById('is_anonymous');
        const contactFields = document.getElementById('contact_fields');
        const contactInputs = contactFields.querySelectorAll('input');
        const messageInput = document.getElementById('message');
        const messageCount = document.getElementById('message_count');

        function updateContactFields() {
            const isAnonymous = anonymousInput.checked;
            contactFields.classList.toggle('opacity-50', isAnonymous);
            contactInputs.forEach((input) => { input.disabled = isAnonymous; });
        }
        function updateMessageCount() {
            messageCount.textContent = `${messageInput.value.length} / 5000`;
        }
        anonymousInput.addEventListener('change', updateContactFields);
        messageInput.addEventListener('input', updateMessageCount);
        updateContactFields();
        updateMessageCount();
    </script>
</body>
</html>
