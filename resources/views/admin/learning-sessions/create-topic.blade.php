@extends('layouts.app')

@section('title', 'Create Learning Topic')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="rounded-2xl bg-gradient-to-r from-emerald-600 to-cyan-700 p-6 text-white shadow-lg sm:p-8">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-100">Learning Hub</p>
        <h1 class="mt-2 text-3xl font-black">Create a Learning Topic</h1>
        <p class="mt-2 max-w-2xl text-emerald-50">Share a teaching note, diagram, or explanation. Students can read it, respond, ask questions, and help one another.</p>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.learning-sessions.store-topic') }}" method="POST" enctype="multipart/form-data" class="space-y-6 rounded-2xl bg-white p-5 shadow-lg sm:p-8">
        @csrf

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="school_class_id" class="mb-1 block text-sm font-semibold text-gray-700">Class</label>
                <select id="school_class_id" name="school_class_id" required class="w-full rounded-xl border border-gray-200 px-4 py-3">
                    <option value="">Choose class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="subject_id" class="mb-1 block text-sm font-semibold text-gray-700">Subject</label>
                <select id="subject_id" name="subject_id" required class="w-full rounded-xl border border-gray-200 px-4 py-3">
                    <option value="">Choose subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="title" class="mb-1 block text-sm font-semibold text-gray-700">Learning title</label>
                <input id="title" type="text" name="title" value="{{ old('title') }}" required maxlength="255" class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="Understanding Fractions">
            </div>
            <div>
                <label for="topic" class="mb-1 block text-sm font-semibold text-gray-700">Topic</label>
                <input id="topic" type="text" name="topic" value="{{ old('topic') }}" required maxlength="255" class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="Fractions and equal parts">
            </div>
        </div>

        <div>
            <label for="description" class="mb-1 block text-sm font-semibold text-gray-700">Short introduction</label>
            <textarea id="description" name="description" rows="3" class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="Tell students what they will explore.">{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="learning_goals" class="mb-1 block text-sm font-semibold text-gray-700">Learning goals</label>
            <textarea id="learning_goals" name="learning_goals" rows="3" class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="One goal per line">{{ old('learning_goals') }}</textarea>
        </div>

        <div>
            <label for="lesson_content" class="mb-1 block text-sm font-semibold text-gray-700">Teaching note</label>
            <textarea id="lesson_content" name="lesson_content" rows="12" required class="w-full rounded-xl border border-gray-200 px-4 py-3" placeholder="Write the explanation, worked example, or reading note here.">{{ old('lesson_content') }}</textarea>
        </div>

        <div>
            <label for="estimated_minutes" class="mb-1 block text-sm font-semibold text-gray-700">Estimated reading time</label>
            <input id="estimated_minutes" type="number" name="estimated_minutes" value="{{ old('estimated_minutes', 20) }}" required min="1" max="300" class="w-full rounded-xl border border-gray-200 px-4 py-3 sm:max-w-xs">
        </div>

        <div class="rounded-xl border border-sky-200 bg-sky-50 p-4">
            <label for="attachment" class="mb-1 block text-sm font-semibold text-sky-900">Upload a diagram or study material <span class="font-normal text-sky-700">(optional)</span></label>
            <input id="attachment" type="file" name="attachment" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp" class="w-full rounded-xl border border-sky-200 bg-white px-3 py-3 text-sm">
            <p class="mt-1 text-xs text-sky-700">Images, PDF, Word, and PowerPoint files up to 10MB.</p>
        </div>

        <div class="flex flex-col gap-4 border-t border-gray-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
            <label class="inline-flex items-center gap-3 text-sm font-semibold text-gray-700">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published')) class="rounded border-gray-300">
                Publish for students now
            </label>
            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.learning-sessions.index') }}" class="rounded-xl bg-gray-100 px-5 py-3 text-center text-sm font-bold text-gray-700">Cancel</a>
                <button class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700">Create Learning Topic</button>
            </div>
        </div>
    </form>
</div>
@endsection
