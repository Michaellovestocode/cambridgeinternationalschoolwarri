@extends('layouts.app')

@section('title', 'Edit Learning Session')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 rounded-lg bg-white p-5 shadow sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Learning Hub</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $learningSession->title }}</h1>
            <p class="text-sm text-gray-600">{{ $learningSession->schoolClass->display_name ?? 'No class' }} • {{ $learningSession->subject->name ?? 'N/A' }} • {{ $learningSession->topic }}</p>
        </div>
        <a href="{{ route('admin.learning-sessions.index') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-center text-sm font-semibold text-gray-700">Back to Learning Hub</a>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('admin.learning-sessions.submissions', $learningSession) }}" class="rounded-lg bg-cyan-600 px-4 py-2 text-center text-sm font-semibold text-white">View Submissions</a>
            @if(! $learningSession->is_published)
                <form action="{{ route('admin.learning-sessions.publish', $learningSession) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-center text-sm font-semibold text-white">Publish for Students</button>
                </form>
            @else
                <span class="rounded-lg bg-emerald-100 px-4 py-2 text-center text-sm font-semibold text-emerald-800">Published for Students</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
            <h2 class="text-xl font-bold text-gray-900">Activity Settings</h2>
            <p class="mt-1 text-sm text-gray-500">Save changes as a draft while you add questions, then publish when the activity is ready.</p>
            @include('admin.learning-sessions.partials.form', [
                'action' => route('admin.learning-sessions.update', $learningSession),
                'method' => 'PUT',
                'learningSession' => $learningSession,
                'subjects' => $subjects,
                'classes' => $classes,
            ])
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="text-xl font-bold text-gray-900">Study Materials</h2>
            <p class="mt-1 text-sm text-gray-500">Share diagrams, PDFs, presentations, or reference images with the class.</p>
            <form action="{{ route('admin.learning-sessions.attachments.store', $learningSession) }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <label class="mb-1 block text-sm font-semibold text-gray-700">Upload file</label>
                    <input type="file" name="attachment" required accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp" class="w-full rounded-lg border px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-gray-500">Maximum 10MB.</p>
                </div>
                <button class="rounded-lg bg-sky-600 px-4 py-3 text-sm font-bold text-white">Upload Material</button>
            </form>
            <div class="mt-5 space-y-2">
                @forelse($learningSession->attachments as $attachment)
                    <a href="{{ $attachment->url() }}" target="_blank" rel="noopener" class="flex min-w-0 items-center justify-between rounded-lg bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-800">
                        <span class="truncate">{{ $attachment->name }}</span><span class="ml-3 text-xs">Open</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">No study materials uploaded yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="text-xl font-bold text-gray-900">Class Discussion</h2>
            <div class="mt-3 flex gap-3 text-xs font-bold">
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-800">{{ $feedbackCounts->get('understood', 0) }} understand</span>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800">{{ $feedbackCounts->get('needs_help', 0) }} need help</span>
            </div>
            <p class="mt-1 text-sm text-gray-500">Reply to questions, pin useful explanations, or hide inappropriate comments.</p>
            <form action="{{ route('admin.learning-sessions.comments.store', $learningSession) }}" method="POST" class="mt-4">
                @csrf
                <textarea name="body" rows="3" required maxlength="3000" class="w-full rounded-lg border px-4 py-3 text-sm" placeholder="Post a teacher clarification..."></textarea>
                <button class="mt-2 rounded-lg bg-cyan-600 px-4 py-3 text-sm font-bold text-white">Post Clarification</button>
            </form>
            <div class="mt-5 space-y-3">
                @forelse($learningSession->comments as $comment)
                    <div class="rounded-lg {{ $comment->is_hidden ? 'bg-red-50 opacity-70' : 'bg-gray-50' }} p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-gray-500">{{ $comment->user->name }}{{ $comment->is_pinned ? ' · Pinned' : '' }}</p>
                                <p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $comment->body }}</p>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <form action="{{ route('admin.learning-comments.moderate', $comment) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="pin">
                                    <button class="text-xs font-bold text-amber-700">{{ $comment->is_pinned ? 'Unpin' : 'Pin' }}</button>
                                </form>
                                <form action="{{ route('admin.learning-comments.moderate', $comment) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="{{ $comment->is_hidden ? 'show' : 'hide' }}">
                                    <button class="text-xs font-bold text-red-700">{{ $comment->is_hidden ? 'Show' : 'Hide' }}</button>
                                </form>
                            </div>
                        </div>
                        @foreach($comment->replies as $reply)
                            <p class="mt-3 border-l-2 border-cyan-200 pl-3 text-sm text-gray-700"><strong>{{ $reply->user->name }}:</strong> {{ $reply->body }}</p>
                        @endforeach
                        <form action="{{ route('admin.learning-sessions.comments.store', $learningSession) }}" method="POST" class="mt-3 flex flex-col gap-2 sm:flex-row">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                            <input name="body" required maxlength="3000" class="min-w-0 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Reply to this question">
                            <button class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-bold text-white sm:shrink-0">Reply</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No student discussion yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Add Practice Question</h2>
            <p class="text-sm text-gray-500 mb-4">Add as many questions as you need before publishing the task.</p>

            <form action="{{ route('admin.learning-sessions.questions.store', $learningSession) }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="learning-question-form">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Question Type</label>
                    <select name="question_type" id="question_type" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                        <option value="objective" @selected(old('question_type', $learningSession->assessment_format === 'theory' ? 'theory' : 'objective') === 'objective')>Objective / MCQ</option>
                        <option value="theory" @selected(old('question_type', $learningSession->assessment_format === 'theory' ? 'theory' : 'objective') === 'theory')>Theory / Written</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Question Text *</label>
                    @include('admin.exams.partials.rich-question-editor', [
                        'name' => 'question_text',
                        'value' => old('question_text'),
                        'placeholder' => 'Enter your question here'
                    ])
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Marks *</label>
                    <input type="number" name="marks" value="{{ old('marks', 1) }}" min="0.01" step="0.01" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reference Image <span class="text-gray-500">(Optional)</span></label>
                    <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-cyan-500">
                    <p class="text-xs text-gray-500 mt-1">Upload an image showing what students should design or reference. Max 5MB (JPG, PNG, GIF, WEBP)</p>
                </div>

                <div id="objective-options" class="space-y-4">
                    <label class="block text-sm font-medium text-gray-700">Options *</label>
                    <div class="space-y-2">
                        @foreach(['A', 'B', 'C', 'D'] as $letter)
                            <div class="flex gap-2">
                                <span class="font-bold text-gray-700 w-8">{{ $letter }}.</span>
                                <input type="text" name="options[{{ $letter }}]" value="{{ old('options.' . $letter) }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg" placeholder="Option {{ $letter }}">
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answer *</label>
                        <select name="correct_option" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">-- Select Correct Answer --</option>
                            @foreach(['A', 'B', 'C', 'D'] as $option)
                                <option value="{{ $option }}" @selected(old('correct_option') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="theory-options" class="hidden rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    Theory questions do not need multiple-choice options. Students will answer in written form.
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Order</label>
                        <input type="number" min="0" name="order" value="{{ old('order', $learningSession->questions->count() + 1) }}" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Explanation <span class="text-gray-500">(Optional)</span></label>
                        <textarea name="explanation" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">{{ old('explanation') }}</textarea>
                    </div>
                </div>

                <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-3 rounded-lg font-bold">
                    Add Question
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Question Bank</h2>
            <div class="space-y-4">
                @forelse($learningSession->questions as $question)
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between gap-4">
                        <p class="font-semibold text-gray-900">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                        <form action="{{ route('admin.learning-sessions.questions.destroy', $question) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-sm" onclick="return confirm('Delete this question?')">Delete</button>
                        </form>
                    </div>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                        @foreach($question->options as $key => $option)
                            <div class="{{ $key === $question->correct_option ? 'bg-green-50 text-green-800' : 'bg-gray-50 text-gray-700' }} rounded px-3 py-2">
                                <strong>{{ $key }}.</strong> {{ $option }}
                            </div>
                        @endforeach
                    </div>
                    @if($question->explanation)
                        <p class="mt-3 text-sm text-gray-600"><strong>Explanation:</strong> {{ $question->explanation }}</p>
                    @endif
                </div>
                @empty
                <p class="text-gray-500 text-center py-10">No questions added yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const questionType = document.getElementById('question_type');
        const objectiveOptions = document.getElementById('objective-options');
        const theoryOptions = document.getElementById('theory-options');

        function syncQuestionType() {
            if (!questionType || !objectiveOptions || !theoryOptions) return;

            const isTheory = questionType.value === 'theory';
            objectiveOptions.classList.toggle('hidden', isTheory);
            theoryOptions.classList.toggle('hidden', !isTheory);
        }

        if (questionType) {
            questionType.addEventListener('change', syncQuestionType);
            syncQuestionType();
        }
    });
</script>
@endsection
