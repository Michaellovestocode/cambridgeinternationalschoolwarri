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
use App\Models\AdmissionEnquiry;
use App\Models\FormTeacher;
use App\Models\Message;
use App\Services\CbtReportCardSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

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

        $classStudents = $formTeacherAssignment
            ? $formTeacherAssignment->schoolClass->students()->orderBy('name')->get()
            : collect();

        $studentsCount = $formTeacherAssignment
            ? User::where('role', 'student')->where('class_id', $formTeacherAssignment->class_id)->count()
            : User::where('role', 'student')->count();
        
        $recentExams = Exam::when(!$user->isAdmin(), function($query) use ($user) {
            return $query->where('created_by', $user->id);
        })->latest()->take(5)->get();

        $recentAttempts = ExamAttempt::with(['user', 'exam'])
            ->whereHas('exam', function($query) use ($user) {
                if (!$user->isAdmin()) {
                    $query->where('created_by', $user->id);
                }
            })
            ->latest()
            ->take(20) // Increased to get more attempts for grouping
            ->get();

        // Group attempts by class
        $groupedAttempts = collect();
        foreach ($recentAttempts as $attempt) {
            $className = $attempt->user->class ? $attempt->user->class->display_name : 'No Class';
            if (!$groupedAttempts->has($className)) {
                $groupedAttempts[$className] = collect();
            }
            $groupedAttempts[$className]->push($attempt);
        }

        // Sort classes and limit attempts per class to 5
        $groupedAttempts = $groupedAttempts->map(function ($attempts) {
            return $attempts->take(5);
        })->sortKeys();

        // Check if user is a form teacher
        $isFormTeacher = $formTeacherAssignment !== null;

        $newEnquiriesCount = AdmissionEnquiry::where('status', AdmissionEnquiry::STATUS_NEW)->count();
        $unreadMessagesCount = Message::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('admin.dashboard', compact(
            'examsCount',
            'studentsCount',
            'recentExams',
            'groupedAttempts',
            'isFormTeacher',
            'newEnquiriesCount',
            'unreadMessagesCount',
            'formTeacherAssignment',
            'classStudents'
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
            'instructions' => $validated['instructions'],
            'created_by' => Auth::id(),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $request->boolean('is_active'),
            'show_results_to_students' => $request->boolean('show_results_to_students'),
        ]);

        $exam->classes()->attach($validated['classes']);

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

    public function examQuestions($examId)
    {
        $exam = Exam::with(['questions.passage', 'passages.questions'])->findOrFail($examId);
        
        // Check permission
        if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
            abort(403);
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
        'marks' => 'required|integer|min:1',
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

    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    $request->validate([
        'question_text' => 'required|string',
        'question_passage_id' => 'nullable|exists:question_passages,id',
        'question_type' => 'required|in:multiple_choice,theory,coding,fill_blank',
        'marks' => 'required|integer|min:1',
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

    return redirect()->route('admin.exam.questions', $exam->id)
        ->with('success', 'Question updated successfully!');
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
            ->whereIn('status', ['submitted', 'graded'])
            ->with('user')
            ->get();

        // Calculate statistics
        $gradedAttempts = $attempts->where('status', 'graded');
        $scores = $gradedAttempts->pluck('total_score')->filter();
        
        $statistics = [
            'total_students' => $attempts->count(),
            'graded' => $gradedAttempts->count(),
            'pending' => $attempts->where('status', 'submitted')->count(),
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

    public function gradeAttempt($attemptId)
    {
        $attempt = ExamAttempt::with(['exam', 'user', 'answers.question'])
            ->findOrFail($attemptId);
        
        // Check permission
        if (!Auth::user()->isAdmin() && $attempt->exam->created_by != Auth::id()) {
            abort(403);
        }

        return view('admin.exams.grade', compact('attempt'));
    }

    public function updateGrading(Request $request, $attemptId)
    {
        $attempt = ExamAttempt::with(['answers'])->findOrFail($attemptId);
        
        // Check permission
        if (!Auth::user()->isAdmin() && $attempt->exam->created_by != Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.answer_id' => 'required|exists:answers,id',
            'grades.*.marks_obtained' => 'required|numeric|min:0',
            'grades.*.feedback' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $attempt) {
            $subjectiveScore = 0;

            foreach ($validated['grades'] as $gradeData) {
                $answer = Answer::findOrFail($gradeData['answer_id']);
                
                $answer->update([
                    'marks_obtained' => $gradeData['marks_obtained'],
                    'feedback' => $gradeData['feedback'] ?? null,
                    'graded_by' => Auth::id(),
                ]);

                $subjectiveScore += $gradeData['marks_obtained'];
            }

            $totalScore = ($attempt->objective_score ?? 0) + $subjectiveScore;

            $attempt->update([
                'subjective_score' => $subjectiveScore,
                'total_score' => $totalScore,
                'status' => 'graded',
            ]);
        });

        app(CbtReportCardSyncService::class)->syncAttempt($attempt->fresh(['exam.subjectModel', 'user']));

        return redirect()->route('admin.exam.results', $attempt->exam_id)
            ->with('success', 'Grading completed successfully!');
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
    $teachers = User::where('role', 'teacher')->with('exams', 'subjects', 'teachingClasses')->get();
    return view('admin.teachers.index', compact('teachers'));
}

public function createTeacher()
{
    return view('admin.teachers.create');
}

public function storeTeacher(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'registration_number' => 'required|string|unique:users,registration_number',
        'whatsapp_number' => 'nullable|string|max:30',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        'password' => 'required|string|min:6',
    ]);

    $data = [
        'name' => $validated['name'],
        'email' => $validated['email'],
        'registration_number' => $validated['registration_number'],
        'whatsapp_number' => $validated['whatsapp_number'] ?? null,
        'password' => Hash::make($validated['password']),
        'role' => 'teacher',
    ];

    if ($request->hasFile('photo')) {
        $data['photo'] = $request->file('photo')->store('photos', 'public');
    }

    User::create($data);

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
    $teacher = User::where('role', 'teacher')->findOrFail($teacherId);
    return view('admin.teachers.edit', compact('teacher'));
}

public function updateTeacher(Request $request, $teacherId)
{
    $teacher = User::where('role', 'teacher')->findOrFail($teacherId);
    
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $teacherId,
        'registration_number' => 'required|string|unique:users,registration_number,' . $teacherId,
        'whatsapp_number' => 'nullable|string|max:30',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        'password' => 'nullable|string|min:6',
        'can_manage_blog' => 'nullable|boolean',
    ]);

    $data = [
        'name' => $validated['name'],
        'email' => $validated['email'],
        'registration_number' => $validated['registration_number'],
        'whatsapp_number' => $validated['whatsapp_number'] ?? null,
        'can_manage_blog' => $request->boolean('can_manage_blog'),
    ];

    if ($request->hasFile('photo')) {
        $data['photo'] = $request->file('photo')->store('photos', 'public');
    }

    $teacher->update($data);

    if ($request->filled('password')) {
        $teacher->update(['password' => Hash::make($validated['password'])]);
    }

    return redirect()->route('admin.teachers')->with('success', 'Teacher updated successfully!');
}

