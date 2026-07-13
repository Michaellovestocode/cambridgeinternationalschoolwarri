<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionPassage;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\ExamAttempt;
use App\Models\Answer;
use App\Models\ReportCard;
use App\Models\Score;
use App\Models\Session;
use App\Models\Term;
use App\Models\AdmissionEnquiry;
use App\Models\FormTeacher;
use App\Models\Message;
use App\Services\CbtReportCardSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $formTeacherAssignment = null;
        
        $examsCount = Exam::when(!$user->isAdmin(), function($query) use ($user) {
            return $query->where('created_by', $user->id);
        })->count();

        if ($user->isTeacher()) {
            $formTeacherAssignment = FormTeacher::with(['schoolClass', 'schoolClass.students'])
                ->where('teacher_id', $user->id)
                ->where('is_active', true)
                ->first();
        }

        $teachingClasses = $user->isTeacher()
            ? $user->teachingClasses()->withCount('students')->orderBy('name')->get()
            : collect();

        $paperScoreClasses = collect($teachingClasses->all());

        if ($formTeacherAssignment?->schoolClass) {
            $paperScoreClasses = $paperScoreClasses->push($formTeacherAssignment->schoolClass);
        }

        $canUsePaperScores = $user->isAdmin() || $paperScoreClasses
            ->unique('id')
            ->contains(fn (SchoolClass $class) => in_array($class->section_key, ['junior_secondary', 'senior_secondary'], true));

        $classStudents = $formTeacherAssignment
            ? $formTeacherAssignment->schoolClass->students()->orderBy('name')->get()
            : collect();

        $studentsCount = $formTeacherAssignment
            ? User::where('role', 'student')->where('class_id', $formTeacherAssignment->class_id)->count()
            : User::where('role', 'student')->count();
        
        $recentExams = Exam::when(!$user->isAdmin(), function($query) use ($user) {
            return $query->where('created_by', $user->id);
        })->latest()->take(5)->get();

        $recentAttempts = ExamAttempt::with(['user.class', 'exam'])
            ->whereHas('exam', function($query) use ($user) {
                if (!$user->isAdmin()) {
                    $query->where('created_by', $user->id);
                }
            })
            ->latest()
            ->take(20) // Increased to get more attempts for grouping
            ->get();

        // Group attempts by class
        $recentAttemptsCount = $recentAttempts->count();
        $groupedAttempts = $recentAttempts
            ->groupBy(fn ($attempt) => $attempt->user?->class_id ?: 'unassigned')
            ->map(function ($attempts) {
                $class = $attempts->first()?->user?->class;

                return [
                    'class' => $class,
                    'class_name' => $class?->display_name ?? 'No Class',
                    'attempts_count' => $attempts->count(),
                    'pending_count' => $attempts->where('status', ExamAttempt::STATUS_SUBMITTED)->count(),
                    'graded_count' => $attempts->where('status', ExamAttempt::STATUS_GRADED)->count(),
                    'latest_attempt_at' => $attempts->max('created_at'),
                ];
            })
            ->sortBy('class_name');

        // Check if user is a form teacher
        $isFormTeacher = $formTeacherAssignment !== null;
        $assignedSubjectCount = $user->isTeacher()
            ? $this->availableExamSubjects()->count()
            : 0;

        $birthdayLearners = collect();
        $today = now();

        if ($user->isAdmin() || $isFormTeacher) {
            $birthdayLearners = User::with('class')
                ->where('role', 'student')
                ->whereNotNull('date_of_birth')
                ->whereMonth('date_of_birth', $today->month)
                ->whereDay('date_of_birth', $today->day)
                ->when($isFormTeacher && ! $user->isAdmin(), function ($query) use ($formTeacherAssignment) {
                    $query->where('class_id', $formTeacherAssignment->class_id);
                })
                ->orderBy('name')
                ->get();
        }

        $newEnquiriesCount = AdmissionEnquiry::where('status', AdmissionEnquiry::STATUS_NEW)->count();
        $unreadMessagesCount = Message::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('admin.dashboard', compact(
            'examsCount',
            'studentsCount',
            'recentExams',
            'groupedAttempts',
            'recentAttemptsCount',
            'isFormTeacher',
            'newEnquiriesCount',
            'unreadMessagesCount',
            'formTeacherAssignment',
            'classStudents',
            'teachingClasses',
            'assignedSubjectCount',
            'canUsePaperScores',
            'birthdayLearners'
        ));
    }

    public function exams()
    {
        $user = Auth::user();
        
        $exams = Exam::with(['creator', 'classes'])
            ->when(!$user->isAdmin(), function($query) use ($user) {
                return $query->where('created_by', $user->id);
            })
            ->latest()
            ->get();

        return view('admin.exams.index', compact('exams'));
    }

    public function classAttempts(SchoolClass $class)
    {
        $user = Auth::user();

        $attempts = ExamAttempt::with(['user', 'exam'])
            ->whereHas('user', function ($query) use ($class) {
                $query->where('class_id', $class->id);
            })
            ->whereHas('exam', function ($query) use ($user) {
                if (! $user->isAdmin()) {
                    $query->where('created_by', $user->id);
                }
            })
            ->latest()
            ->paginate(30);

        return view('admin.attempts.class', compact('class', 'attempts'));
    }

    public function createExam()
    {
        $subjects = $this->availableExamSubjects();
        $classes = $this->availableExamClasses();
        $classesBySubject = $this->classesBySubject($subjects, $classes);
        
        return view('admin.exams.create', compact('classes', 'subjects', 'classesBySubject'));
    }

    public function storeExam(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'pass_mark' => 'required|integer|min:0',
            'grading_mode' => 'required|in:auto,manual',
            'assessment_component' => 'required|in:test,exam',
            'instructions' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'classes' => 'required|array',
            'classes.*' => 'exists:school_classes,id',
            'is_active' => 'boolean',
            'show_results_to_students' => 'boolean',
        ]);

        $assignmentErrors = $this->validateExamAssignment(
            (int) $validated['subject_id'],
            $validated['classes']
        );

        if ($assignmentErrors) {
            return back()->withErrors($assignmentErrors)->withInput();
        }

        // Get subject name for backward compatibility
        $subject = Subject::findOrFail($validated['subject_id']);
        
        $exam = Exam::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'subject' => $subject->name,
            'subject_id' => $validated['subject_id'],
            'duration_minutes' => $validated['duration_minutes'],
            'total_marks' => $validated['total_marks'],
            'pass_mark' => $validated['pass_mark'],
            'grading_mode' => $validated['grading_mode'],
            'assessment_component' => $validated['assessment_component'],
            'instructions' => $validated['instructions'],
            'created_by' => Auth::id(),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $request->boolean('is_active'),
            'show_results_to_students' => $request->boolean('show_results_to_students'),
        ]);

        $exam->classes()->attach($validated['classes']);

        if ($exam->isManual()) {
            return redirect()->route('admin.exam.manual-scores', $exam->id)
                ->with('success', 'Manual exam created. Enter the exam scores for the class.');
        }

        return redirect()->route('admin.exam.questions', $exam->id)
            ->with('success', 'Exam created successfully! Now add questions.');
    }

    public function editExam($examId)
    {
        $exam = Exam::with('classes')->findOrFail($examId);
        
        // Check permission
        if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
            abort(403);
        }

        $subjects = $this->availableExamSubjects();
        $classes = $this->availableExamClasses();
        $classesBySubject = $this->classesBySubject($subjects, $classes);
        
        return view('admin.exams.edit', compact('exam', 'classes', 'subjects', 'classesBySubject'));
    }

public function updateExam(Request $request, $examId)
{
    $exam = Exam::findOrFail($examId);
    
    // Check permission
    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'subject_id' => 'required|exists:subjects,id',
        'duration_minutes' => 'required|integer|min:1',
        'total_marks' => 'required|integer|min:1',
        'pass_mark' => 'required|integer|min:0',
        'grading_mode' => 'required|in:auto,manual',
        'assessment_component' => 'required|in:test,exam',
        'instructions' => 'nullable|string',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'classes' => 'required|array',
        'classes.*' => 'exists:school_classes,id',
        'is_active' => 'boolean',
        'show_results_to_students' => 'boolean',
    ]);

    $assignmentErrors = $this->validateExamAssignment(
        (int) $validated['subject_id'],
        $validated['classes']
    );

    if ($assignmentErrors) {
        return back()->withErrors($assignmentErrors)->withInput();
    }

    // Get subject name for backward compatibility
    $subject = Subject::findOrFail($validated['subject_id']);

    $exam->update([
        'title' => $validated['title'],
        'description' => $validated['description'],
        'subject' => $subject->name,
        'subject_id' => $validated['subject_id'],
        'duration_minutes' => $validated['duration_minutes'],
        'total_marks' => $validated['total_marks'],
        'pass_mark' => $validated['pass_mark'],
        'grading_mode' => $validated['grading_mode'],
        'assessment_component' => $validated['assessment_component'],
        'instructions' => $validated['instructions'],
        'start_date' => $validated['start_date'],
        'end_date' => $validated['end_date'],
        'is_active' => $request->boolean('is_active'),
        'show_results_to_students' => $request->boolean('show_results_to_students'),
    ]);

    // Sync classes (this will add new ones and remove unchecked ones)
    $exam->classes()->sync($validated['classes']);

    return redirect()->route('admin.exams')
        ->with('success', 'Exam updated successfully!');
}

