<?php

namespace App\Http\Controllers;

use App\Models\LearningAttempt;
use App\Models\LearningComment;
use App\Models\LearningCommentRead;
use App\Models\LearningFeedback;
use App\Models\LearningSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentLearningSessionController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        $sessions = LearningSession::published()
            ->where('school_class_id', $student->class_id)
            ->with(['subject', 'schoolClass'])
            ->withCount('questions')
            ->latest()
            ->get();

        $teacherComments = LearningComment::whereIn('learning_session_id', $sessions->pluck('id'))
            ->whereHas('user', fn ($query) => $query->whereIn('role', ['teacher', 'admin']))
            ->whereNotIn('id', LearningCommentRead::where('user_id', Auth::id())->pluck('learning_comment_id'))
            ->get()
            ->groupBy('learning_session_id');
        $sessions->each(fn ($session) => $session->unread_teacher_replies = $teacherComments->get($session->id, collect())->count());

        $latestAttempts = LearningAttempt::where('user_id', Auth::id())
            ->with('learningSession')
            ->latest()
            ->get()
            ->unique('learning_session_id')
            ->keyBy('learning_session_id');

        return view('student.learning-sessions.index', compact('sessions', 'latestAttempts'));
    }

    public function show(LearningSession $learningSession)
    {
        abort_unless(
            $learningSession->is_published && $learningSession->school_class_id === Auth::user()->class_id,
            404
        );

        $learningSession->load(['subject', 'schoolClass', 'questions', 'attachments', 'comments' => fn ($query) => $query->where('is_hidden', false)->whereNull('parent_id')->with(['user', 'replies.user'])]);
        $latestAttempt = LearningAttempt::where('user_id', Auth::id())
            ->where('learning_session_id', $learningSession->id)
            ->with(['answers.question'])
            ->latest()
            ->first();
        $feedback = LearningFeedback::where('learning_session_id', $learningSession->id)
            ->where('user_id', Auth::id())
            ->value('status');
        $teacherCommentIds = LearningComment::where('learning_session_id', $learningSession->id)
            ->whereHas('user', fn ($query) => $query->whereIn('role', ['teacher', 'admin']))
            ->pluck('id');
        $unreadTeacherReplies = $teacherCommentIds->diff(
            LearningCommentRead::where('user_id', Auth::id())
                ->whereIn('learning_comment_id', $teacherCommentIds)
                ->pluck('learning_comment_id')
        )->count();
        foreach ($teacherCommentIds as $commentId) {
            LearningCommentRead::updateOrCreate(
                ['learning_comment_id' => $commentId, 'user_id' => Auth::id()],
                ['read_at' => now()]
            );
        }

        $practiceLocked = $latestAttempt?->is_published && ! $latestAttempt->allow_resubmission;
        $practicePendingReview = $latestAttempt && ! $latestAttempt->is_published && ! $latestAttempt->allow_resubmission;

        return view('student.learning-sessions.show', compact('learningSession', 'feedback', 'unreadTeacherReplies', 'latestAttempt', 'practiceLocked', 'practicePendingReview'));
    }

    public function feedback(Request $request, LearningSession $learningSession)
    {
        $this->ensureStudentSessionAccess($learningSession);
        $validated = $request->validate(['status' => ['required', 'in:understood,needs_help']]);

        LearningFeedback::updateOrCreate(
            ['learning_session_id' => $learningSession->id, 'user_id' => Auth::id()],
            ['status' => $validated['status']]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'status' => $validated['status'],
                'message' => 'Your understanding response was saved.',
            ]);
        }

        return back()->with('success', 'Your understanding response was saved.');
    }

    public function comment(Request $request, LearningSession $learningSession)
    {
        $this->ensureStudentSessionAccess($learningSession);
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
            'parent_id' => ['nullable', 'integer', 'exists:learning_comments,id'],
        ]);

        if (! empty($validated['parent_id']) && ! LearningComment::whereKey($validated['parent_id'])->where('learning_session_id', $learningSession->id)->exists()) {
            abort(422, 'That discussion thread does not belong to this lesson.');
        }

        $comment = LearningComment::create([
            'learning_session_id' => $learningSession->id,
            'user_id' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'body' => $validated['body'],
        ])->load('user');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Your comment was posted.',
                'comment' => [
                    'id' => $comment->id,
                    'parent_id' => $comment->parent_id,
                    'name' => $comment->user->name,
                    'body' => $comment->body,
                    'is_teacher' => in_array($comment->user->role, ['teacher', 'admin'], true),
                ],
            ]);
        }

        return back()->with('success', 'Your comment was posted.');
    }

    public function submit(Request $request, LearningSession $learningSession)
    {
        abort_unless(
            $learningSession->is_published && $learningSession->school_class_id === Auth::user()->class_id,
            404
        );

        $latestAttempt = LearningAttempt::where('user_id', Auth::id())
            ->where('learning_session_id', $learningSession->id)
            ->latest()
            ->first();
        if ($latestAttempt && ! $latestAttempt->allow_resubmission) {
            abort(403, $latestAttempt->is_published
                ? 'This activity has been graded and published. Your teacher has not enabled another submission.'
                : 'This activity has already been submitted and is awaiting teacher review.');
        }

        $learningSession->load('questions');

        $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'string', 'max:10000'],
            'handwriting_pages' => ['nullable', 'array'],
            'handwriting_pages.*' => ['nullable', 'array', 'max:12'],
            'handwriting_pages.*.*' => ['nullable', 'string', 'max:12000000'],
        ]);

        $submittedAnswers = $request->input('answers', []);
        $submittedHandwriting = $request->input('handwriting_pages', []);

        $attempt = DB::transaction(function () use ($learningSession, $submittedAnswers, $submittedHandwriting) {
            $attempt = LearningAttempt::create([
                'user_id' => Auth::id(),
                'learning_session_id' => $learningSession->id,
                'started_at' => now(),
                'completed_at' => now(),
                'total_questions' => $learningSession->questions->count(),
            ]);

            $score = 0;

            foreach ($learningSession->questions as $question) {
                $rawAnswer = $submittedAnswers[$question->id] ?? '';
                $selected = is_string($rawAnswer) ? trim($rawAnswer) : '';
                $pagePaths = [];
                foreach ($submittedHandwriting[$question->id] ?? [] as $page) {
                    if (! is_string($page) || ! preg_match('/^data:image\/(png|jpeg);base64,/', $page, $matches)) {
                        continue;
                    }

                    $binary = base64_decode(substr($page, strpos($page, ',') + 1), true);
                    if ($binary === false || strlen($binary) > 9000000) {
                        continue;
                    }

                    $extension = $matches[1] === 'jpeg' ? 'jpg' : 'png';
                    $path = 'learning-handwriting/' . uniqid('page_', true) . '.' . $extension;
                    Storage::disk('public')->put($path, $binary);
                    $pagePaths[] = $path;
                }

                if (($question->correct_option === null || $question->correct_option === '') && $question->question_type === 'theory') {
                    $normalizedSelected = strtolower($selected);
                    $normAnswer = is_string($question->explanation) ? strtolower(trim($question->explanation)) : '';
                    $isCorrect = $selected !== '' && $normalizedSelected === $normAnswer;
                } else {
                    $selected = strtoupper((string) $selected);
                    $isCorrect = $selected !== '' && $selected === strtoupper((string) $question->correct_option);
                }

                if ($isCorrect && $question->question_type !== 'theory') {
                    $score++;
                }

                $attempt->answers()->create([
                    'learning_question_id' => $question->id,
                    'selected_option' => $selected !== '' ? $selected : null,
                    'handwriting_pages' => $pagePaths ?: null,
                    'is_correct' => $isCorrect,
                ]);
            }

            $attempt->update(['score' => $score]);

            return $attempt;
        });

        return redirect()
            ->route('student.learning.result', $attempt)
            ->with('success', 'Learning session submitted successfully.');
    }

    public function result(LearningAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id(), 403);

        $attempt->load(['learningSession.subject', 'answers.question']);

        return view('student.learning-sessions.result', compact('attempt'));
    }

    private function ensureStudentSessionAccess(LearningSession $learningSession): void
    {
        abort_unless($learningSession->is_published && $learningSession->school_class_id === Auth::user()->class_id, 404);
    }
}
