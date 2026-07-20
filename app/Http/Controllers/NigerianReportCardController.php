<?php

namespace App\Http\Controllers;

use App\Models\ReportCard;
use App\Models\Score;
use App\Models\Session;
use App\Models\Term;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\FormTeacher;
use App\Models\Subject;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class NigerianReportCardController extends Controller
{
    // ========== VIEW REPORT CARDS ==========
    
    public function index(Request $request)
    {
        $this->authorizeReportCardManagement();

        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $selectedSessionId = $request->input('session_id', $activeSession?->id);
        $selectedTermId = $request->input('term_id', $activeTerm?->id);

        $user = auth()->user();
        $formTeacherClassIds = $this->formTeacherClassIdsFor($user->id);
        $reportCards = ReportCard::with(['student', 'session', 'term', 'class'])
            ->when($user->isTeacher(), function ($query) use ($formTeacherClassIds) {
                $query->whereIn('class_id', $formTeacherClassIds);
            })
            ->when($selectedSessionId, fn ($query) => $query->where('session_id', $selectedSessionId))
            ->when($selectedTermId, fn ($query) => $query->where('term_id', $selectedTermId))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('registration_number', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('student_id'), function ($query) use ($request) {
                $query->where('student_id', $request->student_id);
            })
            ->when($request->filled('class_id'), function ($query) use ($request) {
                $query->where('class_id', $request->class_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('workflow_status'), function ($query) use ($request) {
                $query->where('workflow_status', $request->workflow_status);
            })
            ->latest()
            ->paginate(20);

        $reportCards->appends($request->query());

        $students = User::where('role', 'student')
            ->when($user->isTeacher(), function ($query) use ($formTeacherClassIds) {
                $query->whereIn('class_id', $formTeacherClassIds);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('registration_number', 'like', '%' . $search . '%');
                });
            })
            ->with('class')
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::when($user->isTeacher(), function ($query) use ($formTeacherClassIds) {
            $query->whereIn('id', $formTeacherClassIds);
        })->orderBy('name')->get();

        $sessions = Session::orderByDesc('start_date')->get();
        $terms = Term::with('session')->orderByDesc('start_date')->get();
        $selectedSession = $sessions->firstWhere('id', (int) $selectedSessionId);
        $selectedTerm = $terms->firstWhere('id', (int) $selectedTermId);
        $selectedClass = $request->filled('class_id') ? $classes->firstWhere('id', (int) $request->class_id) : null;

        return view('admin.report-cards.index', compact(
            'reportCards',
            'activeSession',
            'activeTerm',
            'students',
            'classes',
            'sessions',
            'terms',
            'selectedSession',
            'selectedTerm',
            'selectedClass'
        ));
    }

    public function reviews(Request $request)
    {
        $this->authorizeAcademicReview();

        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $selectedSessionId = $request->input('session_id', $activeSession?->id);
        $selectedTermId = $request->input('term_id', $activeTerm?->id);
        $reviewClassIds = $this->reviewerClassIdsFor(auth()->user());
        $selectedClassId = $request->filled('class_id') ? (int) $request->input('class_id') : null;

        if ($selectedClassId) {
            abort_unless(in_array($selectedClassId, $reviewClassIds, true), 403, 'You are not assigned to review this class.');
        }

        $reviewStatuses = [
            ReportCard::WORKFLOW_SUBMITTED,
            ReportCard::WORKFLOW_REJECTED,
            ReportCard::WORKFLOW_ACADEMIC_APPROVED,
            ReportCard::WORKFLOW_PUBLISHED,
        ];

        $reviewBaseQuery = ReportCard::query()
            ->whereIn('class_id', $reviewClassIds)
            ->when($selectedSessionId, fn ($query) => $query->where('session_id', $selectedSessionId))
            ->when($selectedTermId, fn ($query) => $query->where('term_id', $selectedTermId))
            ->whereIn('workflow_status', $reviewStatuses);

        $reviewCounts = (clone $reviewBaseQuery)
            ->select('class_id', 'workflow_status')
            ->get()
            ->groupBy('class_id');

        $studentsByClass = User::where('role', 'student')
            ->whereIn('class_id', $reviewClassIds)
            ->orderBy('name')
            ->get()
            ->groupBy('class_id');

        $reviewClasses = SchoolClass::whereIn('id', $reviewClassIds)
            ->orderBy('name')
            ->get()
            ->map(function (SchoolClass $class) use ($reviewCounts, $studentsByClass) {
                $classCards = $reviewCounts->get($class->id, collect());
                $learnerCount = $studentsByClass->get($class->id, collect())->count();
                $class->review_total = $classCards->count();
                $class->learner_total = $learnerCount;
                $class->not_submitted_count = max(0, $learnerCount - $classCards->whereIn('workflow_status', [
                    ReportCard::WORKFLOW_SUBMITTED,
                    ReportCard::WORKFLOW_REJECTED,
                    ReportCard::WORKFLOW_ACADEMIC_APPROVED,
                    ReportCard::WORKFLOW_PUBLISHED,
                ])->count());
                $class->review_submitted_count = $classCards->where('workflow_status', ReportCard::WORKFLOW_SUBMITTED)->count();
                $class->review_rejected_count = $classCards->where('workflow_status', ReportCard::WORKFLOW_REJECTED)->count();
                $class->review_approved_count = $classCards->where('workflow_status', ReportCard::WORKFLOW_ACADEMIC_APPROVED)->count();
                $class->review_published_count = $classCards->where('workflow_status', ReportCard::WORKFLOW_PUBLISHED)->count();

                return $class;
            });

        $reportCards = ReportCard::with(['student', 'session', 'term', 'class', 'academicReviewer'])
            ->whereIn('class_id', $reviewClassIds)
            ->when($selectedClassId, fn ($query) => $query->where('class_id', $selectedClassId))
            ->when(! $selectedClassId, fn ($query) => $query->whereRaw('1 = 0'))
            ->when($selectedSessionId, fn ($query) => $query->where('session_id', $selectedSessionId))
            ->when($selectedTermId, fn ($query) => $query->where('term_id', $selectedTermId))
            ->whereIn('workflow_status', $reviewStatuses)
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        $selectedClassLearners = collect();
        $reviewRows = collect();

        if ($selectedClassId) {
            $selectedClassLearners = $studentsByClass->get($selectedClassId, collect())->values();
            $cardsByStudent = ReportCard::with(['student', 'session', 'term', 'class', 'academicReviewer'])
                ->where('class_id', $selectedClassId)
                ->when($selectedSessionId, fn ($query) => $query->where('session_id', $selectedSessionId))
                ->when($selectedTermId, fn ($query) => $query->where('term_id', $selectedTermId))
                ->whereIn('workflow_status', $reviewStatuses)
                ->get()
                ->keyBy('student_id');

            $reviewRows = $selectedClassLearners
                ->map(fn (User $learner) => (object) [
                    'learner' => $learner,
                    'reportCard' => $cardsByStudent->get($learner->id),
                ])
                ->sortBy([
                    fn ($row) => $row->reportCard ? 0 : 1,
                    fn ($row) => strtolower($row->learner->name),
                ])
                ->values();
        }

        $sessions = Session::orderByDesc('start_date')->get();
        $terms = Term::with('session')->orderByDesc('start_date')->get();
        $selectedSession = $sessions->firstWhere('id', (int) $selectedSessionId);
        $selectedTerm = $terms->firstWhere('id', (int) $selectedTermId);
        $selectedClass = $selectedClassId ? $reviewClasses->firstWhere('id', $selectedClassId) : null;

        return view('admin.report-cards.reviews', compact('reportCards', 'sessions', 'terms', 'selectedSession', 'selectedTerm', 'reviewClasses', 'selectedClass', 'reviewRows', 'selectedClassLearners'));
    }

    public function earlyPrimaryLearners(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isTeacher(), 403);

        $formClasses = $this->classScoreEntryFormTeacherClassesFor($user);
        abort_unless($formClasses->isNotEmpty(), 403, 'This score-entry page is for Early Years, Primary, and Other Classes form teachers.');

        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $selectedSessionId = $request->input('session_id', $activeSession?->id);
        $selectedTermId = $request->input('term_id', $activeTerm?->id);
        $selectedClassId = (int) $request->input('class_id', $formClasses->first()?->id);
        $selectedClass = $formClasses->firstWhere('id', $selectedClassId) ?: $formClasses->first();

        $learners = User::where('role', 'student')
            ->where('class_id', $selectedClass?->id)
            ->orderBy('name')
            ->get();

        $sessions = Session::orderByDesc('start_date')->get();
        $terms = Term::with('session')->orderByDesc('start_date')->get();
        $selectedSession = $sessions->firstWhere('id', (int) $selectedSessionId);
        $selectedTerm = $terms->firstWhere('id', (int) $selectedTermId);

        return view('admin.report-cards.early-primary-learners', compact(
            'formClasses',
            'learners',
            'sessions',
            'terms',
            'selectedClass',
            'selectedSession',
            'selectedTerm'
        ));
    }

    public function manual(Request $request)
    {
        $this->authorizeReportCardManagement();
        $this->authorizeManualReportCardBuilderAccess();

        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $user = auth()->user();
        $selectedSessionId = $request->input('session_id', $activeSession?->id);
        $selectedTermId = $request->input('term_id', $activeTerm?->id);
        $selectedClassId = $request->input('class_id');
        $selectedStudentId = $request->input('student_id');
        $reviewClassIds = $this->reviewerClassIdsFor($user);

        if ($selectedStudentId && !$selectedClassId) {
            $selectedClassId = User::where('role', 'student')
                ->whereKey($selectedStudentId)
                ->value('class_id');
        }

        if ($selectedClassId) {
            $this->authorizeClassAccess((int) $selectedClassId);
        }

        $classes = SchoolClass::when($user->isTeacher() && ! $user->canReviewReportCards(), function ($query) use ($user) {
            $query->whereIn('id', $this->formTeacherClassIdsFor($user->id));
        })->when($user->isTeacher() && $user->canReviewReportCards(), function ($query) use ($reviewClassIds) {
            $query->whereIn('id', $reviewClassIds);
        })->orderBy('name')->get();

        $students = User::where('role', 'student')
            ->when($user->isTeacher() && ! $user->canReviewReportCards(), function ($query) use ($user) {
                $query->whereIn('class_id', $this->formTeacherClassIdsFor($user->id));
            })
            ->when($user->isTeacher() && $user->canReviewReportCards(), function ($query) use ($reviewClassIds) {
                $query->whereIn('class_id', $reviewClassIds);
            })
            ->with('class')
            ->orderBy('name')
            ->get();

        $sessions = Session::orderByDesc('start_date')->get();
        $terms = Term::with('session')->orderByDesc('start_date')->get();
        $selectedSession = $sessions->firstWhere('id', (int) $selectedSessionId);
        $selectedTerm = $terms->firstWhere('id', (int) $selectedTermId);
        $selectedClass = $selectedClassId ? $classes->firstWhere('id', (int) $selectedClassId) : null;
        $selectedStudent = $selectedStudentId
            ? User::with('class')->where('role', 'student')->findOrFail($selectedStudentId)
            : null;

        if ($selectedStudent) {
            $this->authorizeClassAccess($selectedStudent->class_id);
            if ($selectedClassId && (int) $selectedStudent->class_id !== (int) $selectedClassId) {
                return redirect()->route('admin.report-cards.manual', [
                    'session_id' => $selectedSessionId,
                    'term_id' => $selectedTermId,
                    'class_id' => $selectedClassId,
                ])->with('error', 'Selected student does not belong to the selected class.');
            }
        }

        $subjects = collect();
        $scores = collect();

        if ($selectedClass && $selectedStudent && $selectedSession && $selectedTerm) {
            $subjects = $this->subjectsForManualReportClass($selectedClass)
                ->sortBy('name')
                ->values();

            $scores = Score::where('student_id', $selectedStudent->id)
                ->where('session_id', $selectedSession->id)
                ->where('term_id', $selectedTerm->id)
                ->get()
                ->keyBy('subject_id');
        }

        return view('admin.report-cards.manual', compact(
            'classes',
            'students',
            'sessions',
            'terms',
            'selectedSession',
            'selectedTerm',
            'selectedClass',
            'selectedStudent',
            'subjects',
            'scores'
        ));
    }

    public function storeManual(Request $request)
    {
        $this->authorizeReportCardManagement();

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'scores' => 'required|array',
            'scores.*.subject_id' => 'required|exists:subjects,id',
            'scores.*.ca1' => 'nullable|numeric|min:0|max:30',
            'scores.*.ca2' => 'nullable|numeric|min:0|max:10',
            'scores.*.exam' => 'nullable|numeric|min:0|max:60',
        ]);

        $student = User::where('role', 'student')->findOrFail($validated['student_id']);
        $this->authorizeClassAccess((int) $validated['class_id']);
        $this->authorizeManualReportCardBuilderAccess((int) $validated['class_id']);

        if ((int) $student->class_id !== (int) $validated['class_id']) {
            return back()->withErrors([
                'student_id' => 'Selected student does not belong to the selected class.',
            ])->withInput();
        }

        $term = Term::findOrFail($validated['term_id']);
        $teacherId = auth()->id();

        DB::beginTransaction();

        try {
            foreach ($validated['scores'] as $scoreData) {
                $hasScore = collect(['ca1', 'ca2', 'exam'])
                    ->contains(fn ($field) => $scoreData[$field] !== null && $scoreData[$field] !== '');

                if (!$hasScore) {
                    continue;
                }

                $score = Score::firstOrNew(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $scoreData['subject_id'],
                        'session_id' => $validated['session_id'],
                        'term_id' => $validated['term_id'],
                    ]
                );

                $score->fill([
                    'class_id' => $validated['class_id'],
                    'teacher_id' => $teacherId,
                    'ca1' => $scoreData['ca1'] ?? ($score->exists ? $score->ca1 : 0),
                    'ca2' => $scoreData['ca2'] ?? ($score->exists ? $score->ca2 : 0),
                    'ca3' => $score->exists ? $score->ca3 : 0,
                    'exam' => $scoreData['exam'] ?? ($score->exists ? $score->exam : 0),
                    'status' => 'submitted',
                ])->save();

                Score::calculatePositions(
                    $scoreData['subject_id'],
                    $validated['class_id'],
                    $validated['session_id'],
                    $validated['term_id']
                );

                $classAverage = Score::calculateClassAverage(
                    $scoreData['subject_id'],
                    $validated['class_id'],
                    $validated['session_id'],
                    $validated['term_id']
                );

                Score::where('subject_id', $scoreData['subject_id'])
                    ->where('class_id', $validated['class_id'])
                    ->where('session_id', $validated['session_id'])
                    ->where('term_id', $validated['term_id'])
                    ->update(['class_average' => $classAverage]);
            }

            $summary = ReportCard::generateForStudent(
                $student->id,
                $validated['session_id'],
                $validated['term_id']
            );

            if (!$summary) {
                DB::rollBack();

                return back()->withErrors([
                    'scores' => 'Enter at least one subject score before generating the report card.',
                ])->withInput();
            }

            $reportCard = ReportCard::firstOrNew([
                'student_id' => $student->id,
                'session_id' => $validated['session_id'],
                'term_id' => $validated['term_id'],
            ]);

            $attributes = [
                'class_id' => $validated['class_id'],
                'status' => 'generated',
                'workflow_status' => ReportCard::WORKFLOW_DRAFT,
                'review_required' => true,
                'published_at' => null,
                'scores_updated_at' => now(),
                'next_term_begins' => $term->next_term_begins,
            ];

            if (! $reportCard->exists) {
                $attributes['class_teacher_name'] = $this->defaultClassTeacherName($validated['class_id']);
                $attributes['head_teacher_name'] = $this->defaultReportAuthorityName($validated['class_id']);
            }

            $reportCard->applyGeneratedSummary($summary, $attributes);
            $reportCard->save();

            DB::commit();

            return redirect()->route('admin.report-cards.preview', $reportCard->id)
                ->with('success', 'Manual scores saved and report card generated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Error saving manual report card: ' . $e->getMessage())->withInput();
        }
    }
    
    // ========== GENERATE REPORT CARD FOR STUDENT ==========
    
    public function generate(Request $request, $studentId)
    {
        $this->authorizeReportCardManagement();

        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $session = $request->filled('session_id')
            ? Session::findOrFail($request->session_id)
            : $activeSession;
        $term = $request->filled('term_id')
            ? Term::findOrFail($request->term_id)
            : $activeTerm;
        
        if (!$session || !$term) {
            return redirect()->back()->with('error', 'No active session or term. Please contact admin.');
        }
        
        $student = User::with('class')->findOrFail($studentId);
        $this->authorizeClassAccess($student->class_id);
        
        // Get all scores for this student
        $scores = Score::where('student_id', $studentId)
            ->where('session_id', $session->id)
            ->where('term_id', $term->id)
            ->with('subject')
            ->join('subjects', 'scores.subject_id', '=', 'subjects.id')
            ->select('scores.*')
            ->orderBy('subjects.name')
            ->get();
        
        if ($scores->isEmpty()) {
            return redirect()->back()->with('error', 'No scores found for this student.');
        }
        
        // Generate or update report card
        $summary = ReportCard::generateForStudent($studentId, $session->id, $term->id);
        
        $reportCard = ReportCard::firstOrNew([
            'student_id' => $studentId,
            'session_id' => $session->id,
            'term_id' => $term->id,
        ]);

        $attributes = [
            'class_id' => $student->class_id,
            'status' => 'generated',
            'workflow_status' => ReportCard::WORKFLOW_DRAFT,
            'review_required' => true,
            'published_at' => null,
            'scores_updated_at' => now(),
            'next_term_begins' => $term->next_term_begins,
        ];

        if (! $reportCard->exists) {
            $attributes['class_teacher_name'] = $this->defaultClassTeacherName($student->class_id);
            $attributes['head_teacher_name'] = $this->defaultReportAuthorityName($student->class_id);
        }

        $reportCard->applyGeneratedSummary($summary, $attributes);
        $reportCard->save();

        return redirect()->route('admin.report-cards.preview', $reportCard->id)
            ->with('success', 'Report card generated successfully!');
    }
    
    // ========== PREVIEW REPORT CARD ==========
    
    public function preview($reportCardId)
    {
        $this->authorizeReportCardManagement();

        $reportCard = ReportCard::with(['student.class', 'session', 'term', 'reviewer', 'academicReviewer'])
            ->findOrFail($reportCardId);
        $this->authorizeReportCardAccess($reportCard);
        
        // Get scores
        $scores = Score::where('student_id', $reportCard->student_id)
            ->where('session_id', $reportCard->session_id)
            ->where('term_id', $reportCard->term_id)
            ->with('subject')
            ->join('subjects', 'scores.subject_id', '=', 'subjects.id')
            ->select('scores.*')
            ->orderBy('subjects.name')
            ->get();
        
        // Get school settings (use helper to ensure defaults are available)
        $schoolSettings = \App\Models\SchoolSettings::getSettings();
        
        // Color schemes
        $colorSchemes = [
            'blue' => ['primary' => '#1E40AF', 'secondary' => '#3B82F6', 'light' => '#DBEAFE'],
            'green' => ['primary' => '#15803D', 'secondary' => '#22C55E', 'light' => '#DCFCE7'],
            'brown' => ['primary' => '#78350F', 'secondary' => '#A16207', 'light' => '#FEF3C7'],
            'pink' => ['primary' => '#BE123C', 'secondary' => '#F472B6', 'light' => '#FCE7F3'],
            'purple' => ['primary' => '#6B21A8', 'secondary' => '#A855F7', 'light' => '#F3E8FF'],
        ];
        
        $selectedColor = $colorSchemes[$reportCard->theme_color ?? 'blue'] ?? $colorSchemes['blue'];
        $colors = ['blue', 'green', 'brown', 'pink', 'purple'];
        
        $canEditScores = auth()->user()->canReviewReportCards() && $reportCard->isSubmittedForReview();
        $canSubmitForReview = $this->canCurrentUserSubmitForReview($reportCard);

        return view('admin.report-cards.preview', compact('reportCard', 'scores', 'colors', 'schoolSettings', 'selectedColor', 'canEditScores', 'canSubmitForReview'));
    }

    public function visualPreview($reportCardId)
    {
        $this->authorizeReportCardManagement();

        $reportCard = ReportCard::with(['student.class', 'session', 'term'])
            ->findOrFail($reportCardId);
        $this->authorizeReportCardAccess($reportCard);

        $scores = Score::where('student_id', $reportCard->student_id)
            ->where('session_id', $reportCard->session_id)
            ->where('term_id', $reportCard->term_id)
            ->where('total', '>', 0)  // Exclude zero scores (learner not taking subject)
            ->with('subject')
            ->join('subjects', 'scores.subject_id', '=', 'subjects.id')
            ->select('scores.*')
            ->orderBy('subjects.name')
            ->get();

        $schoolSettings = \App\Models\SchoolSettings::getSettings();

        $colorSchemes = [
            'blue' => ['primary' => '#1E40AF', 'secondary' => '#3B82F6', 'light' => '#DBEAFE'],
            'green' => ['primary' => '#15803D', 'secondary' => '#22C55E', 'light' => '#DCFCE7'],
            'brown' => ['primary' => '#78350F', 'secondary' => '#A16207', 'light' => '#FEF3C7'],
            'pink' => ['primary' => '#BE123C', 'secondary' => '#F472B6', 'light' => '#FCE7F3'],
            'purple' => ['primary' => '#6B21A8', 'secondary' => '#A855F7', 'light' => '#F3E8FF'],
        ];

        $selectedColor = $colorSchemes[$reportCard->theme_color ?? 'blue'] ?? $colorSchemes['blue'];
        $renderMode = 'browser';

        return view('admin.report-cards.nigerian-pdf', compact(
            'reportCard',
            'scores',
            'schoolSettings',
            'selectedColor',
            'renderMode'
        ));
    }
    
    // ========== DOWNLOAD PDF ==========
    
    public function downloadPDF($reportCardId, Request $request)
    {
        $this->authorizeReportCardManagement();

        $reportCard = ReportCard::with(['student.class', 'session', 'term'])
            ->findOrFail($reportCardId);
        $this->authorizeReportCardAccess($reportCard);
        
        // Get color theme
        $color = $request->get('color', $reportCard->theme_color ?? 'blue');
        
        // Update color if changed
        if ($reportCard->theme_color != $color) {
            $reportCard->update(['theme_color' => $color]);
        }
        
        // Get scores (excluding zero scores - learners not taking those subjects)
        $scores = Score::where('student_id', $reportCard->student_id)
            ->where('session_id', $reportCard->session_id)
            ->where('term_id', $reportCard->term_id)
            ->where('total', '>', 0)  // Exclude zero scores
            ->with('subject')
            ->join('subjects', 'scores.subject_id', '=', 'subjects.id')
            ->select('scores.*')
            ->orderBy('subjects.name')
            ->get();
        
        // Get school settings (use helper to ensure defaults are available)
        $schoolSettings = \App\Models\SchoolSettings::getSettings();
        
        // Color schemes
        $colorSchemes = [
            'blue' => ['primary' => '#1E40AF', 'secondary' => '#3B82F6', 'light' => '#DBEAFE'],
            'green' => ['primary' => '#15803D', 'secondary' => '#22C55E', 'light' => '#DCFCE7'],
            'brown' => ['primary' => '#78350F', 'secondary' => '#A16207', 'light' => '#FEF3C7'],
            'pink' => ['primary' => '#BE123C', 'secondary' => '#F472B6', 'light' => '#FCE7F3'],
            'purple' => ['primary' => '#6B21A8', 'secondary' => '#A855F7', 'light' => '#F3E8FF'],
        ];
        
        $selectedColor = $colorSchemes[$color] ?? $colorSchemes['blue'];
        $renderMode = 'pdf';
        
        // Generate PDF
        $pdf = Pdf::loadView('admin.report-cards.nigerian-pdf', compact(
            'reportCard',
            'scores',
            'schoolSettings',
            'selectedColor',
            'renderMode'
        ));
        
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultPaperSize' => 'a4',
        ]);
        
        $filenameBase = "Report_Card_{$reportCard->student->name}_{$reportCard->session->name}_{$reportCard->term->name}";
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filenameBase) . '.pdf';
        
        // Save PDF path
        $reportCard->update(['pdf_path' => $filename]);
        
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
    
    public function update(Request $request, $reportCardId)
    {
        $this->authorizeReportCardManagement();

        $reportCard = ReportCard::findOrFail($reportCardId);
        $this->authorizeReportCardAccess($reportCard);

        $validated = $request->validate([
            'days_school_opened' => 'required|integer|min:0',
            'days_present' => 'required|integer|min:0',
            'class_teacher_comment' => 'nullable|string|max:1000',
            'class_teacher_name' => 'nullable|string|max:255',
            'class_teacher_signature' => 'nullable|string|max:255',
            'head_teacher_comment' => 'nullable|string|max:1000',
            'head_teacher_name' => 'nullable|string|max:255',
            'head_teacher_signature' => 'nullable|string|max:255',
            'principal_signature_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'theme_color' => 'nullable|in:blue,green,brown,pink,purple',
            'affective_domain' => 'nullable|array',
            'affective_domain.*' => 'nullable|integer|min:1|max:5',
            'psychomotor_skills' => 'nullable|array',
            'psychomotor_skills.*' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validated['days_present'] > $validated['days_school_opened']) {
            return back()->withErrors([
                'days_present' => 'Days present cannot be greater than days school opened.',
            ])->withInput();
        }

        // Auto-calculate days_absent from days_school_opened and days_present
        $validated['days_absent'] = max(0, $validated['days_school_opened'] - $validated['days_present']);

        $validated['attendance_percentage'] = $validated['days_school_opened'] > 0
            ? round(($validated['days_present'] / $validated['days_school_opened']) * 100, 2)
            : 0;

        $validated['affective_domain'] = collect($request->input('affective_domain', []))
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => (int) $value)
            ->all();

        $validated['psychomotor_skills'] = collect($request->input('psychomotor_skills', []))
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => (int) $value)
            ->all();

        if (! auth()->user()->canReviewReportCards()) {
            $validated['workflow_status'] = ReportCard::WORKFLOW_DRAFT;
            $validated['review_required'] = true;
        }

        $validated['reviewed_at'] = now();
        $validated['reviewed_by'] = auth()->id();

        unset($validated['principal_signature_image']);

        $reportCard->update($validated);

        if (auth()->user()->isAdmin() && $request->hasFile('principal_signature_image')) {
            $schoolSettings = \App\Models\SchoolSettings::getSettings();

            if ($schoolSettings->principal_signature) {
                Storage::disk('public')->delete($schoolSettings->principal_signature);
            }

            $schoolSettings->update([
                'principal_signature' => $request->file('principal_signature_image')->store('signatures/principal', 'public'),
            ]);
        }

        return redirect()->route('admin.report-cards.preview', $reportCard->id)
            ->with('success', 'Report card details updated successfully.');
    }

    public function updateScores(Request $request, $reportCardId)
    {
        $this->authorizeAcademicReview();

        $reportCard = ReportCard::findOrFail($reportCardId);
        $this->authorizeReviewerClassAccess($reportCard);
        abort_unless($reportCard->isSubmittedForReview(), 403, 'Scores can be edited only while the report card is submitted for academic review.');

        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*.id' => 'required|exists:scores,id',
            'scores.*.ca1' => 'nullable|numeric|min:0|max:30',
            'scores.*.ca2' => 'nullable|numeric|min:0|max:10',
            'scores.*.exam' => 'nullable|numeric|min:0|max:60',
        ]);

        DB::transaction(function () use ($validated, $reportCard) {
            foreach ($validated['scores'] as $scoreData) {
                $score = Score::where('student_id', $reportCard->student_id)
                    ->where('session_id', $reportCard->session_id)
                    ->where('term_id', $reportCard->term_id)
                    ->findOrFail($scoreData['id']);

                $score->update([
                    'ca1' => $scoreData['ca1'] ?? 0,
                    'ca2' => $scoreData['ca2'] ?? 0,
                    'exam' => $scoreData['exam'] ?? 0,
                    'status' => 'submitted',
                ]);

                Score::calculatePositions($score->subject_id, $score->class_id, $score->session_id, $score->term_id);
                $classAverage = Score::calculateClassAverage($score->subject_id, $score->class_id, $score->session_id, $score->term_id);
                Score::where('subject_id', $score->subject_id)
                    ->where('class_id', $score->class_id)
                    ->where('session_id', $score->session_id)
                    ->where('term_id', $score->term_id)
                    ->update(['class_average' => $classAverage]);
            }

            $summary = ReportCard::generateForStudent($reportCard->student_id, $reportCard->session_id, $reportCard->term_id);
            if ($summary) {
                $reportCard->applyGeneratedSummary($summary, [
                    'review_required' => true,
                    'scores_updated_at' => now(),
                    'academic_reviewed_by' => null,
                    'academic_reviewed_at' => null,
                    'academic_rejection_reason' => null,
                    'published_at' => null,
                    'status' => 'generated',
                ]);
                $reportCard->save();
            }
        });

        return redirect()->route('admin.report-cards.preview', $reportCard->id)
            ->with('success', 'Scores updated. Please review the refreshed report card before approving.');
    }

    public function submitForReview($reportCardId)
    {
        $reportCard = ReportCard::with(['student', 'session', 'term', 'class'])->findOrFail($reportCardId);
        abort_unless($this->canCurrentUserSubmitForReview($reportCard), 403);

        $reportCard->update([
            'workflow_status' => ReportCard::WORKFLOW_SUBMITTED,
            'submitted_for_review_at' => now(),
            'academic_reviewed_by' => null,
            'academic_reviewed_at' => null,
            'academic_rejection_reason' => null,
            'status' => 'generated',
            'published_at' => null,
            'review_required' => true,
        ]);

        $this->notifyAcademicReviewers($reportCard);

        return redirect()->back()->with('success', 'Report card submitted for academic review. Reviewers have been notified.');
    }

    public function approveAcademicReview(Request $request, $reportCardId)
    {
        $this->authorizeAcademicReview();

        $reportCard = ReportCard::with(['student', 'session', 'term', 'class'])->findOrFail($reportCardId);
        $this->authorizeReviewerClassAccess($reportCard);
        abort_unless($reportCard->isSubmittedForReview(), 403);

        // Check if reviewer is bypassing missing scores check
        $bypass = $request->input('bypass_missing_scores') === 'true';
        
        $readinessErrors = $this->publicationReadinessErrors($reportCard, false, $bypass);
        if ($readinessErrors->isNotEmpty()) {
            return back()->withErrors(['review' => $readinessErrors->implode(' ')]);
        }

        $reportCard->update([
            'workflow_status' => ReportCard::WORKFLOW_ACADEMIC_APPROVED,
            'review_required' => false,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'academic_reviewed_by' => auth()->id(),
            'academic_reviewed_at' => now(),
            'academic_rejection_reason' => null,
        ]);

        $this->notifyAdmins($reportCard, 'Report card ready for fee verification', "{$reportCard->student->name}'s report card has been academically approved and is ready for fee clearance/final publishing.");

        return redirect()->back()->with('success', 'Academic review approved. Admin can now verify fee clearance and publish.');
    }

    public function rejectAcademicReview(Request $request, $reportCardId)
    {
        $this->authorizeAcademicReview();

        $validated = $request->validate([
            'academic_rejection_reason' => 'required|string|max:2000',
        ]);

        $reportCard = ReportCard::with(['student', 'session', 'term', 'class'])->findOrFail($reportCardId);
        $this->authorizeReviewerClassAccess($reportCard);
        abort_unless($reportCard->isSubmittedForReview(), 403);

        $reportCard->update([
            'workflow_status' => ReportCard::WORKFLOW_REJECTED,
            'review_required' => true,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'academic_reviewed_by' => auth()->id(),
            'academic_reviewed_at' => now(),
            'academic_rejection_reason' => $validated['academic_rejection_reason'],
            'published_at' => null,
            'status' => 'generated',
        ]);

        $this->notifyFormTeacher($reportCard, 'Report card rejected', "{$reportCard->student->name}'s report card was rejected. Reason: {$validated['academic_rejection_reason']}");

        return redirect()->back()->with('success', 'Report card rejected. The form teacher has been notified.');
    }

    public function bulkApproveAcademicReview(Request $request)
    {
        $this->authorizeAcademicReview();

        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        abort_unless(in_array((int) $validated['class_id'], $this->reviewerClassIdsFor(auth()->user()), true), 403);

        $approved = 0;
        $skipped = 0;

        ReportCard::with(['student', 'session', 'term', 'class'])
            ->where('class_id', $validated['class_id'])
            ->where('session_id', $validated['session_id'])
            ->where('term_id', $validated['term_id'])
            ->where('workflow_status', ReportCard::WORKFLOW_SUBMITTED)
            ->get()
            ->each(function (ReportCard $reportCard) use (&$approved, &$skipped) {
                if ($this->publicationReadinessErrors($reportCard, false)->isNotEmpty()) {
                    $skipped++;
                    return;
                }

                $reportCard->update([
                    'workflow_status' => ReportCard::WORKFLOW_ACADEMIC_APPROVED,
                    'review_required' => false,
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id(),
                    'academic_reviewed_by' => auth()->id(),
                    'academic_reviewed_at' => now(),
                    'academic_rejection_reason' => null,
                ]);
                $approved++;
            });

        return back()->with('success', "{$approved} report cards approved. {$skipped} skipped.");
    }

    public function bulkPublish(Request $request)
    {
        $this->authorizeReportCardManagement();
        abort_unless(auth()->user()->isAdmin(), 403, 'Only admin can publish final report cards.');

        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        $published = 0;

        ReportCard::with(['student', 'session', 'term', 'class'])
            ->where('class_id', $validated['class_id'])
            ->where('session_id', $validated['session_id'])
            ->where('term_id', $validated['term_id'])
            ->where('workflow_status', ReportCard::WORKFLOW_ACADEMIC_APPROVED)
            ->where('status', '!=', 'published')
            ->get()
            ->each(function (ReportCard $reportCard) use (&$published) {
                $reportCard->publish();
                $published++;
            });

        return back()->with('success', "{$published} report cards published.");
    }

    public function updatePublication(Request $request, $reportCardId)
    {
        $this->authorizeReportCardManagement();

        $reportCard = ReportCard::findOrFail($reportCardId);
        abort_unless(auth()->user()->isAdmin(), 403, 'Only admin can publish final report cards.');

        if ($request->boolean('published')) {
            $reportCard->publish();
            $message = 'Report card published. Parents and students can view it only after fee clearance is approved.';
        } else {
            $reportCard->unpublish();
            $message = 'Report card unpublished. Parents and students can no longer view it.';
        }

        return redirect()->back()->with('success', $message);
    }

    private function publicationReadinessErrors(ReportCard $reportCard, bool $requireAcademicApproval = true, bool $bypassMissingScores = false): \Illuminate\Support\Collection
    {
        $reportCard->loadMissing(['class.subjects', 'student', 'session', 'term']);

        $errors = collect();

        if ($requireAcademicApproval && $reportCard->review_required) {
            $errors->push('This report card has updated scores and must be reviewed before publishing.');
        }

        if ($requireAcademicApproval && ! $reportCard->isAcademicallyApproved()) {
            $errors->push('Academic reviewer must approve this report card before final publishing.');
        }

        if ($requireAcademicApproval && ! $reportCard->hasFeeClearance()) {
            $errors->push('Fee clearance must be approved before final publishing.');
        }

        if ((int) $reportCard->days_school_opened <= 0) {
            $errors->push('Enter attendance before publishing.');
        }

        if (!filled($reportCard->class_teacher_comment) || !filled($reportCard->head_teacher_comment)) {
            $errors->push('Form teacher and head teacher/principal remarks are required before publishing.');
        }

        // Only check for missing scores if not bypassed
        if (!$bypassMissingScores) {
            $expectedSubjectIds = $reportCard->class?->subjects()
                ->active()
                ->pluck('subjects.id')
                ->map(fn ($id) => (int) $id)
                ->values() ?? collect();

            if ($expectedSubjectIds->isNotEmpty()) {
                $scoredSubjectIds = Score::where('student_id', $reportCard->student_id)
                    ->where('session_id', $reportCard->session_id)
                    ->where('term_id', $reportCard->term_id)
                    ->where('status', '!=', 'draft')
                    ->pluck('subject_id')
                    ->map(fn ($id) => (int) $id);

                $missingCount = $expectedSubjectIds->diff($scoredSubjectIds)->count();

                if ($missingCount > 0) {
                    $errors->push("{$missingCount} assigned subject score(s) are missing.");
                }
            }
        }

        return $errors;
    }

    // ========== BULK GENERATE FOR CLASS ==========
    
    public function bulkGenerate(Request $request)
    {
        $this->authorizeReportCardManagement();

        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'nullable|exists:academic_sessions,id',
            'term_id' => 'nullable|exists:terms,id',
        ]);
        
        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $session = $request->filled('session_id')
            ? Session::findOrFail($request->session_id)
            : $activeSession;
        $term = $request->filled('term_id')
            ? Term::findOrFail($request->term_id)
            : $activeTerm;

        if (!$session || !$term) {
            return redirect()->back()->with('error', 'No session or term selected.');
        }

        $this->authorizeClassAccess((int) $request->class_id);
        
        $students = User::where('class_id', $request->class_id)
            ->where('role', 'student')
            ->get();
        
        $generated = 0;
        
        foreach ($students as $student) {
            $summary = ReportCard::generateForStudent($student->id, $session->id, $term->id);
            
            if ($summary) {
                $reportCard = ReportCard::firstOrNew([
                    'student_id' => $student->id,
                    'session_id' => $session->id,
                    'term_id' => $term->id,
                ]);

                $attributes = [
                    'class_id' => $request->class_id,
                    'status' => 'generated',
                    'workflow_status' => ReportCard::WORKFLOW_DRAFT,
                    'review_required' => true,
                    'published_at' => null,
                    'scores_updated_at' => now(),
                    'next_term_begins' => $term->next_term_begins,
                ];

                if (! $reportCard->exists) {
                    $attributes['class_teacher_name'] = $this->defaultClassTeacherName($request->class_id);
                    $attributes['head_teacher_name'] = $this->defaultReportAuthorityName($request->class_id);
                }

                $reportCard->applyGeneratedSummary($summary, $attributes);
                $reportCard->save();
                
                $generated++;
            }
        }
        
        return redirect()->back()->with('success', "Generated {$generated} report cards!");
    }
    
    private function authorizeClassAccess(?int $classId): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return;
        }

        $classIds = $this->formTeacherClassIdsFor($user->id);
        abort_unless($classId && in_array($classId, $classIds, true), 403, 'Only admins and the assigned form teacher can manage this report card.');
    }

    private function authorizeReportCardAccess(ReportCard $reportCard): void
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->canReviewReportCards()) {
            if ($user->isTeacher() && $user->canReviewReportCards()) {
                abort_unless(in_array($reportCard->class_id, $this->reviewerClassIdsFor($user), true), 403, 'You are not assigned to review this class.');
            }
            return;
        }

        $this->authorizeClassAccess($reportCard->class_id);
    }

    private function authorizeReportCardManagement(): void
    {
        $user = auth()->user();

        abort_unless(
            $user->isAdmin() || $user->canReviewReportCards() || ! empty($this->formTeacherClassIdsFor($user->id)),
            403,
            'Only admins, academic reviewers, and active form teachers can manage report cards.'
        );
    }

    private function authorizeManualReportCardBuilderAccess(?int $classId = null): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        $classScoreEntryClassIds = $this->classScoreEntryFormTeacherClassesFor($user)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($classId) {
            abort_unless(
                in_array($classId, $classScoreEntryClassIds, true),
                403,
                'Secondary teachers should enter paper scores from Teacher Scores > Paper / Manual Scores.'
            );

            return;
        }

        abort_unless(
            ! empty($classScoreEntryClassIds),
            403,
            'Secondary teachers should enter paper scores from Teacher Scores > Paper / Manual Scores.'
        );
    }

    private function authorizeAcademicReview(): void
    {
        abort_unless(auth()->user()->canReviewReportCards(), 403, 'Only assigned academic reviewers can review report cards.');
    }

    private function authorizeReviewerClassAccess(ReportCard $reportCard): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        abort_unless(
            $user->canReviewReportCards() && in_array($reportCard->class_id, $this->reviewerClassIdsFor($user), true),
            403,
            'You are not assigned to review this class.'
        );
    }

    private function canCurrentUserSubmitForReview(ReportCard $reportCard): bool
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return in_array($reportCard->workflow_status, [ReportCard::WORKFLOW_DRAFT, ReportCard::WORKFLOW_REJECTED, null], true);
        }

        return $user->isTeacher()
            && in_array($reportCard->class_id, $this->formTeacherClassIdsFor($user->id), true)
            && in_array($reportCard->workflow_status, [ReportCard::WORKFLOW_DRAFT, ReportCard::WORKFLOW_REJECTED, null], true);
    }

    private function notifyAcademicReviewers(ReportCard $reportCard): void
    {
        $reviewers = User::where('role', 'teacher')
            ->where('can_review_report_cards', true)
            ->whereHas('reportReviewClasses', fn ($query) => $query->whereKey($reportCard->class_id))
            ->get();

        foreach ($reviewers as $reviewer) {
            Message::create([
                'sender_id' => auth()->id(),
                'recipient_id' => $reviewer->id,
                'subject' => 'Report card submitted for review',
                'body' => "{$reportCard->student->name}'s report card for {$reportCard->class->display_name} has been submitted for academic review.",
            ]);
        }
    }

    private function notifyFormTeacher(ReportCard $reportCard, string $subject, string $body): void
    {
        $teacherId = FormTeacher::where('class_id', $reportCard->class_id)
            ->where('is_active', true)
            ->value('teacher_id');

        if ($teacherId) {
            Message::create([
                'sender_id' => auth()->id(),
                'recipient_id' => $teacherId,
                'subject' => $subject,
                'body' => $body,
            ]);
        }
    }

    private function notifyAdmins(ReportCard $reportCard, string $subject, string $body): void
    {
        User::where('role', 'admin')->get()->each(function (User $admin) use ($subject, $body) {
            Message::create([
                'sender_id' => auth()->id(),
                'recipient_id' => $admin->id,
                'subject' => $subject,
                'body' => $body,
            ]);
        });
    }

    private function formTeacherClassIdsFor(int $teacherId): array
    {
        return FormTeacher::where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->pluck('class_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function formTeacherClassesFor(User $user)
    {
        if (! $user->isTeacher()) {
            return collect();
        }

        return FormTeacher::with('schoolClass')
            ->where('teacher_id', $user->id)
            ->where('is_active', true)
            ->get()
            ->pluck('schoolClass')
            ->filter()
            ->values();
    }

    private function earlyPrimaryFormTeacherClassesFor(User $user)
    {
        return $this->formTeacherClassesFor($user)
            ->filter(fn ($class) => $class && $this->isEarlyYearsOrPrimaryClass($class))
            ->values();
    }

    private function classScoreEntryFormTeacherClassesFor(User $user)
    {
        return $this->formTeacherClassesFor($user)
            ->filter(fn ($class) => $class && in_array($class->section_key, ['creche', 'primary', 'other'], true))
            ->values();
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

    private function subjectsForManualReportClass(SchoolClass $class)
    {
        $subjects = $class->subjects()->active()->ordered()->get();

        if ($subjects->isNotEmpty()) {
            return $subjects;
        }

        $subjects = Subject::active()
            ->whereIn('class_level', $this->subjectClassLevelCandidates($class))
            ->ordered()
            ->get();

        if ($subjects->isNotEmpty()) {
            return $subjects;
        }

        $user = auth()->user();

        if ($user?->isTeacher() && $this->isEarlyYearsOrPrimaryClass($class) && $this->teacherOwnsClass($user, $class)) {
            return $user->subjects()
                ->active()
                ->ordered()
                ->get();
        }

        return $subjects;
    }

    private function teacherOwnsClass(User $user, SchoolClass $class): bool
    {
        return FormTeacher::where('teacher_id', $user->id)
            ->where('class_id', $class->id)
            ->where('is_active', true)
            ->exists();
    }

    private function reviewerClassIdsFor(User $user): array
    {
        if (! $user->canReviewReportCards()) {
            return [];
        }

        if ($user->isAdmin()) {
            return SchoolClass::pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return $user->reportReviewClasses()
            ->pluck('school_classes.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function defaultClassTeacherName(?int $classId): string
    {
        if (!$classId) {
            return '';
        }

        return FormTeacher::with('teacher')
            ->where('class_id', $classId)
            ->where('is_active', true)
            ->first()
            ?->teacher
            ?->name ?? '';
    }

    private function defaultReportAuthorityName(?int $classId): string
    {
        if (!$classId) {
            return '';
        }

        $authority = $this->reportAuthorityForClass(SchoolClass::find($classId));

        return $authority?->name ?? '';
    }

    private function reportAuthorityForClass(?SchoolClass $class): ?User
    {
        $role = $class?->reportAuthorityRole() ?? 'head_teacher';

        return User::where('report_authority_role', $role)
            ->whereIn('role', ['admin', 'teacher'])
            ->orderBy('name')
            ->first();
    }
}
