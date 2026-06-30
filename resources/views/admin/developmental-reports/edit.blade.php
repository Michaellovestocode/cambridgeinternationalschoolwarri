@extends('layouts.app')

@section('title', 'Fill Developmental Report')

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl bg-white p-6 shadow-lg">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-600">Developmental Report</p>
                <h1 class="mt-2 text-2xl font-black text-gray-900">{{ $student->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $student->class?->display_name }} · {{ $report->session->name }} · {{ $report->term->name }}</p>
            </div>
            <a href="{{ route('admin.developmental-reports.index', ['class_id' => $report->class_id, 'session_id' => $report->session_id, 'term_id' => $report->term_id]) }}" class="rounded-2xl bg-gray-100 px-5 py-3 text-sm font-black text-gray-700 hover:bg-gray-200">Back</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.developmental-reports.update', $report) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-3xl bg-white p-6 shadow-lg">
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-black uppercase text-gray-500">Times School Opened</label>
                    <input type="number" min="0" max="250" name="days_school_opened" value="{{ old('days_school_opened', $report->days_school_opened) }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-black uppercase text-gray-500">Times Present</label>
                    <input type="number" min="0" max="250" name="days_present" value="{{ old('days_present', $report->days_present) }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3">
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase text-gray-500">Authority</p>
                    <p class="mt-2 font-black text-gray-900">{{ $report->class?->reportAuthorityTitle() }}</p>
                    <p class="text-sm text-gray-500">Secondary uses Principal. Other sections use Head Teacher.</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach($skillsBySection as $section => $skills)
                <section class="rounded-3xl bg-white p-5 shadow-lg">
                    <h2 class="text-lg font-black text-gray-900">{{ $section }}</h2>
                    <div class="mt-4 space-y-4">
                        @foreach($skills as $skill)
                            <div class="rounded-2xl border border-gray-100 p-4">
                                <p class="mb-3 text-sm font-bold text-gray-800">{{ $skill->name }}</p>
                                <div class="grid grid-cols-5 gap-2">
                                    @foreach($ratingLabels as $rating => $label)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="ratings[{{ $skill->id }}]" value="{{ $rating }}" class="peer sr-only" @checked(old("ratings.$skill->id", $ratings->get($skill->id)) === $rating)>
                                            <span title="{{ $label }}" class="flex h-10 items-center justify-center rounded-xl border border-gray-200 text-xs font-black text-gray-600 peer-checked:border-emerald-600 peer-checked:bg-emerald-600 peer-checked:text-white">{{ $rating }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-lg">
            <div class="grid gap-5 lg:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-black text-gray-700">Class Teacher Remark</label>
                    <textarea name="class_teacher_remark" rows="4" class="w-full rounded-2xl border border-gray-200 px-4 py-3">{{ old('class_teacher_remark', $report->class_teacher_remark) }}</textarea>
                </div>
                @if(auth()->user()->isAdmin())
                    <div>
                        <label class="mb-2 block text-sm font-black text-gray-700">{{ $report->class?->reportAuthorityTitle() }} Remark</label>
                        <textarea name="authority_remark" rows="4" class="w-full rounded-2xl border border-gray-200 px-4 py-3">{{ old('authority_remark', $report->authority_remark) }}</textarea>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button name="submit" value="0" class="rounded-2xl bg-slate-200 px-6 py-3 text-sm font-black text-slate-800 hover:bg-slate-300">Save Draft</button>
            <button name="submit" value="1" class="rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-black text-white hover:bg-emerald-700">Submit Report</button>
            @if($report->exists)
                <a href="{{ route('admin.developmental-reports.show', $report) }}" class="rounded-2xl bg-sky-600 px-6 py-3 text-center text-sm font-black text-white hover:bg-sky-700">Preview</a>
            @endif
        </div>
    </form>
</div>
@endsection