public function assignTeacherClasses($teacherId)
{
    $teacher = User::where('role', 'teacher')
        ->with('teachingClasses')
        ->findOrFail($teacherId);
    $classes = SchoolClass::orderBy('name')->get();

    return view('admin.teachers.assign-classes', compact('teacher', 'classes'));
}

public function updateTeacherClasses(Request $request, $teacherId)
{
    $teacher = User::where('role', 'teacher')->findOrFail($teacherId);

    $validated = $request->validate([
        'classes' => 'nullable|array',
        'classes.*' => 'exists:school_classes,id',
    ]);

    $teacher->teachingClasses()->sync($validated['classes'] ?? []);

    return redirect()->route('admin.teachers')->with('success', 'Classes assigned to teacher successfully!');
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
        ->sort(fn (SchoolClass $first, SchoolClass $second) => strnatcasecmp($first->display_name, $second->display_name))
        ->values();

    return view('admin.classes.index', compact('classes'));
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
    return view('admin.classes.edit', compact('class'));
}

public function updateClass(Request $request, SchoolClass $class)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ]);

    $class->update($validated);

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
        'class_id' => 'required|exists:school_classes,id',
        'password' => 'required|string|min:6',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        'date_of_birth' => 'nullable',
        'parent_phone_number' => 'nullable|string|max:20',
        'sex' => 'nullable|in:male,female',
    ]);

    if ($request->filled('date_of_birth') && !$dateOfBirth) {
        return back()
            ->withErrors(['date_of_birth' => 'Enter date of birth in DD/MM/YYYY format.'])
            ->withInput();
    }

    $data = [
        'name' => $validated['name'],
        'registration_number' => $validated['registration_number'],
        'class_id' => $validated['class_id'],
        'password' => Hash::make($validated['password']),
        'role' => 'student',
        'date_of_birth' => $dateOfBirth,
        'parent_phone_number' => $validated['parent_phone_number'] ?? null,
        'sex' => $validated['sex'] ?? null,
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
        'class_id' => 'required|exists:school_classes,id',
        'password' => 'nullable|string|min:6',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        'date_of_birth' => 'nullable',
        'parent_phone_number' => 'nullable|string|max:20',
        'sex' => 'nullable|in:male,female',
    ]);

    if ($request->filled('date_of_birth') && !$dateOfBirth) {
        return back()
            ->withErrors(['date_of_birth' => 'Enter date of birth in DD/MM/YYYY format.'])
            ->withInput();
    }

    $data = [
        'name' => $validated['name'],
        'registration_number' => $validated['registration_number'],
        'class_id' => $validated['class_id'],
        'date_of_birth' => $dateOfBirth,
        'parent_phone_number' => $validated['parent_phone_number'] ?? null,
        'sex' => $validated['sex'] ?? null,
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

    return $user->subjects()
        ->where('is_active', true)
        ->orderBy('name')
        ->get();
}