public function deleteExam($examId)
{
    $exam = Exam::findOrFail($examId);
    
    // Check permission - only admin or the teacher who created it can delete
    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    // Withdraw any synced report-card scores generated from graded attempts on this exam
    $this->withdrawExamAttemptScoresFromReportCard($exam);

    // Delete all associated questions' images
    foreach ($exam->questions as $question) {
        if ($question->image_path) {
            $path = public_path('storage/' . $question->image_path);
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    // Delete all associated records through cascading relationships
    $exam->delete();

    return redirect()->route('admin.exams')
        ->with('success', 'Exam deleted successfully!');
}

private function withdrawExamAttemptScoresFromReportCard(Exam $exam): void
{
    $attempts = $exam->attempts()->with(['exam.subjectModel', 'user'])->get();

    foreach ($attempts as $attempt) {
        if ($attempt->isGraded()) {
            $this->removeAttemptScoreFromReportCard($attempt);
        }
    }
}

private function removeAttemptScoreFromReportCard(ExamAttempt $attempt): void
{
    $attempt->loadMissing(['exam.subjectModel', 'user']);

    $session = Session::getActive();
    $term = Term::getActive();

    if (!$session || !$term || !$attempt->user?->class_id) {
        return;
    }

    $subject = $attempt->exam->subjectModel;

    if (!$subject && $attempt->exam->subject) {
        $subject = Subject::where('name', $attempt->exam->subject)
            ->orWhere('code', $attempt->exam->subject)
            ->first();
    }

    if (!$subject) {
        return;
    }

    $score = Score::where('student_id', $attempt->user_id)
        ->where('subject_id', $subject->id)
        ->where('session_id', $session->id)
        ->where('term_id', $term->id)
        ->first();

    if (!$score) {
        return;
    }

    if ($attempt->exam->assessment_component === 'test') {
        $score->ca1 = 0;
        $score->ca1_source = null;
    } else {
        $score->exam = 0;
        $score->exam_source = null;
    }

    if ((float) $score->ca1 === 0.0 && (float) $score->ca2 === 0.0 && (float) $score->ca3 === 0.0 && (float) $score->exam === 0.0) {
        $score->delete();
    } else {
        $score->status = 'submitted';
        $score->save();
    }

    Score::calculatePositions($subject->id, $attempt->user->class_id, $session->id, $term->id);

    $classAverage = Score::calculateClassAverage($subject->id, $attempt->user->class_id, $session->id, $term->id);

    Score::where('subject_id', $subject->id)
        ->where('class_id', $attempt->user->class_id)
        ->where('session_id', $session->id)
        ->where('term_id', $term->id)
        ->update(['class_average' => $classAverage]);
}

public function manualExamScores(Request $request, $examId)
{
    $exam = Exam::with(['classes', 'subjectModel'])->findOrFail($examId);
    abort_unless($this->canManageManualExam($exam), 403);

    if (! $exam->isManual()) {
        return redirect()->route('admin.exam.questions', $exam->id)
            ->with('error', 'This exam is auto-gradable. Use the questions page instead.');
    }

    $activeSession = Session::getActive();
    $activeTerm = Term::getActive();

    if (! $activeSession || ! $activeTerm) {
        return redirect()->route('admin.exams')
            ->with('error', 'No active session or term found. Please set the active academic period first.');
    }

    $availableClasses = $this->manualExamClassesFor($exam);
    $selectedClassId = (int) $request->input('class_id', $availableClasses->first()?->id);
    $selectedClass = $availableClasses->firstWhere('id', $selectedClassId);

    abort_unless($selectedClass, 403, 'You can only enter scores for assigned classes.');

    $students = User::where('role', 'student')
        ->where('class_id', $selectedClass->id)
        ->orderBy('name')
        ->get();

    $scores = Score::where('class_id', $selectedClass->id)
        ->where('subject_id', $exam->subject_id)
        ->where('session_id', $activeSession->id)
        ->where('term_id', $activeTerm->id)
        ->get()
        ->keyBy('student_id');

    return view('admin.exams.manual-scores', compact(
        'exam',
        'activeSession',
        'activeTerm',
        'availableClasses',
        'selectedClass',
        'students',
        'scores'
    ));
}

public function storeManualExamScores(Request $request, $examId)
{
    $exam = Exam::with(['classes', 'subjectModel'])->findOrFail($examId);
    abort_unless($this->canManageManualExam($exam), 403);
    abort_unless($exam->isManual(), 403);

    $validated = $request->validate([
        'class_id' => 'required|exists:school_classes,id',
        'scores' => 'required|array',
        'scores.*.student_id' => 'required|exists:users,id',
        'scores.*.ca1' => ['nullable', 'numeric', 'min:0', 'max:30'],
        'scores.*.ca2' => ['nullable', 'numeric', 'min:0', 'max:10'],
        'scores.*.exam' => ['nullable', 'numeric', 'min:0', 'max:60'],
    ]);

    $activeSession = Session::getActive();
    $activeTerm = Term::getActive();

    if (! $activeSession || ! $activeTerm) {
        return back()->with('error', 'No active session or term found. Please set the active academic period first.');
    }

    $availableClasses = $this->manualExamClassesFor($exam);
    abort_unless($availableClasses->contains('id', (int) $validated['class_id']), 403, 'You can only enter scores for assigned classes.');

    DB::beginTransaction();

    try {
        $saved = 0;

        foreach ($validated['scores'] as $scoreData) {
            $hasScore = collect(['ca1', 'ca2', 'exam'])
                ->contains(fn ($field) => ($scoreData[$field] ?? null) !== null && ($scoreData[$field] ?? '') !== '');

            if (! $hasScore) {
                continue;
            }

            $student = User::where('role', 'student')
                ->where('class_id', $validated['class_id'])
                ->findOrFail($scoreData['student_id']);

            $existing = Score::firstOrNew([
                'student_id' => $student->id,
                'subject_id' => $exam->subject_id,
                'session_id' => $activeSession->id,
                'term_id' => $activeTerm->id,
            ]);

            $existing->fill($this->manualScorePayloadForFields(
                $existing,
                $scoreData,
                ['ca1', 'ca2', 'exam'],
                [
                    'class_id' => $validated['class_id'],
                    'teacher_id' => Auth::id(),
                    'status' => 'submitted',
                ]
            ));
            $existing->save();
            $saved++;
        }

        Score::calculatePositions($exam->subject_id, $validated['class_id'], $activeSession->id, $activeTerm->id);

        $classAverage = Score::calculateClassAverage($exam->subject_id, $validated['class_id'], $activeSession->id, $activeTerm->id);

        Score::where('subject_id', $exam->subject_id)
            ->where('class_id', $validated['class_id'])
            ->where('session_id', $activeSession->id)
            ->where('term_id', $activeTerm->id)
            ->update(['class_average' => $classAverage]);

        $generated = $this->refreshReportCardsForClass((int) $validated['class_id'], $activeSession, $activeTerm);

        DB::commit();

        return redirect()
            ->route('admin.exam.manual-scores', ['exam' => $exam->id, 'class_id' => $validated['class_id']])
            ->with('success', "{$saved} scores saved. {$generated} report cards refreshed.");
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        throw $e;
    } catch (\Throwable $e) {
        DB::rollBack();

        return back()->with('error', 'Error saving manual scores: ' . $e->getMessage())->withInput();
    }
}

    public function examQuestions($examId)
    {
        $exam = Exam::with(['questions.passage', 'passages.questions'])->findOrFail($examId);
        
        // Check permission
        if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
            abort(403);
        }

        if ($exam->isManual()) {
            return redirect()->route('admin.exam.manual-scores', $exam->id)
                ->with('error', 'This is a manual-entry exam. Enter scores directly instead of adding questions.');
        }

        return view('admin.exams.questions', compact('exam'));
    }

   public function storeQuestion(Request $request, $examId)
{
    $exam = Exam::findOrFail($examId);

    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    // Check if exam is already complete
    if ($exam->questions->sum('marks') >= $exam->total_marks) {
        return redirect()->route('admin.exam.questions', $exam->id)
            ->with('error', 'Cannot add questions to a completed exam.');
    }

    $request->validate([
        'question_text' => 'required|string',
        'question_passage_id' => 'nullable|exists:question_passages,id',
        'question_type' => 'required|in:multiple_choice,theory,coding,fill_blank',
        'marks' => 'required|numeric|min:0.01',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
    ]);

    // Handle image upload
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('question_images', 'public');
    }

    // Conditional validation based on question type
    if ($request->question_type === 'multiple_choice') {
        $request->validate([
            'options' => 'required|array|min:4',
            'correct_answer' => 'required|in:A,B,C,D',
        ]);
    }

    if ($request->question_type === 'fill_blank') {
        $request->validate([
            'correct_answer' => 'required|string',
        ]);
    }

    $questionData = [
        'exam_id' => $exam->id,
        'question_passage_id' => $this->validPassageIdForExam($request->question_passage_id, $exam->id),
        'question_text' => $this->cleanQuestionText($request->question_text),
        'question_type' => $request->question_type,
        'marks' => $request->marks,
        'order' => $exam->questions()->count() + 1,
        'image_path' => $imagePath,
    ];

    if ($request->question_type === 'multiple_choice') {
        $questionData['options'] = $request->options;
        $questionData['correct_answer'] = $request->correct_answer;
    }

    if ($request->question_type === 'fill_blank') {
        $questionData['correct_answer'] = $request->correct_answer;
    }

    Question::create($questionData);

    return redirect()->route('admin.exam.questions', $exam->id)
        ->with('success', 'Question added successfully!');
}

   public function storeQuestionPassage(Request $request, $examId)
{
    $exam = Exam::findOrFail($examId);

    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    $validated = $request->validate([
        'title' => 'nullable|string|max:255',
        'body' => 'required|string',
    ]);

    $exam->passages()->create([
        'title' => $validated['title'] ?? null,
        'body' => $this->cleanQuestionText($validated['body']),
        'order' => $exam->passages()->count() + 1,
    ]);

    return redirect()->route('admin.exam.questions', $exam->id)
        ->with('success', 'Passage added successfully. You can now attach questions to it.');
}

   public function updateQuestionPassage(Request $request, $passageId)
{
    $passage = QuestionPassage::with('exam')->findOrFail($passageId);
    $exam = $passage->exam;

    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    $validated = $request->validate([
        'title' => 'nullable|string|max:255',
        'body' => 'required|string',
    ]);

    $passage->update([
        'title' => $validated['title'] ?? null,
        'body' => $this->cleanQuestionText($validated['body']),
    ]);

    return redirect()->route('admin.exam.questions', $exam->id)
        ->with('success', 'Passage updated successfully!');
}

   public function deleteQuestionPassage($passageId)
{
    $passage = QuestionPassage::with('exam')->findOrFail($passageId);
    $exam = $passage->exam;

    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    $passage->delete();

    return redirect()->route('admin.exam.questions', $exam->id)
        ->with('success', 'Passage deleted. Its questions are now standalone.');
}

   public function editQuestion($questionId)
{
    $question = Question::with(['exam.passages'])->findOrFail($questionId);
    $exam = $question->exam;

    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    return view('admin.exams.edit-question', compact('exam', 'question'));
}

   public function updateQuestion(Request $request, $questionId)
{
    $question = Question::with('exam')->findOrFail($questionId);
    $exam = $question->exam;
    $wasObjective = $question->isObjective();
    $oldQuestionType = $question->question_type;
    $oldCorrectAnswer = $question->correct_answer;
    $oldMarks = (float) $question->marks;

    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    $request->validate([
        'question_text' => 'required|string',
        'question_passage_id' => 'nullable|exists:question_passages,id',
        'question_type' => 'required|in:multiple_choice,theory,coding,fill_blank',
        'marks' => 'required|numeric|min:0.01',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'remove_image' => 'nullable|boolean',
    ]);

    if ($request->question_type === 'multiple_choice') {
        $request->validate([
            'options' => 'required|array|min:4',
            'correct_answer' => 'required|in:A,B,C,D',
        ]);
    }

    if ($request->question_type === 'fill_blank') {
        $request->validate([
            'correct_answer' => 'required|string',
        ]);
    }

    $imagePath = $question->image_path;
    $shouldDeleteCurrentImage = $request->boolean('remove_image') || $request->hasFile('image');

    if ($shouldDeleteCurrentImage && $question->image_path) {
        $path = public_path('storage/' . $question->image_path);
        if (file_exists($path)) {
            unlink($path);
        }
        $imagePath = null;
    }

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('question_images', 'public');
    }

    $questionData = [
        'question_passage_id' => $this->validPassageIdForExam($request->question_passage_id, $exam->id),
        'question_text' => $this->cleanQuestionText($request->question_text),
        'question_type' => $request->question_type,
        'marks' => $request->marks,
        'image_path' => $imagePath,
        'options' => null,
        'correct_answer' => null,
    ];

    if ($request->question_type === 'multiple_choice') {
        $questionData['options'] = $request->options;
        $questionData['correct_answer'] = $request->correct_answer;
    }

    if ($request->question_type === 'fill_blank') {
        $questionData['correct_answer'] = $request->correct_answer;
    }

    $question->update($questionData);

    $question->refresh();
    $shouldRegradeAttempts = $this->questionUpdateRequiresAttemptRegrade(
        $wasObjective,
        $oldQuestionType,
        $oldCorrectAnswer,
        $oldMarks,
        $question
    );

    $regradedAttempts = $shouldRegradeAttempts
        ? $this->regradeAttemptsForUpdatedQuestion($question)
        : 0;

    $message = 'Question updated successfully!';

    if ($regradedAttempts > 0) {
        $message .= " {$regradedAttempts} submitted/graded attempt(s) were recalculated with the corrected answer key.";
    }

    return redirect()->route('admin.exam.questions', $exam->id)
        ->with('success', $message);
}

   public function deleteQuestion($questionId)
{
    $question = Question::with('exam')->findOrFail($questionId);
    $exam = $question->exam;
    $examId = $question->exam_id;

    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    // Delete image if exists
    if ($question->image_path) {
        $path = public_path('storage/' . $question->image_path);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    $question->delete();

    return redirect()->route('admin.exam.questions', $examId)
        ->with('success', 'Question deleted successfully!');
}
    public function examResults($examId)
    {
        $exam = Exam::with(['attempts.user', 'attempts.answers'])
            ->findOrFail($examId);
        
        // Check permission
        if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
            abort(403);
        }

        $attempts = $exam->attempts()
            ->whereIn('status', [
                ExamAttempt::STATUS_SUBMITTED,
                ExamAttempt::STATUS_GRADED,
                ExamAttempt::STATUS_REJECTED,
            ])
            ->with('user')
            ->latest()
            ->get();

        // Calculate statistics
        $gradedAttempts = $attempts->where('status', ExamAttempt::STATUS_GRADED);
        $scores = $gradedAttempts->pluck('total_score')->filter();
        
        $statistics = [
            'total_students' => $attempts->where('status', '!=', ExamAttempt::STATUS_REJECTED)->count(),
            'graded' => $gradedAttempts->count(),
            'pending' => $attempts->where('status', ExamAttempt::STATUS_SUBMITTED)->count(),
            'rejected' => $attempts->where('status', ExamAttempt::STATUS_REJECTED)->count(),
            'average' => $scores->count() > 0 ? round($scores->average(), 2) : 0,
            'highest' => $scores->count() > 0 ? $scores->max() : 0,
            'lowest' => $scores->count() > 0 ? $scores->min() : 0,
            'pass_rate' => $gradedAttempts->count() > 0 
                ? round(($gradedAttempts->where('total_score', '>=', $exam->pass_mark)->count() / $gradedAttempts->count()) * 100, 2)
                : 0,
        ];

        return view('admin.exams.results', compact('exam', 'attempts', 'statistics'));
    }

    public function updateResultRelease(Request $request, $examId)
    {
        $exam = Exam::findOrFail($examId);

        if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
            abort(403);
        }

        $exam->update([
            'show_results_to_students' => $request->boolean('show_results_to_students'),
        ]);

        $message = $exam->show_results_to_students
            ? 'Students can now view their scores and scripts for this exam.'
            : 'Scores and scripts are now hidden from students for this exam.';

        return redirect()->route('admin.exam.results', $exam->id)->with('success', $message);
    }

    public function rejectAttempt(Request $request, $attemptId)
    {
        $attempt = ExamAttempt::with(['exam.subjectModel', 'user'])->findOrFail($attemptId);

        abort_unless($this->canManageAttempt($attempt), 403);

        if ($attempt->isRejected()) {
            return redirect()->route('admin.exam.results', $attempt->exam_id)
                ->with('info', 'This attempt has already been rejected.');
        }

        DB::transaction(function () use ($attempt) {
            $wasGraded = $attempt->isGraded();

            $attempt->update([
                'status' => ExamAttempt::STATUS_REJECTED,
                'total_score' => null,
                'objective_score' => null,
                'subjective_score' => null,
            ]);

            if ($wasGraded) {
                $this->removeRejectedAttemptFromReportCard($attempt);
            }
        });

        return redirect()->route('admin.exam.results', $attempt->exam_id)
            ->with('success', "{$attempt->user->name}'s attempt was rejected. The student can now retake this exam.");
    }

    public function gradeAttempt($attemptId)
    {
        $attempt = ExamAttempt::with(['exam.questions', 'user', 'answers.question'])
            ->findOrFail($attemptId);
        
        abort_unless($this->canManageAttempt($attempt), 403);
        abort_if($attempt->isRejected(), 403, 'Rejected attempts cannot be graded. The student should retake the exam.');

        return view('admin.exams.grade', compact('attempt'));
    }

    public function updateGrading(Request $request, $attemptId)
    {
        $attempt = ExamAttempt::with(['answers.question', 'exam', 'user'])->findOrFail($attemptId);
        
        abort_unless($this->canManageAttempt($attempt), 403);
        abort_if($attempt->isRejected(), 403, 'Rejected attempts cannot be graded. The student should retake the exam.');

        $validated = $request->validate([
            'final_score' => ['nullable', 'numeric', 'min:0', 'max:' . max((float) $attempt->exam->total_marks, 1)],
            'manual_subjective_score' => ['nullable', 'numeric', 'min:0', 'max:' . max((float) $attempt->exam->total_marks, 1)],
            'grades' => ['sometimes', 'array'],
            'grades.*.answer_id' => 'required|exists:answers,id',
            'grades.*.marks_obtained' => 'required|numeric|min:0',
            'grades.*.feedback' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $attempt, $request) {
            $objectiveScore = $this->regradeObjectiveAnswers($attempt);
            $subjectiveScore = 0.0;

            foreach ($validated['grades'] ?? [] as $gradeData) {
                $answer = Answer::findOrFail($gradeData['answer_id']);
                
                $answer->update([
                    'marks_obtained' => $gradeData['marks_obtained'],
                    'feedback' => $gradeData['feedback'] ?? null,
                    'graded_by' => Auth::id(),
                ]);

                $subjectiveScore += $gradeData['marks_obtained'];
            }

            if (isset($validated['manual_subjective_score'])) {
                $subjectiveScore = (float) $validated['manual_subjective_score'];
            }

            $calculatedScore = $objectiveScore + $subjectiveScore;
            $totalScore = $request->filled('final_score') ? (float) $request->final_score : $calculatedScore;

            $attempt->update([
                'objective_score' => $objectiveScore,
                'subjective_score' => $subjectiveScore,
                'total_score' => $totalScore,
                'status' => ExamAttempt::STATUS_GRADED,
            ]);
        });

        app(CbtReportCardSyncService::class)->syncAttempt($attempt->fresh(['exam.subjectModel', 'user']));

        return redirect()->route('admin.attempt.grade', $attempt->id)
            ->with('success', 'Grading completed successfully! Objective questions were recalculated using the current answer key.');
    }

    private function regradeObjectiveAnswers(ExamAttempt $attempt): float
    {
        $objectiveScore = 0.0;

        $attempt->loadMissing('answers.question');

        foreach ($attempt->answers as $answer) {
            $question = $answer->question;

            if (! $question || ! $question->isObjective()) {
                continue;
            }

            $isCorrect = $this->isObjectiveAnswerCorrect($answer, $question);
            $marksObtained = $isCorrect ? (float) $question->marks : 0.0;

            $answer->update([
                'is_correct' => $isCorrect,
                'marks_obtained' => $marksObtained,
            ]);

            $objectiveScore += $marksObtained;
        }

        return $objectiveScore;
    }

    private function questionUpdateRequiresAttemptRegrade(
        bool $wasObjective,
        string $oldQuestionType,
        ?string $oldCorrectAnswer,
        float $oldMarks,
        Question $question
    ): bool {
        return $wasObjective
            || $question->isObjective()
            || $oldQuestionType !== $question->question_type
            || trim((string) $oldCorrectAnswer) !== trim((string) $question->correct_answer)
            || round($oldMarks, 2) !== round((float) $question->marks, 2);
    }

    private function regradeAttemptsForUpdatedQuestion(Question $question): int
    {
        $attemptIds = Answer::where('question_id', $question->id)
            ->whereHas('attempt', function ($query) {
                $query->whereIn('status', [
                    ExamAttempt::STATUS_SUBMITTED,
                    ExamAttempt::STATUS_GRADED,
                ]);
            })
            ->pluck('attempt_id')
            ->unique()
            ->values();

        $regraded = 0;

        foreach ($attemptIds as $attemptId) {
            $attempt = ExamAttempt::with(['answers.question', 'exam.subjectModel', 'user'])
                ->find($attemptId);

            if (! $attempt || $attempt->isRejected()) {
                continue;
            }

            $objectiveScore = $this->regradeObjectiveAnswers($attempt);
            $subjectiveScore = $attempt->answers
                ->filter(fn (Answer $answer) => $answer->question && ! $answer->question->isObjective())
                ->sum(fn (Answer $answer) => (float) ($answer->marks_obtained ?? 0));

            $attempt->update([
                'objective_score' => $objectiveScore,
                'subjective_score' => $subjectiveScore,
                'total_score' => $objectiveScore + $subjectiveScore,
            ]);

            if ($attempt->isGraded()) {
                app(CbtReportCardSyncService::class)->syncAttempt($attempt->fresh(['exam.subjectModel', 'user']));
            }

            $regraded++;
        }

        return $regraded;
    }

    private function isObjectiveAnswerCorrect(Answer $answer, Question $question): bool
    {
        $studentAnswer = trim((string) $answer->answer_text);
        $correctAnswer = trim((string) $question->correct_answer);

        if ($question->question_type === 'multiple_choice') {
            return strtoupper($studentAnswer) === strtoupper($correctAnswer);
        }

        if ($question->question_type === 'fill_blank') {
            return strtolower($studentAnswer) === strtolower($correctAnswer);
        }

        return false;
    }

    public function exportResultsPDF($examId)
    {
        $exam = Exam::with(['attempts' => function($query) {
            $query->where('status', 'graded')->with('user');
        }])->findOrFail($examId);

        $attempts = $exam->attempts;
        $scores = $attempts->pluck('total_score');
        
        $statistics = [
            'average' => $scores->count() > 0 ? round($scores->average(), 2) : 0,
            'highest' => $scores->count() > 0 ? $scores->max() : 0,
            'lowest' => $scores->count() > 0 ? $scores->min() : 0,
        ];

        $pdf = Pdf::loadView('admin.exports.results-pdf', compact('exam', 'attempts', 'statistics'));
        
        return $pdf->download($exam->title . '_results.pdf');
    }

    public function exportResultsWord($examId)
    {
        $exam = Exam::with(['attempts' => function($query) {
            $query->where('status', 'graded')->with('user');
        }])->findOrFail($examId);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Title
        $section->addTitle($exam->title . ' - Results', 1);
        $section->addText('Subject: ' . $exam->subject);
        $section->addText('Date: ' . now()->format('d M Y'));
        $section->addTextBreak(1);

        // Statistics
        $scores = $exam->attempts->pluck('total_score');
        $section->addTitle('Statistics', 2);
        $section->addText('Total Students: ' . $exam->attempts->count());
        $section->addText('Average Score: ' . ($scores->count() > 0 ? round($scores->average(), 2) : 0));
        $section->addText('Highest Score: ' . ($scores->count() > 0 ? $scores->max() : 0));
        $section->addText('Lowest Score: ' . ($scores->count() > 0 ? $scores->min() : 0));
        $section->addTextBreak(1);

        // Results table
        $section->addTitle('Student Results', 2);
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        
        // Header
        $table->addRow();
        $table->addCell(2000)->addText('Student Name');
        $table->addCell(2000)->addText('Registration No.');
        $table->addCell(2000)->addText('Score');
        $table->addCell(2000)->addText('Grade');

        // Data
        foreach ($exam->attempts as $attempt) {
            $table->addRow();
            $table->addCell(2000)->addText($attempt->user->name);
            $table->addCell(2000)->addText($attempt->user->registration_number);
            $table->addCell(2000)->addText($attempt->total_score . '/' . $exam->total_marks);
            $table->addCell(2000)->addText($attempt->total_score >= $exam->pass_mark ? 'Pass' : 'Fail');
        }

        $filename = $exam->title . '_results.docx';
        $tempFile = storage_path('app/' . $filename);
        
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    public function printScript($attemptId)
    {
        $attempt = ExamAttempt::with(['exam.questions', 'user', 'answers'])
            ->findOrFail($attemptId);
        
        // Check permission
        if (!Auth::user()->isAdmin() && $attempt->exam->created_by != Auth::id()) {
            abort(403);
        }

        return view('admin.exports.print-script', compact('attempt'));
    }

    // Teacher Management
public function teachers()
{
    $teachers = User::where('role', 'teacher')->with('exams', 'subjects', 'teachingClasses', 'reportReviewClasses')->get();
    return view('admin.teachers.index', compact('teachers'));
}

public function createTeacher()
{
    $classes = SchoolClass::orderBy('name')->get();

    return view('admin.teachers.create', compact('classes'));
}

public function storeTeacher(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'registration_number' => 'required|string|unique:users,registration_number',
        'attendance_card_uid' => 'nullable|string|max:255|unique:users,attendance_card_uid',
        'attendance_section' => 'nullable|string|max:100',
        'whatsapp_number' => 'nullable|string|max:30',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        'signature' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
        'password' => 'required|string|min:6',
        'can_manage_attendance' => 'nullable|boolean',
        'can_review_report_cards' => 'nullable|boolean',
        'report_authority_role' => 'nullable|in:head_teacher,principal',
        'review_class_ids' => 'nullable|array',
        'review_class_ids.*' => 'exists:school_classes,id',
    ]);

    $data = [
        'name' => $validated['name'],
        'email' => $validated['email'],
        'registration_number' => $validated['registration_number'],
        'attendance_card_uid' => $validated['attendance_card_uid'] ?? null,
        'attendance_section' => $validated['attendance_section'] ?? null,
        'whatsapp_number' => $validated['whatsapp_number'] ?? null,
        'password' => Hash::make($validated['password']),
        'role' => 'teacher',
        'can_manage_attendance' => $request->boolean('can_manage_attendance'),
        'can_review_report_cards' => $request->boolean('can_review_report_cards'),
        'report_authority_role' => $validated['report_authority_role'] ?? null,
    ];

    if ($request->hasFile('photo')) {
        $data['photo'] = $request->file('photo')->store('photos', 'public');
    }

    if ($request->hasFile('signature')) {
        $data['signature'] = $request->file('signature')->store('signatures/teachers', 'public');
    }

    $teacher = User::create($data);
    $teacher->reportReviewClasses()->sync($request->boolean('can_review_report_cards') ? ($validated['review_class_ids'] ?? []) : []);

    return redirect()->route('admin.teachers')->with('success', 'Teacher added successfully!');
}

