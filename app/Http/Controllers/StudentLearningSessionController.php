<?php

namespace App\Http\Controllers;

use App\Models\LearningAttempt;
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

        $learningSession->load(['subject', 'schoolClass', 'questions']);

        return view('student.learning-sessions.show', compact('learningSession'));
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
                $selected = strtoupper((string) ($submittedAnswers[$question->id] ?? ''));
                $isCorrect = $selected !== '' && $selected === strtoupper($question->correct_option);

                if ($isCorrect) {
                    $score++;
                }

                $attempt->answers()->create([
                    'learning_question_id' => $question->id,
                    'selected_option' => $selected ?: null,
                    'is_correct' => $isCorrect,
                ]);
            }

            $attempt->update(['score' => $score]);

            return $attempt;
        });

        return redirect()
            ->route('student.learning.result', $attempt)
            ->with('success', 'Learning session submitted. Review your corrections below.');
    }

    public function result(LearningAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id(), 403);

        $attempt->load(['learningSession.subject', 'answers.question']);

        return view('student.learning-sessions.result', compact('attempt'));
    }
}
