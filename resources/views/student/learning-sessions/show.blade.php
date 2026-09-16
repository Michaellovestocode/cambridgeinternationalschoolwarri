@extends('layouts.app')

@section('title', $learningSession->title)

@push('styles')
<style>
    @media (max-width: 767px) {
        .student-session-hero {
            padding: 1.5rem;
            border-radius: 1.25rem;
        }

        .student-session-hero h1 {
            font-size: 2rem;
            line-height: 2.5rem;
        }

        .student-question-card {
            padding: 1rem;
            border-radius: 1rem;
        }

        .student-question-options {
            grid-template-columns: 1fr;
        }

        .student-submit-bar {
            justify-content: stretch;
        }

        .student-submit-bar button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="student-session-hero bg-gradient-to-r from-cyan-600 to-emerald-600 text-white p-8">
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <p class="text-cyan-50 font-semibold">{{ $learningSession->subject->name ?? 'Subject' }}</p>
                    <h1 class="text-3xl font-bold mt-2">{{ $learningSession->title }}</h1>
                    <p class="text-cyan-50 mt-2">{{ $learningSession->schoolClass->display_name ?? 'Your class' }} • {{ $learningSession->topic }} • {{ $learningSession->estimated_minutes }} mins</p>
                </div>
                <a href="{{ route('student.learning.index') }}" class="bg-white/15 hover:bg-white/25 px-4 py-2 rounded-lg font-semibold">Back</a>
            </div>
        </div>

        <div class="p-6 md:p-8 space-y-6">
            @if($learningSession->description)
                <p class="text-gray-700 leading-7">{{ $learningSession->description }}</p>
            @endif

            @if($learningSession->learning_goals)
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-5">
                <h2 class="font-bold text-emerald-900 mb-3">Learning Goals</h2>
                <ul class="list-disc list-inside space-y-1 text-emerald-900">
                    @foreach(preg_split('/\r\n|\r|\n/', $learningSession->learning_goals) as $goal)
                        @if(trim($goal) !== '')
                            <li>{{ trim($goal) }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
            @endif

            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-3">Lesson</h2>
                <div class="prose max-w-none text-gray-700 leading-8 whitespace-pre-line">{{ $learningSession->lesson_content ?: 'No lesson content has been added yet.' }}</div>
            </div>

            @if($unreadTeacherReplies > 0)
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                    You have {{ $unreadTeacherReplies }} new teacher {{ $unreadTeacherReplies === 1 ? 'reply' : 'replies' }} in the class discussion.
                </div>
            @endif

            @if($learningSession->attachments->isNotEmpty())
                <div class="rounded-xl border border-sky-100 bg-sky-50 p-5">
                    <h2 class="font-bold text-sky-900">Study Materials</h2>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach($learningSession->attachments as $attachment)
                            <a href="{{ $attachment->url() }}" target="_blank" rel="noopener" class="flex min-w-0 items-center justify-between rounded-xl bg-white p-4 text-sm font-semibold text-sky-800 shadow-sm hover:bg-sky-100">
                                <span class="truncate">{{ $attachment->name }}</span>
                                <span class="ml-3 shrink-0 text-xs text-sky-600">Open</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <h2 class="text-xl font-bold text-gray-900">How are you feeling about this topic?</h2>
            <p class="mt-2 text-sm text-gray-600">Your teacher uses this to know who needs another explanation.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <form action="{{ route('student.learning.feedback', $learningSession) }}" method="POST" class="learning-feedback-form" data-status="understood">
                    @csrf
                    <input type="hidden" name="status" value="understood">
                    <button type="submit" class="feedback-button rounded-xl {{ $feedback === 'understood' ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-800' }} px-4 py-3 text-sm font-bold">I understand</button>
                </form>
                <form action="{{ route('student.learning.feedback', $learningSession) }}" method="POST" class="learning-feedback-form" data-status="needs_help">
                    @csrf
                    <input type="hidden" name="status" value="needs_help">
                    <button type="submit" class="feedback-button rounded-xl {{ $feedback === 'needs_help' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800' }} px-4 py-3 text-sm font-bold">I need help</button>
                </form>
            </div>
            <p id="feedback-message" class="mt-3 hidden text-sm font-semibold text-emerald-700" role="status"></p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Class Discussion</h2>
                    <p class="mt-1 text-sm text-gray-600">Ask a question or help a classmate.</p>
                </div>
                <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-bold text-cyan-800">Shared with class</span>
            </div>
            <form action="{{ route('student.learning.comments.store', $learningSession) }}" method="POST" class="learning-comment-form mt-4">
                @csrf
                <textarea name="body" rows="3" required maxlength="3000" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm" placeholder="What do you understand, or where do you need help?"></textarea>
                <button class="mt-2 rounded-xl bg-cyan-600 px-4 py-3 text-sm font-bold text-white">Post to Class</button>
            </form>
            <div id="learning-comments-list" class="mt-5 space-y-3">
                @forelse($learningSession->comments as $comment)
                    <div data-comment-id="{{ $comment->id }}" class="learning-comment-card rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <button type="button" class="learning-thread-toggle flex w-full items-start justify-between gap-3 text-left" aria-expanded="false">
                            <span class="min-w-0">
                                <span class="block truncate text-xs font-bold text-gray-500">{{ $comment->user->name }} @if(in_array($comment->user->role, ['teacher', 'admin'], true))<span class="ml-1 rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] text-cyan-800">Teacher</span>@endif{{ $comment->is_pinned ? ' · Pinned by teacher' : '' }}</span>
                                <span class="mt-1 block overflow-hidden text-sm text-gray-800" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $comment->body }}</span>
                            </span>
                            <span class="learning-thread-label shrink-0 rounded-full bg-white px-3 py-1 text-xs font-bold text-cyan-700">{{ $comment->replies->count() }} {{ $comment->replies->count() === 1 ? 'reply' : 'replies' }}</span>
                        </button>
                        <div class="learning-thread-content mt-3 hidden border-t border-gray-200 pt-3">
                            <div class="learning-replies space-y-3">
                                @foreach($comment->replies as $reply)
                                    <div class="border-l-2 border-cyan-200 pl-3 text-sm" data-reply-id="{{ $reply->id }}">
                                        <p class="text-xs font-bold text-gray-500">{{ $reply->user->name }} @if(in_array($reply->user->role, ['teacher', 'admin'], true))<span class="ml-1 rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] text-cyan-800">Teacher</span>@endif</p>
                                        <p class="mt-1 whitespace-pre-line text-gray-700">{{ $reply->body }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <form action="{{ route('student.learning.comments.store', $learningSession) }}" method="POST" class="learning-comment-form mt-3 flex flex-col gap-2 sm:flex-row">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                <input name="body" required maxlength="3000" class="min-w-0 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Reply to this discussion">
                                <button type="submit" class="comment-submit rounded-lg bg-gray-900 px-3 py-2 text-xs font-bold text-white sm:shrink-0">Reply</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No discussion yet. Start the conversation.</p>
                @endforelse
            </div>
        </div>
    </div>

@if($practicePendingReview)
<div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900 shadow-lg">
    <h2 class="text-xl font-bold">Task already submitted</h2>
    <p class="mt-2 text-sm">Your answers are waiting for teacher review. They are shown below as read-only while you wait.</p>
</div>
<div class="rounded-2xl bg-white p-6 shadow-lg md:p-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Your Submitted Answers</h2>
            <p class="text-sm text-gray-600">Submitted {{ $latestAttempt->completed_at?->format('j M Y, g:i A') }}</p>
        </div>
        <span class="rounded-full bg-amber-100 px-4 py-2 text-xs font-bold text-amber-800">Awaiting Review</span>
    </div>
    <div class="space-y-5">
        @foreach($learningSession->questions as $question)
            @php($answer = $latestAttempt->answers->firstWhere('learning_question_id', $question->id))
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <p class="font-bold text-gray-900">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                <div class="mt-3 whitespace-pre-line rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-700">{{ $answer?->selected_option ?: 'No answer submitted.' }}</div>
            </div>
        @endforeach
    </div>
</div>
@elseif($practiceLocked)
<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-emerald-900 shadow-lg">
    <h2 class="text-xl font-bold">Practice submitted and graded</h2>
    <p class="mt-2 text-sm">Your teacher has published your result. The practice questions are locked. You can still read the lesson and join the class discussion.</p>
    <a href="{{ route('student.learning.result', $latestAttempt) }}" class="mt-4 inline-block rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white">View Published Result</a>
</div>
@else
<form action="{{ route('student.learning.submit', $learningSession) }}" method="POST" class="space-y-6">
    @csrf
    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Practice Questions</h2>
                <p class="text-gray-600 text-sm">Choose the best answer for each question.</p>
            </div>
            <span class="bg-cyan-100 text-cyan-800 px-4 py-2 rounded-full text-sm font-bold">{{ $learningSession->questions->count() }} questions</span>
        </div>

        <div class="space-y-6">
            @forelse($learningSession->questions as $question)
            <div class="student-question-card border border-gray-200 rounded-xl p-5">
                <p class="font-bold text-gray-900 mb-4">{{ $loop->iteration }}. {{ $question->question_text }}</p>

                @if(!empty($question->options) && is_array($question->options))
                    <div class="student-question-options grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($question->options as $key => $option)
                        <label class="flex items-start gap-3 border rounded-lg px-4 py-3 cursor-pointer hover:bg-cyan-50">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $key }}" class="mt-1">
                            <span><strong>{{ $key }}.</strong> {{ $option }}</span>
                        </label>
                        @endforeach
                    </div>
                @else
                    <textarea name="answers[{{ $question->id }}]" rows="5" class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-cyan-500" placeholder="Write your answer here..."></textarea>
                    <div class="mt-4 rounded-2xl border border-violet-200 bg-violet-50 p-4" data-notepad>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-bold text-violet-900">Optional writing pad</p>
                                <p class="text-xs text-violet-700">Use a stylus, finger, or mouse for workings. Add pages when you need more space.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" data-undo class="rounded-lg bg-white px-3 py-2 text-xs font-bold text-violet-800">Undo</button>
                                <button type="button" data-redo class="rounded-lg bg-white px-3 py-2 text-xs font-bold text-violet-800">Redo</button>
                                <button type="button" data-erase class="rounded-lg bg-white px-3 py-2 text-xs font-bold text-violet-800">Eraser</button>
                                <button type="button" data-clear class="rounded-lg bg-white px-3 py-2 text-xs font-bold text-rose-700">Clear</button>
                                <button type="button" data-add-page class="rounded-lg bg-violet-700 px-3 py-2 text-xs font-bold text-white">Add Page</button>
                            </div>
                        </div>
                        <div class="mt-3 space-y-3" data-pages></div>
                    </div>
                @endif
            </div>
            @empty
            <div class="text-center py-10 text-gray-500">No questions have been added to this session yet.</div>
            @endforelse
        </div>

        @if($learningSession->questions->count() > 0)
        <div class="student-submit-bar mt-8 flex justify-end">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg">
                Submit Practice
            </button>
        </div>
        @endif
    </div>
