<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class ResultsController extends Controller
{
    /**
     * Main Results Portal Dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Build base query for attempts
        $query = ExamAttempt::with(['user', 'exam', 'exam.creator'])
            ->whereHas('exam', function($q) use ($user) {
                if (!$user->isAdmin() && $user->role === 'teacher') {
                    $q->where('created_by', $user->id);
                }
            });

        // Filter by exam
        if ($request->filled('exam_id')) {
            // Validate that teacher can access this exam
            if (!$user->isAdmin()) {
                $exam = Exam::find($request->exam_id);
                if (!$exam || $exam->created_by !== $user->id) {
                    abort(403);
                }
            }
            $query->where('exam_id', $request->exam_id);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('user_id', $request->student_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get filtered attempts
        $attempts = $query->latest()->paginate(20);

        // Calculate statistics
        $statistics = $this->calculateStatistics($user);

        // Get filter options - respect teacher permissions
        $exams = $this->getAvailableExams($user);
        
        // For teachers, only get classes and students from their exams
        if ($user->isAdmin()) {
            $classes = SchoolClass::all();
            $students = User::where('role', 'student')->get();
        } else {
            // Get classes that have students who took teacher's exams
            $classes = SchoolClass::whereHas('students', function($q) use ($user) {
                $q->whereHas('examAttempts', function($q) use ($user) {
                    $q->whereHas('exam', function($q) use ($user) {
                        $q->where('created_by', $user->id);
                    });
                });
            })->get();

            // Get students who took teacher's exams
            $students = User::where('role', 'student')
                ->whereHas('examAttempts', function($q) use ($user) {
                    $q->whereHas('exam', function($q) use ($user) {
                        $q->where('created_by', $user->id);
                    });
                })->get();
        }

        return view('admin.results.index', compact('attempts', 'statistics', 'exams', 'classes', 'students'));
    }

    /**
     * Overall Statistics Dashboard
     */
    public function statistics(Request $request)
    {
        $user = Auth::user();

        // Base query
        $baseQuery = ExamAttempt::query()
            ->whereHas('exam', function($q) use ($user) {
                if (!$user->isAdmin() && $user->role === 'teacher') {
                    $q->where('created_by', $user->id);
                }
            });

        // Overall Statistics
        $totalAttempts = (clone $baseQuery)->count();
        $gradedAttempts = (clone $baseQuery)->where('status', 'graded')->count();
        $submittedAttempts = (clone $baseQuery)->where('status', 'submitted')->count();
        $inProgressAttempts = (clone $baseQuery)->where('status', 'in_progress')->count();

        $gradedScores = (clone $baseQuery)
            ->where('status', 'graded')
            ->pluck('total_score');

        $overallStats = [
            'total_attempts' => $totalAttempts,
            'graded' => $gradedAttempts,
            'submitted' => $submittedAttempts,
            'in_progress' => $inProgressAttempts,
            'average_score' => $gradedScores->count() > 0 ? round($gradedScores->average(), 2) : 0,
            'highest_score' => $gradedScores->count() > 0 ? $gradedScores->max() : 0,
            'lowest_score' => $gradedScores->count() > 0 ? $gradedScores->min() : 0,
            'pass_rate' => $totalAttempts > 0 ? round(
                (clone $baseQuery)
                    ->whereRaw('total_score >= (SELECT pass_mark FROM exams WHERE id = exam_attempts.exam_id)')
                    ->count() / $totalAttempts * 100, 2
            ) : 0,
        ];

        // Statistics by Exam
        $examStats = Exam::query()
            ->when(!$user->isAdmin(), function($q) use ($user) {
                if ($user->role === 'teacher') {
                    $q->where('created_by', $user->id);
                }
            })
            ->withCount('attempts')
            ->with(['attempts' => function($q) {
                $q->where('status', 'graded');
            }])
            ->get()
            ->map(function($exam) {
                $scores = $exam->attempts->pluck('total_score');
                $passCount = $exam->attempts->filter(fn($a) => $a->total_score >= $exam->pass_mark)->count();

                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'subject' => $exam->subject,
                    'attempts' => $exam->attempts_count,
                    'graded' => $exam->attempts->count(),
                    'average' => $scores->count() > 0 ? round($scores->average(), 2) : 0,
                    'pass_rate' => $exam->attempts->count() > 0 ? round($passCount / $exam->attempts->count() * 100, 2) : 0,
                ];
            });

        // Statistics by Class
        $classStats = SchoolClass::withCount('students')
            ->with(['students' => function($q) {
                $q->withCount(['attempts' => function($q) {
                    $q->where('status', 'graded');
                }])
                ->withSum(['attempts' => function($q) {
                    $q->where('status', 'graded');
                }], 'total_score');
            }])
            ->get()
            ->map(function($class) {
                $totalScore = $class->students->sum('attempts_sum_total_score') ?? 0;
                $totalGraded = $class->students->sum('attempts_count') ?? 0;

                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'students' => $class->students_count,
                    'graded_count' => $totalGraded,
                    'average' => $totalGraded > 0 ? round($totalScore / $totalGraded, 2) : 0,
                ];
            });

        // Top Performers
        $topPerformers = ExamAttempt::where('status', 'graded')
            ->with('user')
            ->orderBy('total_score', 'desc')
            ->limit(10)
            ->get()
            ->map(function($attempt) {
                return [
                    'name' => $attempt->user->name,
                    'score' => $attempt->total_score,
                    'exam' => $attempt->exam->title,
                ];
            });

        // Recent Activity
        $recentActivity = ExamAttempt::with(['user', 'exam'])
            ->latest()
            ->limit(15)
            ->get();

        return view('admin.results.statistics', compact('overallStats', 'examStats', 'classStats', 'topPerformers', 'recentActivity'));
    }

    /**
     * Exam-wise Results
     */
    public function examWise($examId, Request $request)
    {
        $exam = Exam::with(['attempts' => function($q) {
            $q->with('user');
        }])->findOrFail($examId);

        // Check permission
        if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
            abort(403);
        }

        // Filter attempts
        $attempts = $exam->attempts->when($request->filled('status'), function($collection) use ($request) {
            return $collection->filter(fn($a) => $a->status === $request->status);
        });

        // Calculate statistics
        $scores = $attempts->pluck('total_score')->filter();
        $passCount = $attempts->filter(fn($a) => $a->total_score >= $exam->pass_mark)->count();

        $statistics = [
            'total_attempts' => $attempts->count(),
            'graded' => $attempts->filter(fn($a) => $a->status === 'graded')->count(),
            'submitted' => $attempts->filter(fn($a) => $a->status === 'submitted')->count(),
            'in_progress' => $attempts->filter(fn($a) => $a->status === 'in_progress')->count(),
            'average' => $scores->count() > 0 ? round($scores->average(), 2) : 0,
            'highest' => $scores->count() > 0 ? $scores->max() : 0,
            'lowest' => $scores->count() > 0 ? $scores->min() : 0,
            'pass_rate' => $attempts->count() > 0 ? round($passCount / $attempts->count() * 100, 2) : 0,
        ];

        return view('admin.results.exam-wise', compact('exam', 'attempts', 'statistics'));
    }

    /**
     * Class-wise Results
     */
    public function classWise($classId, Request $request)
    {
        $class = SchoolClass::with(['students' => function($q) {
            $q->with(['examAttempts' => function($q) {
                $q->with('exam');
            }]);
        }])->findOrFail($classId);

        // Collect all attempts for the class
        $attempts = [];
        foreach ($class->students as $student) {
            foreach ($student->examAttempts as $attempt) {
                $attempts[] = $attempt;
            }
        }

        // Calculate statistics
        $scores = collect($attempts)
            ->filter(fn($a) => $a->status === 'graded')
            ->pluck('total_score')
            ->filter(fn($s) => $s !== null);

        $statistics = [
            'total_students' => $class->students->count(),
            'total_attempts' => count($attempts),
            'graded' => collect($attempts)->filter(fn($a) => $a->status === 'graded')->count(),
            'average' => $scores->count() > 0 ? round($scores->average(), 2) : 0,
            'highest' => $scores->count() > 0 ? $scores->max() : 0,
            'lowest' => $scores->count() > 0 ? $scores->min() : 0,
        ];

        return view('admin.results.class-wise', compact('class', 'attempts', 'statistics'));
    }

    /**
     * Student Results (for individual student)
     */
    public function studentResults($studentId)
    {
        $student = User::where('role', 'student')->with(['examAttempts' => function($q) {
            $q->with('exam')->orderBy('created_at', 'desc');
        }])->findOrFail($studentId);

        $attempts = $student->examAttempts;
        
        // Calculate statistics
        $gradedAttempts = $attempts->filter(fn($a) => $a->status === 'graded');
        $scores = $gradedAttempts->pluck('total_score')->filter(fn($s) => $s !== null);

        $statistics = [
            'total_attempts' => $attempts->count(),
            'graded' => $gradedAttempts->count(),
            'average' => $scores->count() > 0 ? round($scores->average(), 2) : 0,
            'highest' => $scores->count() > 0 ? $scores->max() : 0,
            'lowest' => $scores->count() > 0 ? $scores->min() : 0,
        ];

        return view('admin.results.student-results', compact('student', 'attempts', 'statistics'));
    }

    /**
     * Export Results to PDF
     */
    public function exportPDF(Request $request)
    {
        $query = ExamAttempt::with(['user', 'exam'])
            ->where('status', 'graded');

        // Apply filters
        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }
        if ($request->filled('class_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $attempts = $query->get();
        $statistics = $this->calculateStatistics(Auth::user());

        $pdf = Pdf::loadView('admin.results.export-pdf', compact('attempts', 'statistics'));
        return $pdf->download('results_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Export Results to CSV
     */
    public function exportCSV(Request $request)
    {
        $query = ExamAttempt::with(['user', 'exam'])
            ->where('status', 'graded');

        // Apply filters
        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }
        if ($request->filled('class_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        $attempts = $query->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=results_" . now()->format('Y-m-d_H-i-s') . ".csv"
        );

        $columns = array('Student', 'Registration', 'Class', 'Exam', 'Score', 'Max Score', 'Percentage', 'Status');

        $callback = function() use($attempts, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($attempts as $attempt) {
                $percentage = $attempt->exam->total_marks > 0 
                    ? round(($attempt->total_score / $attempt->exam->total_marks) * 100, 2) 
                    : 0;

                fputcsv($file, array(
                    $attempt->user->name,
                    $attempt->user->registration_number,
                    $attempt->user->class->name ?? 'N/A',
                    $attempt->exam->title,
                    $attempt->total_score,
                    $attempt->exam->total_marks,
                    $percentage . '%',
                    $attempt->total_score >= $attempt->exam->pass_mark ? 'Pass' : 'Fail'
                ));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper: Calculate Statistics
     */
    private function calculateStatistics($user)
    {
        $query = ExamAttempt::query()
            ->whereHas('exam', function($q) use ($user) {
                if (!$user->isAdmin() && $user->role === 'teacher') {
                    $q->where('created_by', $user->id);
                }
            });

        $totalAttempts = (clone $query)->count();
        $gradedAttempts = (clone $query)->where('status', 'graded')->count();
        $submittedAttempts = (clone $query)->where('status', 'submitted')->count();

        $gradedScores = (clone $query)
            ->where('status', 'graded')
            ->pluck('total_score');

        return [
            'total_attempts' => $totalAttempts,
            'graded' => $gradedAttempts,
            'submitted' => $submittedAttempts,
            'average' => $gradedScores->count() > 0 ? round($gradedScores->average(), 2) : 0,
            'highest' => $gradedScores->count() > 0 ? $gradedScores->max() : 0,
            'lowest' => $gradedScores->count() > 0 ? $gradedScores->min() : 0,
            'pass_rate' => $totalAttempts > 0 ? round(
                (clone $query)
                    ->whereRaw('total_score >= (SELECT pass_mark FROM exams WHERE id = exam_attempts.exam_id)')
                    ->count() / $totalAttempts * 100, 2
            ) : 0,
        ];
    }

    /**
     * Helper: Get Available Exams
     */
    private function getAvailableExams($user)
    {
        return Exam::when(!$user->isAdmin() && $user->role === 'teacher', function($q) use ($user) {
            return $q->where('created_by', $user->id);
        })->get();
    }
}
