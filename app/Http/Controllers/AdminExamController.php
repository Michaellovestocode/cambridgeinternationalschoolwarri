public function submitForStudent(ExamAttempt $attempt)
{
    // Check if already submitted
    if ($attempt->status === 'completed') {
        return back()->with('error', 'This exam has already been submitted.');
    }

    // Mark as completed
    $attempt->update([
        'status' => 'completed',
        'submitted_at' => now(),
        'time_remaining' => 0
    ]);

    // Auto-grade MCQ and Fill Blank questions
    foreach ($attempt->answers as $answer) {
        $question = $answer->question;
        
        if ($question->question_type === 'multiple_choice' || $question->question_type === 'fill_blank') {
            $isCorrect = strtolower(trim($answer->answer_text)) === strtolower(trim($question->correct_answer));
            $answer->update([
                'is_correct' => $isCorrect,
                'marks_obtained' => $isCorrect ? $question->marks : 0
            ]);
        }
    }

    // Calculate total score
    $totalScore = $attempt->answers()->sum('marks_obtained');
    $attempt->update(['score' => $totalScore]);

    return back()->with('success', 'Exam submitted successfully for student: ' . $attempt->student->name);

    public function previewAttempt(ExamAttempt $attempt)
{
    $attempt->load(['exam', 'user', 'answers.question']);
    $questions = $attempt->exam->questions;
    
    return view('admin.exams.preview', compact('attempt', 'questions'));
}
}