<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\Term;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\ReportCard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherScoreController extends Controller
{
    // ========== SCORE ENTRY DASHBOARD ==========
    
    public function dashboard()
    {
        $teacher = Auth::user();
        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $selectedSessionId = request('session_id', $activeSession?->id);
        $selectedTermId = request('term_id', $activeTerm?->id);
        
        $teacherSubjects = $this->availableSubjectsFor($teacher);
        $classes = $this->availableClassesFor($teacher);
        $sessions = Session::orderByDesc('start_date')->get();
        $terms = Term::with('session')->orderByDesc('start_date')->get();
        
        // Get statistics
        $totalScoresEntered = Score::where('teacher_id', $teacher->id)
            ->when($selectedSessionId, fn ($query) => $query->where('session_id', $selectedSessionId))
            ->when($selectedTermId, fn ($query) => $query->where('term_id', $selectedTermId))
            ->count();
        
        $pendingScores = Score::where('teacher_id', $teacher->id)
            ->when($selectedSessionId, fn ($query) => $query->where('session_id', $selectedSessionId))
            ->when($selectedTermId, fn ($query) => $query->where('term_id', $selectedTermId))
            ->where('status', 'draft')
            ->count();
        
        $submittedScores = Score::where('teacher_id', $teacher->id)
            ->when($selectedSessionId, fn ($query) => $query->where('session_id', $selectedSessionId))
            ->when($selectedTermId, fn ($query) => $query->where('term_id', $selectedTermId))
            ->where('status', 'submitted')
            ->count();

        $selectedSession = $sessions->firstWhere('id', (int) $selectedSessionId);
        $selectedTerm = $terms->firstWhere('id', (int) $selectedTermId);
        
        return view('teacher.scores.dashboard', compact(
            'activeSession', 
            'activeTerm', 
            'selectedSession',
            'selectedTerm',
            'teacherSubjects', 
            'classes',
            'sessions',
            'terms',
            'totalScoresEntered',
            'pendingScores',
            'submittedScores'
        ));
    }
    
    // ========== SELECT CLASS & SUBJECT ==========
    
    public function selectClassSubject()
    {
        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        
        if (!$activeSession || !$activeTerm) {
            return redirect()->back()->with('error', 'No active session or term found. Please contact admin.');
        }
        
        $teacher = Auth::user();
        $classes = $this->availableClassesFor($teacher);
        $subjects = $this->availableSubjectsFor($teacher);
        
        return view('teacher.scores.select', compact('classes', 'subjects', 'activeSession', 'activeTerm'));
    }
    
    // ========== ENTER SCORES ==========
    
    public function enterScores(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'scores' => 'sometimes|array',
            'scores.*.student_id' => 'required_with:scores|exists:users,id',
            'scores.*.ca1' => 'nullable|numeric|min:0|max:30',
            'scores.*.ca2' => 'nullable|numeric|min:0|max:10',
            'scores.*.exam' => 'nullable|numeric|min:0|max:60',
        ]);
        
        $teacher = Auth::user();
        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        
        $class = SchoolClass::findOrFail($request->class_id);
        $subject = Subject::findOrFail($request->subject_id);
        
        // Get all students in the class
        $students = User::where('class_id', $request->class_id)
            ->where('role', 'student')
            ->orderBy('name')
            ->get();
        
        // Get existing scores
        $scores = Score::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('session_id', $activeSession->id)
            ->where('term_id', $activeTerm->id)
            ->get()
            ->keyBy('student_id');
        
        return view('teacher.scores.enter', compact(
            'class', 
            'subject', 
            'students', 
            'scores', 
            'activeSession', 
            'activeTerm'
        ));
    }
    
    // ========== SAVE SCORES ==========
    
    public function saveScores(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'scores' => 'required|array',
            'scores.*.student_id' => 'required|exists:users,id',
            'scores.*.ca1' => 'nullable|numeric|min:0|max:30',
            'scores.*.ca2' => 'nullable|numeric|min:0|max:10',
            'scores.*.exam' => 'nullable|numeric|min:0|max:60',
        ]);
        
        $teacher = Auth::user();
        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $this->authorizeScoreEntry($teacher, (int) $request->class_id, (int) $request->subject_id);
        
        DB::beginTransaction();
        
        try {
            foreach ($request->scores as $scoreData) {
                // Skip if all fields are empty
                if (empty($scoreData['ca1']) && empty($scoreData['ca2']) && empty($scoreData['exam'])) {
                    continue;
                }
                
                Score::updateOrCreate(
                    [
                        'student_id' => $scoreData['student_id'],
                        'subject_id' => $request->subject_id,
                        'session_id' => $activeSession->id,
                        'term_id' => $activeTerm->id,
                    ],
                    [
                        'class_id' => $request->class_id,
                        'teacher_id' => $teacher->id,
                        'ca1' => $scoreData['ca1'] ?? 0,
                        'ca2' => $scoreData['ca2'] ?? 0,
                        'ca3' => 0,
                        'exam' => $scoreData['exam'] ?? 0,
                        'status' => 'draft',
                    ]
                );
            }
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Scores saved successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error saving scores: ' . $e->getMessage());
        }
    }
    
    // ========== SUBMIT SCORES FOR APPROVAL ==========
    
    public function submitScores(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);
        
        $teacher = Auth::user();
        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $this->authorizeScoreEntry($teacher, (int) $request->class_id, (int) $request->subject_id);
        
        DB::beginTransaction();

        try {
            $savedCount = 0;

            foreach ($request->input('scores', []) as $scoreData) {
                if (empty($scoreData['ca1']) && empty($scoreData['ca2']) && empty($scoreData['exam'])) {
                    continue;
                }

                Score::updateOrCreate(
                    [
                        'student_id' => $scoreData['student_id'],
                        'subject_id' => $request->subject_id,
                        'session_id' => $activeSession->id,
                        'term_id' => $activeTerm->id,
                    ],
                    [
                        'class_id' => $request->class_id,
                        'teacher_id' => $teacher->id,
                        'ca1' => $scoreData['ca1'] ?? 0,
                        'ca2' => $scoreData['ca2'] ?? 0,
                        'ca3' => 0,
                        'exam' => $scoreData['exam'] ?? 0,
                        'status' => 'submitted',
                    ]
                );

                $savedCount++;
            }

            $submittedDrafts = Score::where('teacher_id', $teacher->id)
                ->where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('session_id', $activeSession->id)
                ->where('term_id', $activeTerm->id)
                ->where('status', 'draft')
                ->update(['status' => 'submitted']);

            Score::calculatePositions($request->subject_id, $request->class_id, $activeSession->id, $activeTerm->id);

            $classAverage = Score::calculateClassAverage($request->subject_id, $request->class_id, $activeSession->id, $activeTerm->id);

            Score::where('subject_id', $request->subject_id)
                ->where('class_id', $request->class_id)
                ->where('session_id', $activeSession->id)
                ->where('term_id', $activeTerm->id)
                ->update(['class_average' => $classAverage]);

            $generated = $this->refreshReportCardsForClass((int) $request->class_id, $activeSession, $activeTerm);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error submitting scores: ' . $e->getMessage());
        }
        
        return redirect()->route('teacher.scores.dashboard')
            ->with('success', ($savedCount + $submittedDrafts) . " scores submitted. {$generated} report cards refreshed.");
    }
    
    // ========== VIEW MY SUBMITTED SCORES ==========
    
    public function myScores(Request $request)
    {
        $teacher = Auth::user();
        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $selectedSessionId = $request->input('session_id', $activeSession?->id);
        $selectedTermId = $request->input('term_id', $activeTerm?->id);
        
        $scores = Score::where('teacher_id', $teacher->id)
            ->when($selectedSessionId, fn ($query) => $query->where('session_id', $selectedSessionId))
            ->when($selectedTermId, fn ($query) => $query->where('term_id', $selectedTermId))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('registration_number', 'like', '%' . $search . '%');
                    })->orWhereHas('subject', function ($subjectQuery) use ($search) {
                        $subjectQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('code', 'like', '%' . $search . '%');
                    })->orWhereHas('class', function ($classQuery) use ($search) {
                        $classQuery->where('name', 'like', '%' . $search . '%');
                    });
                });
            })
            ->with(['student', 'subject', 'class'])
            ->latest()
            ->paginate(50);

        $scores->appends($request->query());
        $sessions = Session::orderByDesc('start_date')->get();
        $terms = Term::with('session')->orderByDesc('start_date')->get();
        $selectedSession = $sessions->firstWhere('id', (int) $selectedSessionId);
        $selectedTerm = $terms->firstWhere('id', (int) $selectedTermId);
        
        return view('teacher.scores.my-scores', compact(
            'scores',
            'activeSession',
            'activeTerm',
            'sessions',
            'terms',
            'selectedSession',
            'selectedTerm'
        ));
    }

    private function availableSubjectsFor(User $teacher)
    {
        if ($teacher->isAdmin()) {
            return Subject::active()->ordered()->get();
        }

        return $teacher->subjects()->active()->ordered()->get();
    }

    private function availableClassesFor(User $teacher)
    {
        if ($teacher->isAdmin()) {
            return SchoolClass::orderBy('name')->get();
        }

        return $teacher->teachingClasses()->orderBy('name')->get();
    }

    private function authorizeScoreEntry(User $teacher, int $classId, int $subjectId): void
    {
        if ($teacher->isAdmin()) {
            return;
        }

        $hasClass = $teacher->teachingClasses()->whereKey($classId)->exists();
        $hasSubject = $teacher->subjects()->whereKey($subjectId)->exists();

        abort_unless($hasClass && $hasSubject, 403, 'You can only enter scores for classes and subjects assigned to you.');
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

            $reportCard->fill(array_merge($summary, [
                'class_id' => $classId,
                'status' => 'generated',
                'review_required' => true,
                'published_at' => null,
                'scores_updated_at' => now(),
            ]));

            if (!$reportCard->exists) {
                $reportCard->fill([
                    'days_school_opened' => 0,
                    'days_present' => 0,
                    'days_absent' => 0,
                    'attendance_percentage' => 0,
                    'next_term_begins' => $term->next_term_begins,
                ]);
            }

            $reportCard->save();
            $generated++;
        }

        return $generated;
    }
}