public function blogManagers()
{
    $managers = User::where('role', 'blog_manager')
        ->orWhere('can_manage_blog', true)
        ->latest()
        ->get();

    return view('admin.blog-managers.index', compact('managers'));
}

public function createBlogManager()
{
    return view('admin.blog-managers.create');
}

public function storeBlogManager(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'registration_number' => 'nullable|string|unique:users,registration_number',
        'password' => 'required|string|min:6',
    ]);

    User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'registration_number' => $validated['registration_number'] ?? null,
        'password' => Hash::make($validated['password']),
        'role' => 'blog_manager',
        'can_manage_blog' => true,
    ]);

    return redirect()->route('admin.blog-managers.index')->with('success', 'Blog manager account created successfully.');
}

public function deleteBlogManager(User $manager)
{
    abort_unless($manager->isBlogManager(), 404);

    $manager->delete();

    return redirect()->route('admin.blog-managers.index')->with('success', 'Blog manager account deleted.');
}

public function revokeBlogManager(User $manager)
{
    abort_unless($manager->can_manage_blog && ! $manager->isBlogManager(), 404);

    $manager->update(['can_manage_blog' => false]);

    return redirect()->route('admin.blog-managers.index')->with('success', 'Blog Studio access revoked.');
}

public function editTeacher($teacherId)
{
    $teacher = User::where('role', 'teacher')->with('reportReviewClasses')->findOrFail($teacherId);
    $classes = SchoolClass::orderBy('name')->get();

    return view('admin.teachers.edit', compact('teacher', 'classes'));
}

