<?php

namespace App\Http\Controllers;

use App\Models\LearningAttempt;
use App\Models\LearningComment;
use App\Models\LearningFeedback;
use App\Models\LearningSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $feedback = LearningFeedback::where('learning_session_id', $learningSession->id)
            ->where('user_id', Auth::id())
            ->value('status');

        return view('student.learning-sessions.show', compact('learningSession', 'feedback'));
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

        LearningComment::create([
            'learning_session_id' => $learningSession->id,
            'user_id' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'body' => $validated['body'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Your comment was posted.',
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

        $learningSession->load('questions');

        $request->validate([
            'answers' => ['array'],
        ]);

        $submittedAnswers = $request->input('answers', []);

        $attempt = DB::transaction(function () use ($learningSession, $submittedAnswers) {
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

                if (($question->correct_option === null || $question->correct_option === '') && $question->question_type === 'theory') {
                    $normalizedSelected = strtolower($selected);
                    $normAnswer = is_string($question->explanation) ? strtolower(trim($question->explanation)) : '';
                    $isCorrect = $selected !== '' && $normalizedSelected === $normAnswer;
                } else {
                    $selected = strtoupper((string) $selected);
                    $isCorrect = $selected !== '' && $selected === strtoupper((string) $question->correct_option);
                }

                if ($isCorrect) {
                    $score++;
                }

                $attempt->answers()->create([
                    'learning_question_id' => $question->id,
                    'selected_option' => $selected !== '' ? $selected : null,
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
