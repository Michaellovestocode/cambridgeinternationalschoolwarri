<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Answer;
use App\Models\LearningAttempt;
use App\Models\LearningSession;
use App\Models\ReportCard;
use App\Services\CbtReportCardSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = Auth::user();
        
        // Get available exams for student's class
        $availableExams = Exam::whereHas('classes', function($query) use ($student) {
            $query->where('school_classes.id', $student->class_id);
        })
        ->whereDoesntHave('attempts', function ($query) use ($student) {
            $query->where('user_id', $student->id)
                ->whereIn('status', ['in_progress', 'submitted', 'graded']);
        })
        ->where('is_active', true)
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->get();

        // Get completed attempts
        $completedAttempts = ExamAttempt::where('user_id', $student->id)
            ->whereIn('status', ['submitted', 'graded'])
            ->with('exam')
            ->latest()
            ->get();

        // Get in-progress attempts
        $inProgressAttempts = ExamAttempt::where('user_id', $student->id)
            ->where('status', 'in_progress')
            ->with('exam.questions')
            ->get();

        foreach ($inProgressAttempts as $attempt) {
            if ($attempt->hasTimeExpired()) {
                $this->finalizeAttempt($attempt);
            } else {
                $attempt->update(['time_remaining' => $attempt->secondsRemaining()]);
            }
        }

        $inProgressAttempts = $inProgressAttempts->filter->isInProgress();

        $availableLearningSessions = LearningSession::published()
            ->where('school_class_id', $student->class_id)
            ->with(['subject', 'schoolClass'])
            ->withCount('questions')
            ->latest()
            ->take(3)
            ->get();

        $completedLearningAttempts = LearningAttempt::where('user_id', $student->id)
            ->whereNotNull('completed_at')
            ->count();

        $reportCards = ReportCard::with(['session', 'term', 'class'])
            ->where('student_id', $student->id)
            ->published()
            ->feeCleared()
            ->latest()
            ->take(6)
            ->get();

        return view('student.dashboard', compact(
            'availableExams',
            'completedAttempts',
            'inProgressAttempts',
            'availableLearningSessions',
            'completedLearningAttempts',
            'reportCards'
        ));
    }

    public function viewReportCard(ReportCard $reportCard)
    {
        if ($reportCard->student_id !== Auth::id()) {
            abort(403);
        }

        abort_unless($reportCard->isPublished(), 404);
        abort_unless($reportCard->hasFeeClearance(), 403, 'This report card is locked until school fee clearance is approved.');

        $reportCard->loadMissing(['student', 'class', 'session', 'term']);
        $scores = $reportCard->scores();

        return view('student.report-card', compact('reportCard', 'scores'));
    }

    public function startExam($examId)
    {
        $exam = Exam::with('classes')->findOrFail($examId);
        $student = Auth::user();

        if (!$exam->isAvailable() || !$exam->classes->contains('id', $student->class_id)) {
            return redirect()->route('student.dashboard')->with('error', 'This exam is not currently available.');
        }

        // Check if student already has an attempt
        $existingAttempt = ExamAttempt::where('user_id', $student->id)
            ->where('exam_id', $examId)
            ->whereIn('status', ['in_progress', 'submitted', 'graded'])
            ->first();

        if ($existingAttempt) {
            if ($existingAttempt->isInProgress()) {
                return redirect()->route('student.take-exam', $existingAttempt->id);
            }
            return redirect()->route('student.dashboard')->with('error', 'You have already taken this exam.');
        }

        // Create new attempt
        $attempt = ExamAttempt::create([
            'user_id' => $student->id,
            'exam_id' => $examId,
            'started_at' => now(),
            'time_remaining' => $exam->duration_minutes * 60,
            'status' => 'in_progress',
        ]);

        return redirect()->route('student.take-exam', $attempt->id);
    }

    public function takeExam($attemptId)
    {
        $attempt = ExamAttempt::with(['exam.questions', 'answers'])
            ->findOrFail($attemptId);

        // Check ownership
        if ($attempt->user_id != Auth::id()) {
            abort(403);
        }

        // Check if already submitted
        if ($attempt->isSubmitted() || $attempt->isGraded()) {
            return redirect()->route('student.view-result', $attempt->id);
        }

        if ($attempt->hasTimeExpired()) {
            $this->finalizeAttempt($attempt);

            return redirect()->route('student.view-result', $attempt->id)
                ->with('info', 'Your exam time has expired and the attempt was submitted automatically.');
        }

        $attempt->update(['time_remaining' => $attempt->secondsRemaining()]);

        $questions = $attempt->exam->questions()->with('passage')->orderBy('order')->get();
        
        return view('student.take-exam', compact('attempt', 'questions'));
    }

    public function saveAnswer(Request $request, $attemptId)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer_text' => 'nullable|string',
            'time_remaining' => 'required|integer',
        ]);

        $attempt = ExamAttempt::findOrFail($attemptId);

        // Check ownership
        if ($attempt->user_id != Auth::id()) {
            abort(403);
        }

        if (!$attempt->isInProgress()) {
            return response()->json(['success' => false, 'message' => 'This exam has already been submitted.'], 409);
        }

        $attempt->loadMissing('exam');

        if ($attempt->hasTimeExpired()) {
            $this->finalizeAttempt($attempt);

            return response()->json(['success' => false, 'message' => 'Time is up. The exam has been submitted automatically.'], 409);
        }

        if (!$attempt->exam->questions()->whereKey($request->question_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Invalid question for this exam.'], 422);
        }

        // Update time remaining
        $attempt->update(['time_remaining' => $attempt->secondsRemaining()]);

        // Save or update answer
        $answer = Answer::updateOrCreate(
            [
                'attempt_id' => $attemptId,
                'question_id' => $request->question_id,
            ],
            [
                'answer_text' => $request->answer_text,
            ]
        );

        // Auto-grade if objective question
        $question = $answer->question;
        if ($question->isObjective()) {
            $this->autoGradeAnswer($answer);
        }

        return response()->json(['success' => true, 'message' => 'Answer saved']);
    }

    public function submitExam(Request $request, $attemptId)
{
    $attempt = ExamAttempt::with(['exam.questions', 'answers.question'])->findOrFail($attemptId);

    // Check ownership
    if ($attempt->user_id != Auth::id()) {
        abort(403);
    }

    if ($attempt->isSubmitted() || $attempt->isGraded()) {
        return redirect()->route('student.view-result', $attempt->id);
    }

    $this->persistSubmittedAnswers($request, $attempt);
    $this->finalizeAttempt($attempt);

    // ✅ FIXED: Always show success message (removed time check)
    return redirect()->route('student.view-result', $attempt->id)
        ->with('success', 'Exam submitted successfully!');
}

    public function downloadResultPDF($attemptId)
    {
        $attempt = ExamAttempt::with(['exam', 'answers.question', 'user'])
            ->findOrFail($attemptId);

        // Check ownership
        if ($attempt->user_id != Auth::id()) {
            abort(403);
        }

        // Only allow download if graded
        if (!$attempt->isGraded()) {
            return redirect()->back()->with('error', 'Results not available yet. Please wait for grading to complete.');
        }

        if (!$attempt->exam->show_results_to_students) {
            return redirect()->route('student.view-result', $attempt->id)
                ->with('info', 'Your exam has been submitted successfully. Results will be available when your teacher releases them.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('student.exports.result-pdf', compact('attempt'));
        
        $filename = 'Result_' . $attempt->exam->title . '_' . $attempt->user->name . '.pdf';
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
        
        return $pdf->download($filename);
    }

    public function downloadResultWord($attemptId)
    {
        $attempt = ExamAttempt::with(['exam', 'answers.question', 'user'])
            ->findOrFail($attemptId);

        // Check ownership
        if ($attempt->user_id != Auth::id()) {
            abort(403);
        }

        // Only allow download if graded
        if (!$attempt->isGraded()) {
            return redirect()->back()->with('error', 'Results not available yet. Please wait for grading to complete.');
        }

        if (!$attempt->exam->show_results_to_students) {
            return redirect()->route('student.view-result', $attempt->id)
                ->with('info', 'Your exam has been submitted successfully. Results will be available when your teacher releases them.');
        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        // Title
        $section->addTitle($attempt->exam->title . ' - Result', 1);
        $section->addTextBreak(1);

        // Student Info
        $section->addText('Student: ' . $attempt->user->name, ['bold' => true]);
        $section->addText('Registration: ' . $attempt->user->registration_number);
        $section->addText('Subject: ' . $attempt->exam->subject);
        $section->addText('Date: ' . $attempt->submitted_at->format('d M Y, h:i A'));
        $section->addTextBreak(1);

        // ✅ FIX 5: Calculate totals properly for display
        $objectiveTotal = $attempt->exam->questions()->where('question_type', 'multiple_choice')->sum('marks');
        $subjectiveTotal = $attempt->exam->questions()->whereIn('question_type', ['theory', 'coding', 'fill_blank'])->sum('marks');

        // Score
        $section->addTitle('Score Summary', 2);
        $section->addText('Total Score: ' . $attempt->total_score . '/' . $attempt->exam->total_marks, ['size' => 16, 'bold' => true]);
        $section->addText('Percentage: ' . round(($attempt->total_score / $attempt->exam->total_marks) * 100, 1) . '%');
        $section->addText('Objective Score: ' . ($attempt->objective_score ?? 0) . '/' . $objectiveTotal);
        $section->addText('Subjective Score: ' . ($attempt->subjective_score ?? 0) . '/' . $subjectiveTotal);
        
        $resultText = $attempt->total_score >= $attempt->exam->pass_mark ? 'PASSED' : 'FAILED';
        $section->addText('Result: ' . $resultText, ['bold' => true, 'color' => $attempt->total_score >= $attempt->exam->pass_mark ? '008000' : 'FF0000']);
        $section->addTextBreak(1);

        // Questions and Answers
        $section->addTitle('Detailed Review', 2);
        
        foreach ($attempt->answers as $index => $answer) {
            $question = $answer->question;
            
            $section->addText('Question ' . ($index + 1) . ':', ['bold' => true]);
            $section->addText($question->question_text);
            $section->addText('Type: ' . ucwords(str_replace('_', ' ', $question->question_type)), ['italic' => true]);
            $section->addText('Marks: ' . $question->marks);
            
            if ($answer->answer_text) {
                $section->addText('Your Answer:', ['bold' => true]);
                $section->addText($answer->answer_text);
            } else {
                $section->addText('Your Answer: Not Answered', ['color' => 'FF0000']);
            }
            
            if ($question->question_type === 'multiple_choice' || $question->question_type === 'fill_blank') {
                $section->addText('Correct Answer: ' . $question->correct_answer, ['color' => '008000']);
            }
            
            $section->addText('Marks Obtained: ' . ($answer->marks_obtained ?? 0) . '/' . $question->marks, ['bold' => true]);
            
            if ($answer->feedback) {
                $section->addText('Teacher Feedback: ' . $answer->feedback, ['italic' => true, 'color' => '0000FF']);
            }
            
            $section->addTextBreak(1);
        }

        $filename = 'Result_' . $attempt->exam->title . '_' . $attempt->user->name . '.docx';
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
        $tempFile = storage_path('app/' . $filename);
        
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    public function viewResult($attemptId)
    {
        $attempt = ExamAttempt::with(['exam.questions', 'answers.question'])
            ->findOrFail($attemptId);

        // Check ownership
        if ($attempt->user_id != Auth::id()) {
            abort(403);
        }

        $canViewReleasedResults = (bool) $attempt->exam->show_results_to_students;
        $objectiveTotal = 0;
        $subjectiveTotal = 0;

        if ($canViewReleasedResults) {
            // ✅ FIX 6: Calculate totals for result view
            $objectiveTotal = $attempt->exam->questions()->where('question_type', 'multiple_choice')->sum('marks');
            $subjectiveTotal = $attempt->exam->questions()->whereIn('question_type', ['theory', 'coding', 'fill_blank'])->sum('marks');
        }

        return view('student.result', compact('attempt', 'objectiveTotal', 'subjectiveTotal', 'canViewReleasedResults'));
    }

    private function persistSubmittedAnswers(Request $request, ExamAttempt $attempt): void
    {
        $attempt->loadMissing('exam.questions');
        $questionIds = $attempt->exam->questions->pluck('id')->map(fn ($id) => (string) $id);

        foreach ($request->all() as $key => $value) {
            if (!str_starts_with($key, 'question_')) {
                continue;
            }

            $questionId = str_replace('question_', '', $key);

            if (!$questionIds->contains($questionId)) {
                continue;
            }

            $answer = Answer::updateOrCreate(
                [
                    'attempt_id' => $attempt->id,
                    'question_id' => $questionId,
                ],
                [
                    'answer_text' => $value,
                ]
            );

            if ($answer->question->isObjective()) {
                $this->autoGradeAnswer($answer);
            }
        }
    }

    private function finalizeAttempt(ExamAttempt $attempt): void
    {
        $attempt->loadMissing(['exam.questions', 'answers.question']);

        if (!$attempt->isInProgress()) {
            return;
        }

        DB::transaction(function () use ($attempt) {
            $attempt->refresh();

            if (!$attempt->isInProgress()) {
                return;
            }

            $objectiveScore = 0;
            $subjectiveScore = 0;
            $answers = $attempt->answers()->with('question')->get();

            foreach ($answers as $answer) {
                if ($answer->question->isObjective()) {
                    $this->autoGradeAnswer($answer);
                    $answer->refresh();
                    $objectiveScore += $answer->marks_obtained ?? 0;
                } else {
                    $subjectiveScore += $answer->marks_obtained ?? 0;
                }
            }

            $hasSubjective = $attempt->exam->questions->contains(function ($question) {
                return !$question->isObjective();
            });

            $attempt->update([
                'submitted_at' => now(),
                'time_remaining' => $attempt->secondsRemaining(),
                'status' => $hasSubjective ? ExamAttempt::STATUS_SUBMITTED : ExamAttempt::STATUS_GRADED,
                'objective_score' => $objectiveScore,
                'subjective_score' => $subjectiveScore,
                'total_score' => $objectiveScore + $subjectiveScore,
            ]);
        });

        $attempt->refresh();

        if ($attempt->isGraded()) {
            app(CbtReportCardSyncService::class)->syncAttempt($attempt);
        }
    }

    private function autoGradeAnswer($answer)
    {
        $question = $answer->question;
        
        if ($question->question_type === 'multiple_choice') {
            $isCorrect = strtoupper(trim($answer->answer_text)) === strtoupper(trim($question->correct_answer));
            $answer->update([
                'is_correct' => $isCorrect,
                'marks_obtained' => $isCorrect ? $question->marks : 0,
            ]);
        } elseif ($question->question_type === 'fill_blank') {
            $studentAnswer = strtolower(trim($answer->answer_text));
            $correctAnswer = strtolower(trim($question->correct_answer));
            $isCorrect = $studentAnswer === $correctAnswer;
            
            $answer->update([
                'is_correct' => $isCorrect,
                'marks_obtained' => $isCorrect ? $question->marks : 0,
            ]);
        }
    }
}