public function updateTeacher(Request $request, $teacherId)
{
    $teacher = User::where('role', 'teacher')->findOrFail($teacherId);
    
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $teacherId,
        'registration_number' => 'required|string|unique:users,registration_number,' . $teacherId,
        'attendance_card_uid' => 'nullable|string|max:255|unique:users,attendance_card_uid,' . $teacherId,
        'attendance_section' => 'nullable|string|max:100',
        'whatsapp_number' => 'nullable|string|max:30',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        'signature' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
        'password' => 'nullable|string|min:6',
        'can_manage_blog' => 'nullable|boolean',
        'can_manage_attendance' => 'nullable|boolean',
        'can_review_report_cards' => 'nullable|boolean',
        'report_authority_role' => 'nullable|in:head_teacher,principal',
        'review_class_ids' => 'nullable|array',
        'review_class_ids.*' => 'exists:school_classes,id',
    ]);

    $data = [
        'name' => $validated['name'],
        'email' => $validated['email'],
        'registration_number' => $validated['registration_number'],
        'attendance_card_uid' => $validated['attendance_card_uid'] ?? null,
        'attendance_section' => $validated['attendance_section'] ?? null,
        'whatsapp_number' => $validated['whatsapp_number'] ?? null,
        'can_manage_blog' => $request->boolean('can_manage_blog'),
        'can_manage_attendance' => $request->boolean('can_manage_attendance'),
        'can_review_report_cards' => $request->boolean('can_review_report_cards'),
        'report_authority_role' => $validated['report_authority_role'] ?? null,
    ];

    if ($request->hasFile('photo')) {
        $data['photo'] = $request->file('photo')->store('photos', 'public');
    }

    if ($request->hasFile('signature')) {
        if ($teacher->signature) {
            Storage::disk('public')->delete($teacher->signature);
        }

        $data['signature'] = $request->file('signature')->store('signatures/teachers', 'public');
    }

    $teacher->update($data);
    $teacher->reportReviewClasses()->sync($request->boolean('can_review_report_cards') ? ($validated['review_class_ids'] ?? []) : []);

    if ($request->filled('password')) {
        $teacher->update(['password' => Hash::make($validated['password'])]);
    }

    return redirect()->route('admin.teachers')->with('success', 'Teacher updated successfully!');
}