private function availableExamClasses()
{
    $user = Auth::user();

    if ($user->isAdmin()) {
        return SchoolClass::orderBy('name')->get();
    }

    return $user->teachingClasses()
        ->orderBy('name')
        ->get();
}

private function classesBySubject($subjects, $classes): array
{
    return $subjects->mapWithKeys(function ($subject) use ($classes) {
        $subjectClassIds = $subject->classes()->pluck('school_classes.id')->all();
        $subjectClasses = empty($subjectClassIds)
            ? $classes
            : $classes->whereIn('id', $subjectClassIds)->values();

        return [
            $subject->id => $subjectClasses->map(fn ($class) => [
                'id' => $class->id,
                'display_name' => $class->display_name,
                'description' => $class->description,
            ])->values()->all(),
        ];
    })->all();
}

private function validateExamAssignment(int $subjectId, array $classIds): array
{
    $user = Auth::user();

    if ($user->isAdmin()) {
        return [];
    }

    $teachesSubject = $user->subjects()
        ->where('subjects.id', $subjectId)
        ->exists();

    if (! $teachesSubject) {
        return ['subject_id' => 'You can only create exams for your assigned subjects.'];
    }

    $allowedClassIds = collect($this->classesBySubject(
        Subject::where('id', $subjectId)->get(),
        $this->availableExamClasses()
    )[$subjectId] ?? [])->pluck('id')->all();

    $invalidClassIds = array_diff(array_map('intval', $classIds), $allowedClassIds);

    if (! empty($invalidClassIds)) {
        return ['classes' => 'You can only assign this exam to classes assigned to you for the selected subject.'];
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
}