</form>
@endif
</div>

<style>
    .learning-notepad-canvas { touch-action: none; background: #fff; cursor: crosshair; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-notepad]').forEach(function (notepad) {
            const pages = notepad.querySelector('[data-pages]');
            const addPage = notepad.querySelector('[data-add-page]');
            const undo = notepad.querySelector('[data-undo]');
            const redo = notepad.querySelector('[data-redo]');
            const erase = notepad.querySelector('[data-erase]');
            const clear = notepad.querySelector('[data-clear]');
            let pageNumber = 0;
            let activePage = null;
            let history = [];
            let future = [];

            function snapshot(canvas) {
                return canvas.toDataURL('image/png');
            }

            function restore(canvas, image) {
                const context = canvas.getContext('2d');
                const imageObject = new Image();
                imageObject.onload = function () {
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    context.drawImage(imageObject, 0, 0);
                };
                imageObject.src = image;
            }

            function addNewPage() {
                if (pageNumber >= 12) {
                    addPage.disabled = true;
                    addPage.textContent = 'Maximum 12 pages';
                    return;
                }
                pageNumber++;
                const wrapper = document.createElement('div');
                wrapper.className = 'rounded-xl border border-violet-100 bg-white p-2';
                const canvas = document.createElement('canvas');
                canvas.width = 1400;
                canvas.height = 850;
                canvas.className = 'learning-notepad-canvas h-auto w-full rounded-lg border border-gray-200';
                canvas.name = 'handwriting_pages[]';
                wrapper.appendChild(canvas);
                pages.appendChild(wrapper);
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'handwriting_pages[' + notepad.closest('.student-question-card').querySelector('textarea').name.match(/\d+/)[0] + '][]';
                wrapper.appendChild(hidden);
                const context = canvas.getContext('2d');
                context.lineCap = 'round';
                context.lineJoin = 'round';
                context.lineWidth = 4;
                context.strokeStyle = '#111827';
                let drawing = false;
                let lastPoint = null;
                let pageHistory = [];
                function point(event) {
                    const bounds = canvas.getBoundingClientRect();
                    return { x: (event.clientX - bounds.left) * canvas.width / bounds.width, y: (event.clientY - bounds.top) * canvas.height / bounds.height };
                }
                canvas.addEventListener('pointerdown', function (event) {
                    event.preventDefault();
                    canvas.setPointerCapture(event.pointerId);
                    drawing = true;
                    lastPoint = point(event);
                    pageHistory.push(snapshot(canvas));
                    future = [];
                });
                canvas.addEventListener('pointermove', function (event) {
                    if (!drawing) return;
                    const nextPoint = point(event);
                    context.beginPath();
                    context.moveTo(lastPoint.x, lastPoint.y);
                    context.lineTo(nextPoint.x, nextPoint.y);
                    context.stroke();
                    lastPoint = nextPoint;
                });
                ['pointerup', 'pointercancel', 'pointerleave'].forEach(type => canvas.addEventListener(type, function () {
                    if (!drawing) return;
                    drawing = false;
                    hidden.value = snapshot(canvas);
                }));
                wrapper.addEventListener('pointerdown', () => { activePage = { canvas, context, hidden, pageHistory }; });
                activePage = { canvas, context, hidden, pageHistory };
                if (pageNumber >= 12) {
                    addPage.disabled = true;
                    addPage.textContent = 'Maximum 12 pages';
                }
            }
            undo.addEventListener('click', function () {
                if (!activePage || !activePage.pageHistory.length) return;
                future.push(snapshot(activePage.canvas));
                restore(activePage.canvas, activePage.pageHistory.pop());
                activePage.hidden.value = snapshot(activePage.canvas);
            });
            redo.addEventListener('click', function () {
                if (!activePage || !future.length) return;
                activePage.pageHistory.push(snapshot(activePage.canvas));
                restore(activePage.canvas, future.pop());
                activePage.hidden.value = snapshot(activePage.canvas);
            });
            erase.addEventListener('click', function () {
                if (!activePage) return;
                activePage.context.globalCompositeOperation = activePage.context.globalCompositeOperation === 'destination-out' ? 'source-over' : 'destination-out';
                erase.classList.toggle('bg-amber-200');
            });
            clear.addEventListener('click', function () {
                if (!activePage) return;
                activePage.context.clearRect(0, 0, activePage.canvas.width, activePage.canvas.height);
                activePage.hidden.value = '';
            });
            addPage.addEventListener('click', addNewPage);
            addNewPage();
        });

        document.querySelectorAll('.learning-feedback-form').forEach(function (form) {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                const button = form.querySelector('.feedback-button');
                const message = document.getElementById('feedback-message');
                const buttons = document.querySelectorAll('.feedback-button');
                buttons.forEach((item) => item.disabled = true);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    });

                    if (!response.ok) throw new Error('Feedback could not be saved.');

                    const result = await response.json();
                    buttons.forEach((item) => {
                        item.classList.remove('bg-emerald-700', 'bg-amber-600', 'text-white');
                        item.classList.add(item.form.dataset.status === 'understood' ? 'bg-emerald-50' : 'bg-amber-50');
                        item.classList.add(item.form.dataset.status === 'understood' ? 'text-emerald-800' : 'text-amber-800');
                    });
                    button.classList.remove('bg-emerald-50', 'bg-amber-50', 'text-emerald-800', 'text-amber-800');
                    button.classList.add(form.dataset.status === 'understood' ? 'bg-emerald-700' : 'bg-amber-600', 'text-white');
                    message.textContent = result.message;
                    message.classList.remove('hidden');
                } catch (error) {
                    message.textContent = error.message;
                    message.classList.remove('hidden', 'text-emerald-700');
                    message.classList.add('text-red-700');
                } finally {
                    buttons.forEach((item) => item.disabled = false);
                }
            });
        });

        document.querySelectorAll('.learning-thread-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                const card = toggle.closest('.learning-comment-card');
                const content = card.querySelector('.learning-thread-content');
                const isOpen = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', String(!isOpen));
                content.classList.toggle('hidden', isOpen);
                card.querySelector('.learning-thread-label').textContent = isOpen ? 'View thread' : 'Hide thread';
            });
        });

        document.querySelectorAll('.learning-comment-form').forEach(function (form) {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                const submitButton = form.querySelector('.comment-submit') || form.querySelector('button[type="submit"]');
                const originalText = submitButton ? submitButton.textContent : '';
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Posting...';
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    });

                    if (!response.ok) throw new Error('Comment could not be posted.');

                    const result = await response.json();
                    const comment = result.comment;
                    const bodyField = form.querySelector('[name="body"]');
                    const replyMarkup = document.createElement('div');
                    replyMarkup.className = 'mt-3 border-l-2 border-cyan-200 pl-3 text-sm';
                    replyMarkup.dataset.replyId = comment.id;
                    const replyName = document.createElement('p');
                    replyName.className = 'text-xs font-bold text-gray-500';
                    replyName.textContent = comment.is_teacher ? comment.name + ' · Teacher' : comment.name;
                    const replyBody = document.createElement('p');
                    replyBody.className = 'mt-1 whitespace-pre-line text-gray-700';
                    replyBody.textContent = comment.body;
                    replyMarkup.append(replyName, replyBody);

                    if (comment.parent_id) {
                        const parentCard = document.querySelector('[data-comment-id="' + comment.parent_id + '"]');
                        const parent = parentCard ? parentCard.querySelector('.learning-replies') : null;
                        if (parent) {
                            parent.appendChild(replyMarkup);
                            parentCard.querySelector('.learning-thread-content').classList.remove('hidden');
                            parentCard.querySelector('.learning-thread-toggle').setAttribute('aria-expanded', 'true');
                            parentCard.querySelector('.learning-thread-label').textContent = 'Hide thread';
                        }
                    } else {
                        const commentCard = document.createElement('div');
                        commentCard.dataset.commentId = comment.id;
                        commentCard.className = 'learning-comment-card rounded-xl border border-gray-100 bg-gray-50 p-4';
                        const toggle = document.createElement('button');
                        toggle.type = 'button';
                        toggle.className = 'learning-thread-toggle flex w-full items-start justify-between gap-3 text-left';
                        toggle.setAttribute('aria-expanded', 'true');
                        const summary = document.createElement('span');
                        summary.className = 'min-w-0';
                        const name = document.createElement('span');
                        name.className = 'block truncate text-xs font-bold text-gray-500';
                        name.textContent = comment.is_teacher ? comment.name + ' · Teacher' : comment.name;
                        const text = document.createElement('span');
                        text.className = 'mt-1 block text-sm text-gray-800';
                        text.textContent = comment.body;
                        const label = document.createElement('span');
                        label.className = 'learning-thread-label shrink-0 rounded-full bg-white px-3 py-1 text-xs font-bold text-cyan-700';
                        label.textContent = 'Hide thread';
                        summary.append(name, text);
                        toggle.append(summary, label);
                        const replies = document.createElement('div');
                        replies.className = 'learning-replies space-y-3';
                        const content = document.createElement('div');
                        content.className = 'learning-thread-content mt-3 border-t border-gray-200 pt-3';
                        content.append(replies);
                        commentCard.append(toggle, content);
                        document.getElementById('learning-comments-list').appendChild(commentCard);
                        toggle.addEventListener('click', function () {
                            const isOpen = toggle.getAttribute('aria-expanded') === 'true';
                            toggle.setAttribute('aria-expanded', String(!isOpen));
                            content.classList.toggle('hidden', isOpen);
                            label.textContent = isOpen ? 'View thread' : 'Hide thread';
                        });
                    }
                    if (bodyField) bodyField.value = '';
                } catch (error) {
                    const notice = document.createElement('p');
                    notice.className = 'mt-2 text-sm font-semibold text-red-700';
                    notice.textContent = error.message;
                    form.appendChild(notice);
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                }
            });
        });
    });
</script>
@endsection