public function assignTeacherClasses($teacherId)
{
    $teacher = User::where('role', 'teacher')
        ->with(['teachingClasses', 'subjects'])
        ->findOrFail($teacherId);
    $classes = SchoolClass::orderBy('name')->get();
    $subjects = $teacher->subjects()
        ->where('is_active', true)
        ->orderBy('name')
        ->get();
    $teachingLoad = $this->teachingLoadForTeacher($teacher);

    return view('admin.teachers.assign-classes', compact('teacher', 'classes', 'subjects', 'teachingLoad'));
}

public function updateTeacherClasses(Request $request, $teacherId)
{
    $teacher = User::where('role', 'teacher')->findOrFail($teacherId);

    $validated = $request->validate([
        'classes' => 'nullable|array',
        'classes.*' => 'exists:school_classes,id',
        'teaching_load' => 'nullable|array',
        'teaching_load.*' => 'nullable|array',
        'teaching_load.*.*' => 'exists:subjects,id',
    ]);

    $selectedClassIds = collect($validated['classes'] ?? [])
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values()
        ->all();

    $teacher->teachingClasses()->sync($selectedClassIds);
    $this->syncExactTeachingLoad($teacher, $selectedClassIds, $validated['teaching_load'] ?? []);

    return redirect()->route('admin.teachers')->with('success', 'Teaching load assigned successfully!');
}

private function teachingLoadForTeacher(User $teacher): array
{
    if (! Schema::hasTable('teacher_class_subject')) {
        return [];
    }

    return DB::table('teacher_class_subject')
        ->where('teacher_id', $teacher->id)
        ->get()
        ->groupBy('school_class_id')
        ->map(fn ($rows) => $rows->pluck('subject_id')->map(fn ($id) => (int) $id)->all())
        ->all();
}

private function syncExactTeachingLoad(User $teacher, array $selectedClassIds, array $teachingLoad): void
{
    if (! Schema::hasTable('teacher_class_subject')) {
        return;
    }

    $teacherSubjectIds = $teacher->subjects()
        ->pluck('subjects.id')
        ->map(fn ($id) => (int) $id)
        ->all();

    DB::table('teacher_class_subject')
        ->where('teacher_id', $teacher->id)
        ->delete();

    $now = now();
    $rows = [];

    foreach ($selectedClassIds as $classId) {
        $subjectIds = collect($teachingLoad[$classId] ?? [])
            ->map(fn ($id) => (int) $id)
            ->intersect($teacherSubjectIds)
            ->unique()
            ->values();

        foreach ($subjectIds as $subjectId) {
            $rows[] = [
                'teacher_id' => $teacher->id,
                'school_class_id' => $classId,
                'subject_id' => $subjectId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }

    if (! empty($rows)) {
        DB::table('teacher_class_subject')->insert($rows);
    }
}

public function deleteTeacher($teacherId)
{
    $teacher = User::where('role', 'teacher')->findOrFail($teacherId);
    $teacher->delete();
    
    return redirect()->route('admin.teachers')->with('success', 'Teacher deleted successfully!');
}

// Class Management
public function classes()
{
    $classes = SchoolClass::withCount(['students', 'exams'])
        ->get()
        ->sort(fn (SchoolClass $first, SchoolClass $second) => $first->classSortKey() <=> $second->classSortKey())
        ->values();

    $sectionDefinitions = SchoolClass::sectionDefinitions();
    $groupedClasses = $classes->groupBy('section_key');

    return view('admin.classes.index', compact('classes', 'groupedClasses', 'sectionDefinitions'));
}

public function storeClass(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ]);

    SchoolClass::create($validated);

    return redirect()->route('admin.classes')->with('success', 'Class added successfully!');
}

public function editClass(SchoolClass $class)
{
    $class->load('subjects');
    $subjects = Subject::where('is_active', true)
        ->orderBy('class_level')
        ->orderBy('name')
        ->get();

    return view('admin.classes.edit', compact('class', 'subjects'));
}

public function updateClass(Request $request, SchoolClass $class)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'subjects' => 'nullable|array',
        'subjects.*' => 'exists:subjects,id',
    ]);

    $class->update([
        'name' => $validated['name'],
        'description' => $validated['description'] ?? null,
    ]);

    $class->subjects()->sync($validated['subjects'] ?? []);

    return redirect()->route('admin.classes')->with('success', 'Class updated successfully!');
}

