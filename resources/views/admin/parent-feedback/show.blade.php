@extends('layouts.app')

@section('title', 'Review Parent Feedback')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[.16em] text-blue-700">Parent relations · {{ $feedback->reference_code }}</p>
            <h1 class="mt-1 text-3xl font-black text-slate-900">Review feedback</h1>
            <p class="mt-1 text-sm text-slate-500">Submitted {{ $feedback->created_at->format('j F Y, g:i A') }}</p>
        </div>
        <a href="{{ route('admin.parent-feedback.index') }}" class="rounded-xl bg-white px-4 py-3 text-center text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">Back to feedback inbox</a>
    </div>

    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="grid gap-6 lg:grid-cols-[1.35fr_.8fr]">
        <section class="space-y-5">
            <article class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-800">{{ ucfirst($feedback->category) }}</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ ucfirst(str_replace('_', ' ', $feedback->status)) }}</span>
                    @if($feedback->is_anonymous)<span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Anonymous</span>@endif
                </div>
                <h2 class="mt-5 text-xl font-black text-slate-900">{{ $feedback->is_anonymous ? 'Anonymous parent' : $feedback->parent_name }}</h2>
                <div class="mt-5 rounded-2xl bg-slate-50 p-5 text-sm leading-7 text-slate-700 sm:p-6">
                    <p class="whitespace-pre-line">{{ $feedback->message }}</p>
                </div>
            </article>

            <article class="rounded-3xl border border-amber-200 bg-amber-50/70 p-5 shadow-sm sm:p-7">
                <h2 class="text-lg font-black text-amber-950">Private admin notes</h2>
                <p class="mt-1 text-xs leading-5 text-amber-900">These notes are visible only to administrators and are not sent to the parent.</p>
                <form action="{{ route('admin.parent-feedback.update', $feedback) }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="status" class="mb-1 block text-sm font-bold text-slate-800">Follow-up status</label>
                        <select id="status" name="status" required class="w-full rounded-xl border border-amber-200 bg-white px-4 py-3 text-sm">
                            @foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status', $feedback->status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label for="admin_notes" class="mb-1 block text-sm font-bold text-slate-800">Follow-up notes</label>
                        <textarea id="admin_notes" name="admin_notes" rows="7" maxlength="5000" class="w-full rounded-xl border border-amber-200 bg-white px-4 py-3 text-sm leading-6" placeholder="Record actions taken, staff follow-up, or resolution details…">{{ old('admin_notes', $feedback->admin_notes) }}</textarea>
                    </div>
                    @if($feedback->reviewedBy)<p class="text-xs text-amber-900">Last updated by {{ $feedback->reviewedBy->name }}@if($feedback->resolved_at) · Resolved {{ $feedback->resolved_at->format('j M Y, g:i A') }}@endif</p>@endif
                    <button class="w-full rounded-xl bg-amber-700 px-5 py-3 text-sm font-black text-white hover:bg-amber-800 sm:w-auto">Save follow-up</button>
                </form>
            </article>
        </section>

        <aside class="h-fit rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-black text-slate-900">Parent contact</h2>
            @if($feedback->is_anonymous)
                <p class="mt-3 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">This parent chose to submit anonymously. Contact details were not stored.</p>
            @else
                <dl class="mt-4 space-y-4 text-sm">
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Name</dt><dd class="mt-1 font-semibold text-slate-800">{{ $feedback->parent_name }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Email</dt><dd class="mt-1 break-words font-semibold text-slate-800">@if($feedback->email)<a class="text-blue-700 hover:underline" href="mailto:{{ $feedback->email }}">{{ $feedback->email }}</a>@else Not provided @endif</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Phone</dt><dd class="mt-1 font-semibold text-slate-800">@if($feedback->phone)<a class="text-blue-700 hover:underline" href="tel:{{ $feedback->phone }}">{{ $feedback->phone }}</a>@else Not provided @endif</dd></div>
                </dl>
            @endif
            <div class="mt-6 border-t border-slate-100 pt-5">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Record details</p>
                <p class="mt-2 text-sm text-slate-700">Reference: <span class="font-black">{{ $feedback->reference_code }}</span></p>
                <p class="mt-1 text-sm text-slate-700">Received: {{ $feedback->created_at->format('j M Y, g:i A') }}</p>
            </div>
        </aside>
    </div>
</div>
@endsection