public function deleteClass($classId)
{
    $class = SchoolClass::findOrFail($classId);
    
    // Check if class has students
    if ($class->students()->count() > 0) {
        return redirect()->back()->with('error', 'Cannot delete class with enrolled students!');
    }
    
    $class->delete();
    
    return redirect()->route('admin.classes')->with('success', 'Class deleted successfully!');
}

// Student Management
public function students(Request $request)
{
    $students = User::where('role', 'student')
        ->when($request->filled('search'), function ($query) use ($request) {
            $search = trim($request->search);
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('registration_number', 'like', '%' . $search . '%');
            });
        })
        ->when($request->filled('class_id'), function ($query) use ($request) {
            if ($request->class_id === 'unassigned') {
                $query->whereNull('class_id');
                return;
            }

            $query->where('class_id', $request->class_id);
        })
        ->with('class')
        ->orderBy('registration_number')
        ->get();
    $classes = SchoolClass::orderBy('name')->get();
    return view('admin.students.index', compact('students', 'classes'));
}

public function createStudent(Request $request)
{
    $classes = SchoolClass::orderBy('name')->get();
    $preferredClassId = $request->query('class_id');

    return view('admin.students.create', compact('classes', 'preferredClassId'));
}

public function storeStudent(Request $request)
{
    $dateOfBirth = $this->normalizeDateInput($request->input('date_of_birth'));

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'registration_number' => 'required|string|unique:users,registration_number',
        'attendance_card_uid' => 'nullable|string|max:255|unique:users,attendance_card_uid',
        'class_id' => 'required|exists:school_classes,id',
        'password' => 'required|string|min:6',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        'date_of_birth' => 'nullable',
        'parent_phone_number' => 'nullable|string|max:20',
        'sex' => 'nullable|in:male,female',
        'club_society' => 'nullable|string|max:100',
        'favourite_colour' => 'nullable|string|max:50',
    ]);

    if ($request->filled('date_of_birth') && !$dateOfBirth) {
        return back()
            ->withErrors(['date_of_birth' => 'Enter date of birth in DD/MM/YYYY format.'])
            ->withInput();
    }

    $data = [
        'name' => $validated['name'],
        'registration_number' => $validated['registration_number'],
        'attendance_card_uid' => $validated['attendance_card_uid'] ?? null,
        'class_id' => $validated['class_id'],
        'password' => Hash::make($validated['password']),
        'role' => 'student',
        'date_of_birth' => $dateOfBirth,
        'parent_phone_number' => $validated['parent_phone_number'] ?? null,
        'sex' => $validated['sex'] ?? null,
        'club_society' => $validated['club_society'] ?? null,
        'favourite_colour' => $validated['favourite_colour'] ?? null,
    ];

    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('photos', 'public');
        $data['photo'] = $photoPath;
    }

    User::create($data);

    return redirect()->route('admin.students')->with('success', 'Student added successfully!');
}

public function editStudent($studentId)
{
    $student = User::where('role', 'student')->findOrFail($studentId);
    $classes = SchoolClass::orderBy('name')->get();
    return view('admin.students.edit', compact('student', 'classes'));
}

public function updateStudent(Request $request, $studentId)
{
    $student = User::where('role', 'student')->findOrFail($studentId);
    $dateOfBirth = $this->normalizeDateInput($request->input('date_of_birth'));
    
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'registration_number' => 'required|string|unique:users,registration_number,' . $studentId,
        'attendance_card_uid' => 'nullable|string|max:255|unique:users,attendance_card_uid,' . $studentId,
        'class_id' => 'required|exists:school_classes,id',
        'password' => 'nullable|string|min:6',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        'date_of_birth' => 'nullable',
        'parent_phone_number' => 'nullable|string|max:20',
        'sex' => 'nullable|in:male,female',
        'club_society' => 'nullable|string|max:100',
        'favourite_colour' => 'nullable|string|max:50',
    ]);

    if ($request->filled('date_of_birth') && !$dateOfBirth) {
        return back()
            ->withErrors(['date_of_birth' => 'Enter date of birth in DD/MM/YYYY format.'])
            ->withInput();
    }

    $data = [
        'name' => $validated['name'],
        'registration_number' => $validated['registration_number'],
        'attendance_card_uid' => $validated['attendance_card_uid'] ?? null,
        'class_id' => $validated['class_id'],
        'date_of_birth' => $dateOfBirth,
        'parent_phone_number' => $validated['parent_phone_number'] ?? null,
        'sex' => $validated['sex'] ?? null,
        'club_society' => $validated['club_society'] ?? null,
        'favourite_colour' => $validated['favourite_colour'] ?? null,
    ];

    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('photos', 'public');
        $data['photo'] = $photoPath;
    }

    $student->update($data);

    if ($request->filled('password')) {
        $student->update(['password' => Hash::make($validated['password'])]);
    }

    return redirect()->route('admin.students')->with('success', 'Student updated successfully!');
}

public function deleteStudent($studentId)
{
    $student = User::where('role', 'student')->findOrFail($studentId);
    $student->delete();
    
    return redirect()->route('admin.students')->with('success', 'Student deleted successfully!');
}

private function normalizeDateInput(?string $value): ?string
{
    if (!$value) {
        return null;
    }

    $value = trim($value);

    foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
        try {
            $date = Carbon::createFromFormat($format, $value);
            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        } catch (\Throwable $exception) {
        }
    }

    return null;
}

private function availableExamSubjects()
{
    $user = Auth::user();

    if ($user->isAdmin()) {
        return Subject::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    $assignedSubjects = $this->exactTeachingLoadIsAvailable()
        ? Subject::whereIn('id', $this->exactTeachingSubjectIds($user))
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
        : $user->subjects()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

    if (! $user->isTeacher()) {
        return $assignedSubjects;
    }

    $ownedClasses = $this->earlyPrimaryFormTeacherClassesFor($user);
    $ownedClassIds = $ownedClasses->pluck('id')->all();

    if (empty($ownedClassIds)) {
        return $assignedSubjects;
    }

    $ownedClassSubjects = Subject::where('is_active', true)
        ->where(function ($query) use ($ownedClassIds, $ownedClasses) {
            $query->whereHas('classes', fn ($classQuery) => $classQuery->whereIn('school_classes.id', $ownedClassIds));

            foreach ($ownedClasses as $class) {
                $query->orWhereIn('class_level', $this->subjectClassLevelCandidates($class));
            }
        })
        ->orderBy('name')
        ->get();

    $candidateSubjects = $assignedSubjects
        ->merge($ownedClassSubjects)
        ->unique('id')
        ->sortBy('name')
        ->values();

    $availableClasses = $this->availableExamClasses();

    return $candidateSubjects
        ->filter(fn (Subject $subject) => $this->classesForSubjectFromAvailableClasses($subject, $availableClasses)->isNotEmpty())
        ->values();
}

private function availableExamClasses()
{
    $user = Auth::user();

    if ($user->isAdmin()) {
        return SchoolClass::orderBy('name')->get();
    }

    $classes = $user->teachingClasses()
        ->orderBy('name')
        ->get();

    if ($user->isTeacher()) {
        $classes = $classes->merge($this->earlyPrimaryFormTeacherClassesFor($user));
    }

    return $classes
        ->unique('id')
        ->sortBy('name')
        ->values();
}

private function classesBySubject($subjects, $classes): array
{
    return $subjects->mapWithKeys(function ($subject) use ($classes) {
        $subjectClasses = $this->classesForSubjectFromAvailableClasses($subject, $classes);

        return [
            $subject->id => $subjectClasses->map(fn ($class) => [
                'id' => $class->id,
                'display_name' => $class->display_name,
                'description' => $class->description,
            ])->values()->all(),
        ];
    })->all();
}

private function classesForSubjectFromAvailableClasses(Subject $subject, $classes)
{
    $user = Auth::user();

    if ($user?->isTeacher() && $this->exactTeachingLoadIsAvailable()) {
        $exactClassIds = $this->exactTeachingClassIdsForSubject($user, (int) $subject->id);
        $exactClasses = $classes->whereIn('id', $exactClassIds)->values();
        $ownedEarlyPrimaryClasses = $this->earlyPrimaryFormTeacherClassesFor($user)
            ->filter(fn (SchoolClass $class) => $classes->contains('id', $class->id))
            ->filter(fn (SchoolClass $class) => $this->subjectAvailableForClass($subject, $class))
            ->values();

        return $exactClasses
            ->merge($ownedEarlyPrimaryClasses)
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    $subjectClassIds = $subject->classes()->pluck('school_classes.id')->map(fn ($id) => (int) $id)->all();
    $explicitClasses = empty($subjectClassIds)
        ? collect()
        : $classes->whereIn('id', $subjectClassIds)->values();

    if (empty($subjectClassIds) && ! filled($subject->class_level)) {
        return $classes
            ->sortBy('name')
            ->values();
    }

    $levelClasses = $classes
        ->filter(fn (SchoolClass $class) => $this->subjectMatchesClassLevel($subject, $class))
        ->values();

    return $explicitClasses
        ->merge($levelClasses)
        ->unique('id')
        ->sortBy('name')
        ->values();
}

private function subjectAvailableForClass(Subject $subject, SchoolClass $class): bool
{
    $subjectClassIds = $subject->classes()->pluck('school_classes.id')->map(fn ($id) => (int) $id)->all();

    if (! empty($subjectClassIds)) {
        return in_array((int) $class->id, $subjectClassIds, true);
    }

    if (! filled($subject->class_level)) {
        return true;
    }

    return $this->subjectMatchesClassLevel($subject, $class);
}

private function exactTeachingLoadIsAvailable(): bool
{
    return Schema::hasTable('teacher_class_subject');
}

private function exactTeachingSubjectIds(User $teacher): array
{
    if (! $this->exactTeachingLoadIsAvailable()) {
        return [];
    }

    return DB::table('teacher_class_subject')
        ->where('teacher_id', $teacher->id)
        ->pluck('subject_id')
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values()
        ->all();
}

private function exactTeachingClassIdsForSubject(User $teacher, int $subjectId): array
{
    if (! $this->exactTeachingLoadIsAvailable()) {
        return [];
    }

    return DB::table('teacher_class_subject')
        ->where('teacher_id', $teacher->id)
        ->where('subject_id', $subjectId)
        ->pluck('school_class_id')
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values()
        ->all();
}

private function subjectMatchesClassLevel(Subject $subject, SchoolClass $class): bool
{
    if (! filled($subject->class_level)) {
        return false;
    }

    $classLevel = strtolower(trim((string) $subject->class_level));
    $candidates = collect($this->subjectClassLevelCandidates($class))
        ->map(fn ($candidate) => strtolower(trim((string) $candidate)))
        ->all();

    return in_array($classLevel, $candidates, true);
}

private function validateExamAssignment(int $subjectId, array $classIds): array
{
    $user = Auth::user();

    if ($user->isAdmin()) {
        return [];
    }

    $classes = SchoolClass::whereIn('id', array_map('intval', $classIds))->get();
    $explicitlyTeachesSubject = $user->subjects()->where('subjects.id', $subjectId)->exists();
    $teachingClassIds = $user->teachingClasses()->pluck('school_classes.id')->map(fn ($id) => (int) $id)->all();
    $exactTeachingClassIds = $this->exactTeachingClassIdsForSubject($user, $subjectId);
    $ownedEarlyPrimaryClassIds = $this->earlyPrimaryFormTeacherClassesFor($user)->pluck('id')->map(fn ($id) => (int) $id)->all();
    $availableSubjectIds = $this->availableExamSubjects()->pluck('id')->map(fn ($id) => (int) $id)->all();

    if (! in_array($subjectId, $availableSubjectIds, true)) {
        return ['subject_id' => 'You can only create exams for your assigned subjects or your early-years/primary form class subjects.'];
    }

    $subjectClassIds = Subject::whereKey($subjectId)->first()?->classes()->pluck('school_classes.id')->map(fn ($id) => (int) $id)->all() ?? [];
    $subject = Subject::findOrFail($subjectId);

    foreach ($classes as $class) {
        if (
            ! empty($subjectClassIds)
            && ! in_array((int) $class->id, $subjectClassIds, true)
            && ! $this->subjectMatchesClassLevel($subject, $class)
        ) {
            return ['classes' => 'The selected subject is not attached to one of the selected classes.'];
        }

        if ($this->isEarlyYearsOrPrimaryClass($class)) {
            if (! in_array((int) $class->id, $ownedEarlyPrimaryClassIds, true)) {
                return ['classes' => 'Only the assigned form teacher can fill scores for early-years and primary classes.'];
            }

            continue;
        }

        if ($this->exactTeachingLoadIsAvailable() && ! $this->isEarlyYearsOrPrimaryClass($class)) {
            if (! in_array((int) $class->id, $exactTeachingClassIds, true)) {
                return ['classes' => 'You can only assign this subject to classes where your exact teaching load includes this subject.'];
            }

            continue;
        }

        if (! $explicitlyTeachesSubject || ! in_array((int) $class->id, $teachingClassIds, true)) {
            return ['classes' => 'You can only assign this exam to secondary classes and subjects assigned to you.'];
        }
    }

    return [];
}

private function cleanQuestionText(string $text): string
{
    $text = preg_replace('/<\s*(style|script|meta|link|title|xml)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $text);
    $text = preg_replace('/<\s*(style|script|meta|link|title|xml)[^>]*\/?\s*>/i', '', $text);
    $text = preg_replace('/@page\s*\{[^}]*\}\s*/i', '', $text);
    $text = preg_replace('/p\s*\{[^}]*\}\s*/i', '', $text);

    return trim($text);
}

private function validPassageIdForExam($passageId, int $examId): ?int
{
    if (!$passageId) {
        return null;
    }

    return QuestionPassage::where('exam_id', $examId)->whereKey($passageId)->exists()
        ? (int) $passageId
        : null;
}

private function canManageAttempt(ExamAttempt $attempt): bool
{
    $user = Auth::user();

    if ($user->isAdmin()) {
        return true;
    }

    $attempt->loadMissing(['exam', 'user']);

    if ((int) $attempt->exam->created_by === (int) $user->id) {
        return true;
    }

    if (! $user->isTeacher()) {
        return false;
    }

    $classId = $attempt->user?->class_id;

    if (! $classId) {
        return false;
    }

    $teachesSubject = $attempt->exam->subject_id
        ? $user->subjects()->whereKey($attempt->exam->subject_id)->exists()
        : $user->subjects()
            ->where(function ($query) use ($attempt) {
                $query->where('subjects.name', $attempt->exam->subject)
                    ->orWhere('subjects.code', $attempt->exam->subject);
            })
            ->exists();

    if ($this->teacherOwnsEarlyPrimaryClass($user, (int) $classId)) {
        return true;
    }

    return $teachesSubject && $user->teachingClasses()->whereKey($classId)->exists();
}

private function manualScorePayloadForFields(Score $score, array $scoreData, array $fields, array $basePayload): array
{
    $payload = array_merge($basePayload, [
        'ca3' => 0,
        'ca3_source' => $score->exists ? $score->ca3_source : null,
        'ca3_original_cbt_score' => $score->exists ? $score->ca3_original_cbt_score : null,
        'ca3_overridden_by' => $score->exists ? $score->ca3_overridden_by : null,
        'ca3_overridden_at' => $score->exists ? $score->ca3_overridden_at : null,
    ]);

    foreach ($fields as $field) {
        $payload = array_merge($payload, $this->manualComponentPayload($score, $scoreData, $field));
    }

    return $payload;
}

private function manualComponentPayload(Score $score, array $scoreData, string $field): array
{
    $submittedValue = $scoreData[$field] ?? 0;
    $currentSource = $score->exists ? $score->{$field . '_source'} : null;
    $currentValue = $score->exists ? (float) $score->{$field} : 0.0;

    $payload = [
        $field => $submittedValue,
        $field . '_source' => $this->scoreDataHasFieldValue($scoreData, $field)
            ? 'paper'
            : ($score->exists ? $currentSource : null),
        $field . '_original_cbt_score' => $score->exists ? $score->{$field . '_original_cbt_score'} : null,
        $field . '_overridden_by' => $score->exists ? $score->{$field . '_overridden_by'} : null,
        $field . '_overridden_at' => $score->exists ? $score->{$field . '_overridden_at'} : null,
    ];

    if (! $this->scoreDataHasFieldValue($scoreData, $field)) {
        return $payload;
    }

    $isCbtBacked = in_array($currentSource, ['cbt', 'cbt_overridden'], true);
    $changedCbtValue = $isCbtBacked && round((float) $submittedValue, 2) !== round($currentValue, 2);

    if ($changedCbtValue) {
        $payload[$field . '_source'] = 'cbt_overridden';
        $payload[$field . '_original_cbt_score'] = $score->{$field . '_original_cbt_score'} ?? $currentValue;
        $payload[$field . '_overridden_by'] = Auth::id();
        $payload[$field . '_overridden_at'] = now();
    } elseif ($isCbtBacked) {
        $payload[$field . '_source'] = $currentSource;
    }

    return $payload;
}

private function scoreDataHasFieldValue(array $scoreData, string $field): bool
{
    return array_key_exists($field, $scoreData)
        && $scoreData[$field] !== null
        && $scoreData[$field] !== '';
}

private function canManageManualExam(Exam $exam): bool
{
    $user = Auth::user();

    if ($user->isAdmin()) {
        return true;
    }

    if ((int) $exam->created_by === (int) $user->id) {
        return true;
    }

    if (! $user->isTeacher()) {
        return false;
    }

    $teachesSubject = $exam->subject_id
        ? $user->subjects()->whereKey($exam->subject_id)->exists()
        : $user->subjects()
            ->where(function ($query) use ($exam) {
                $query->where('subjects.name', $exam->subject)
                    ->orWhere('subjects.code', $exam->subject);
            })
            ->exists();

    $manageableClasses = $this->manualExamClassesFor($exam);
    $hasOwnedEarlyPrimaryClass = $manageableClasses->contains(fn (SchoolClass $class) => $this->isEarlyYearsOrPrimaryClass($class));

    return $hasOwnedEarlyPrimaryClass || ($teachesSubject && $manageableClasses->isNotEmpty());
}

private function manualExamClassesFor(Exam $exam)
{
    $classes = $exam->classes()->orderBy('name')->get();
    $user = Auth::user();

    if ($user->isAdmin() || (int) $exam->created_by === (int) $user->id) {
        return $classes;
    }

    $teacherClassIds = $user->teachingClasses()->pluck('school_classes.id')->map(fn ($id) => (int) $id)->all();
    $ownedEarlyPrimaryClassIds = $this->earlyPrimaryFormTeacherClassesFor($user)->pluck('id')->map(fn ($id) => (int) $id)->all();

    return $classes
        ->filter(function (SchoolClass $class) use ($teacherClassIds, $ownedEarlyPrimaryClassIds) {
            if ($this->isEarlyYearsOrPrimaryClass($class)) {
                return in_array((int) $class->id, $ownedEarlyPrimaryClassIds, true);
            }

            return in_array((int) $class->id, $teacherClassIds, true);
        })
        ->values();
}

private function earlyPrimaryFormTeacherClassesFor(User $user)
{
    if (! $user->isTeacher()) {
        return collect();
    }

    return FormTeacher::with('schoolClass')
        ->where('teacher_id', $user->id)
        ->where('is_active', true)
        ->get()
        ->pluck('schoolClass')
        ->filter(fn ($class) => $class && $this->isEarlyYearsOrPrimaryClass($class))
        ->values();
}

private function teacherOwnsEarlyPrimaryClass(User $user, int $classId): bool
{
    return $this->earlyPrimaryFormTeacherClassesFor($user)
        ->contains(fn ($class) => (int) $class->id === $classId);
}

private function isEarlyYearsOrPrimaryClass(SchoolClass $class): bool
{
    return in_array($class->section_key, ['creche', 'primary'], true);
}

private function subjectClassLevelCandidates(SchoolClass $class): array
{
    $name = trim((string) $class->name);
    $displayName = trim((string) $class->display_name);
    $level = $class->level_number;

    $candidates = match ($class->section_key) {
        'creche' => ['creche', 'Creche', 'early years', 'Early Years', 'nursery', 'Nursery', 'kg', 'KG', 'pre kg', 'Pre KG', 'pre-kg', 'Pre-KG', 'all', 'All'],
        'primary' => ['primary', 'Primary', 'all', 'All'],
        'junior_secondary' => ['junior', 'Junior', 'jss', 'JSS', 'all', 'All'],
        'senior_secondary' => ['senior', 'Senior', 'sss', 'SSS', 'all', 'All'],
        default => ['all', 'All'],
    };

    foreach ([$name, $displayName] as $className) {
        if ($className !== '') {
            $candidates[] = $className;
            $candidates[] = strtolower($className);
        }
    }

    if ($level) {
        $candidates = array_merge($candidates, match ($class->section_key) {
            'primary' => ["Primary {$level}", "primary {$level}", "Year {$level}", "year {$level}", "Basic {$level}", "basic {$level}", "Pry {$level}", "pry {$level}"],
            'junior_secondary' => ["JSS {$level}", "jss {$level}", "Year {$level}", "year {$level}"],
            'senior_secondary' => ["SSS {$level}", "sss {$level}", "Year {$level}", "year {$level}"],
            'creche' => ["Creche {$level}", "creche {$level}", "Nursery {$level}", "nursery {$level}", "KG {$level}", "kg {$level}"],
            default => [],
        });
    }

    return array_values(array_unique($candidates));
}

private function refreshReportCardsForClass(int $classId, Session $session, Term $term): int
{
    $generated = 0;

    $students = User::where('class_id', $classId)
        ->where('role', 'student')
        ->get();

    foreach ($students as $student) {
        $summary = ReportCard::generateForStudent($student->id, $session->id, $term->id);

        if (!$summary) {
            continue;
        }

        $reportCard = ReportCard::firstOrNew([
            'student_id' => $student->id,
            'session_id' => $session->id,
            'term_id' => $term->id,
        ]);

        $attributes = [
            'class_id' => $classId,
            'status' => 'generated',
            'workflow_status' => ReportCard::WORKFLOW_DRAFT,
            'review_required' => true,
            'published_at' => null,
            'scores_updated_at' => now(),
            'next_term_begins' => $term->next_term_begins,
        ];

        if (! $reportCard->exists) {
            $attributes['class_teacher_name'] = $this->defaultClassTeacherName($classId);
            $attributes['head_teacher_name'] = $this->defaultReportAuthorityName($classId);
        }

        $reportCard->applyGeneratedSummary($summary, $attributes);

        $reportCard->save();
        $generated++;
    }

    return $generated;
}

private function removeRejectedAttemptFromReportCard(ExamAttempt $attempt): void
{
    $attempt->loadMissing(['exam.subjectModel', 'user']);

    $session = Session::getActive();
    $term = Term::getActive();

    if (!$session || !$term || !$attempt->user?->class_id) {
        return;
    }

    $subject = $attempt->exam->subjectModel;

    if (!$subject && $attempt->exam->subject) {
        $subject = Subject::where('name', $attempt->exam->subject)
            ->orWhere('code', $attempt->exam->subject)
            ->first();
    }

    if (!$subject) {
        return;
    }

    $score = Score::where('student_id', $attempt->user_id)
        ->where('subject_id', $subject->id)
        ->where('session_id', $session->id)
        ->where('term_id', $term->id)
        ->first();

    if ($score) {
        $this->removeAttemptScoreFromReportCard($attempt);
    }

    $summary = ReportCard::generateForStudent($attempt->user_id, $session->id, $term->id);
    $reportCard = ReportCard::firstOrNew([
        'student_id' => $attempt->user_id,
        'session_id' => $session->id,
        'term_id' => $term->id,
    ]);

    if ($summary) {
        $attributes = [
            'class_id' => $attempt->user->class_id,
            'status' => 'generated',
            'workflow_status' => ReportCard::WORKFLOW_DRAFT,
            'review_required' => true,
            'published_at' => null,
            'scores_updated_at' => now(),
            'next_term_begins' => $term->next_term_begins,
        ];

        $reportCard->applyGeneratedSummary($summary, $attributes);
        $reportCard->save();
    } elseif ($reportCard->exists) {
        $reportCard->fill([
            'status' => 'generated',
            'workflow_status' => ReportCard::WORKFLOW_DRAFT,
            'review_required' => true,
            'published_at' => null,
            'scores_updated_at' => now(),
        ])->save();
    }
 }

    // ========== ACADEMIC SESSIONS & TERMS MANAGEMENT ==========

    public function academicSessions()
    {
        $sessions = Session::orderByDesc('start_date')->get();
        $terms = Term::with('session')->orderByDesc('start_date')->get();

        return view('admin.academic-sessions.index', compact('sessions', 'terms'));
    }

    public function storeAcademicSession(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:academic_sessions,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        Session::create($validated);

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Academic session created successfully!');
    }

    public function activateAcademicSession($sessionId)
    {
        $session = Session::findOrFail($sessionId);
        $session->activate();

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Session activated successfully!');
    }

    public function deleteAcademicSession($sessionId)
    {
        $session = Session::findOrFail($sessionId);
        $session->delete();

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Session deleted successfully!');
    }

    public function storeTerm(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'next_term_begins' => 'required|date',
        ]);

        // Determine term number based on name
        $termNumber = match($validated['name']) {
            'First Term', 'Autumn', 'Autumn Term' => 1,
            'Second Term', 'Spring', 'Spring Term' => 2,
            'Third Term', 'Summer', 'Summer Term' => 3,
            default => 1,
        };

        Term::create([
            'session_id' => $validated['session_id'],
            'name' => $validated['name'],
            'term_number' => $termNumber,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'next_term_begins' => $validated['next_term_begins'],
        ]);

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Term created successfully! The "Next Term Begins" date will appear on all report cards for this term.');
    }

    public function activateTerm($termId)
    {
        $term = Term::findOrFail($termId);
        $term->activate();

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Term activated successfully!');
    }

    public function editTerm($termId)
    {
        $term = Term::findOrFail($termId);
        $sessions = Session::orderByDesc('start_date')->get();

        return view('admin.academic-sessions.edit-term', compact('term', 'sessions'));
    }

    public function updateTerm(Request $request, $termId)
    {
        $term = Term::findOrFail($termId);

        $validated = $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'next_term_begins' => 'required|date',
        ]);

        $termNumber = match($validated['name']) {
            'First Term', 'Autumn', 'Autumn Term' => 1,
            'Second Term', 'Spring', 'Spring Term' => 2,
            'Third Term', 'Summer', 'Summer Term' => 3,
            default => 1,
        };

        $term->update([
            'session_id' => $validated['session_id'],
            'name' => $validated['name'],
            'term_number' => $termNumber,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'next_term_begins' => $validated['next_term_begins'],
        ]);

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Term updated successfully!');
    }

    public function deleteTerm($termId)
    {
        $term = Term::findOrFail($termId);
        $term->delete();

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Term deleted successfully!');
    }
}
