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
        $reviewClassIds = $this->reviewerClassIdsFor($user);
        $reportCards = ReportCard::with(['student', 'session', 'term', 'class'])
            ->when($user->isTeacher() && ! $user->canReviewReportCards(), function ($query) use ($user) {
                $query->whereIn('class_id', $this->formTeacherClassIdsFor($user->id));
            })
            ->when($user->isTeacher() && $user->canReviewReportCards(), function ($query) use ($reviewClassIds) {
                $query->whereIn('class_id', $reviewClassIds);
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
            ->when($user->isTeacher() && ! $user->canReviewReportCards(), function ($query) use ($user) {
                $query->whereIn('class_id', $this->formTeacherClassIdsFor($user->id));
            })
            ->when($user->isTeacher() && $user->canReviewReportCards(), function ($query) use ($reviewClassIds) {
                $query->whereIn('class_id', $reviewClassIds);
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

        $classes = SchoolClass::when($user->isTeacher() && ! $user->canReviewReportCards(), function ($query) use ($user) {
            $query->whereIn('id', $this->formTeacherClassIdsFor($user->id));
        })->when($user->isTeacher() && $user->canReviewReportCards(), function ($query) use ($reviewClassIds) {
            $query->whereIn('id', $reviewClassIds);
        })->orderBy('name')->get();

        $sessions = Session::orderByDesc('start_date')->get();
        $terms = Term::with('session')->orderByDesc('start_date')->get();
        $selectedSession = $sessions->firstWhere('id', (int) $selectedSessionId);
        $selectedTerm = $terms->firstWhere('id', (int) $selectedTermId);

        return view('admin.report-cards.index', compact(
            'reportCards',
            'activeSession',
            'activeTerm',
            'students',
            'classes',
            'sessions',
            'terms',
            'selectedSession',
            'selectedTerm'
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

        $reportCards = ReportCard::with(['student', 'session', 'term', 'class', 'academicReviewer'])
            ->whereIn('class_id', $reviewClassIds)
            ->when($selectedSessionId, fn ($query) => $query->where('session_id', $selectedSessionId))
            ->when($selectedTermId, fn ($query) => $query->where('term_id', $selectedTermId))
            ->whereIn('workflow_status', [
                ReportCard::WORKFLOW_SUBMITTED,
                ReportCard::WORKFLOW_REJECTED,
                ReportCard::WORKFLOW_ACADEMIC_APPROVED,
            ])
            ->latest()
            ->paginate(20);

        $sessions = Session::orderByDesc('start_date')->get();
        $terms = Term::with('session')->orderByDesc('start_date')->get();
        $selectedSession = $sessions->firstWhere('id', (int) $selectedSessionId);
        $selectedTerm = $terms->firstWhere('id', (int) $selectedTermId);

        return view('admin.report-cards.reviews', compact('reportCards', 'sessions', 'terms', 'selectedSession', 'selectedTerm'));
    }

    public function earlyPrimaryLearners(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isTeacher(), 403);

        $formClasses = $this->earlyPrimaryFormTeacherClassesFor($user);
        abort_unless($formClasses->isNotEmpty(), 403, 'This score-entry page is for Early Years and Primary form teachers.');

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
            $subjects = $selectedClass->subjects()->active()->ordered()->get();

            if ($subjects->isEmpty()) {
                $subjects = Subject::active()
                    ->whereIn('class_level', $this->subjectClassLevelCandidates($selectedClass))
                    ->ordered()
                    ->get();
            }

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

                Score::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $scoreData['subject_id'],
                        'session_id' => $validated['session_id'],
                        'term_id' => $validated['term_id'],
                    ],
                    [
                        'class_id' => $validated['class_id'],
                        'teacher_id' => $teacherId,
                        'ca1' => $scoreData['ca1'] ?? 0,
                        'ca2' => $scoreData['ca2'] ?? 0,
                        'ca3' => 0,
                        'exam' => $scoreData['exam'] ?? 0,
                        'status' => 'submitted',
                    ]
                );

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

            $reportCard = ReportCard::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'session_id' => $validated['session_id'],
                    'term_id' => $validated['term_id'],
                ],
                array_merge($summary, [
                    'class_id' => $validated['class_id'],
                    'status' => 'generated',
                    'workflow_status' => ReportCard::WORKFLOW_DRAFT,
                    'review_required' => true,
                    'published_at' => null,
                    'scores_updated_at' => now(),
                    'days_school_opened' => 0,
                    'days_present' => 0,
                    'days_absent' => 0,
                    'attendance_percentage' => 0,
                    'class_teacher_name' => $this->defaultClassTeacherName($validated['class_id']),
                    'head_teacher_name' => '',
                    'next_term_begins' => $term->next_term_begins,
                ])
            );

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
            ->orderBy('subject_id')
            ->get();
        
        if ($scores->isEmpty()) {
            return redirect()->back()->with('error', 'No scores found for this student.');
        }
        
        // Generate or update report card
        $summary = ReportCard::generateForStudent($studentId, $session->id, $term->id);
        
        $reportCard = ReportCard::updateOrCreate(
            [
                'student_id' => $studentId,
                'session_id' => $session->id,
                'term_id' => $term->id,
            ],
            array_merge($summary, [
                'class_id' => $student->class_id,
                'status' => 'generated',
                'workflow_status' => ReportCard::WORKFLOW_DRAFT,
                'review_required' => true,
                'published_at' => null,
                'scores_updated_at' => now(),
                'days_school_opened' => 0,
                'days_present' => 0,
                'days_absent' => 0,
                'attendance_percentage' => 0,
                'class_teacher_name' => $this->defaultClassTeacherName($student->class_id),
                'head_teacher_name' => '',
                'next_term_begins' => $term->next_term_begins,
            ])
        );
        
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
            ->orderBy('subject_id')
            ->get();
        
        // Get school settings
        $schoolSettings = \App\Models\SchoolSettings::first() ?? new \App\Models\SchoolSettings();
        
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
            ->with('subject')
            ->orderBy('subject_id')
            ->get();

        $schoolSettings = \App\Models\SchoolSettings::first() ?? new \App\Models\SchoolSettings();

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
        
        // Get scores
        $scores = Score::where('student_id', $reportCard->student_id)
            ->where('session_id', $reportCard->session_id)
            ->where('term_id', $reportCard->term_id)
            ->with('subject')
            ->orderBy('subject_id')
            ->get();
        
        // Get school settings
        $schoolSettings = \App\Models\SchoolSettings::first() ?? new \App\Models\SchoolSettings();
        
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
            'days_absent' => 'required|integer|min:0',
            'class_teacher_comment' => 'nullable|string|max:1000',
            'class_teacher_name' => 'nullable|string|max:255',
            'class_teacher_signature' => 'nullable|string|max:255',
            'class_teacher_signature_date' => 'nullable|date',
            'head_teacher_comment' => 'nullable|string|max:1000',
            'head_teacher_name' => 'nullable|string|max:255',
            'head_teacher_signature' => 'nullable|string|max:255',
            'head_teacher_signature_date' => 'nullable|date',
            'next_term_begins' => 'nullable|date',
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

        if (($validated['days_present'] + $validated['days_absent']) > $validated['days_school_opened']) {
            return back()->withErrors([
                'days_absent' => 'Present plus absent days cannot be greater than days school opened.',
            ])->withInput();
        }

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

        $reportCard->update($validated);

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
                $reportCard->fill(array_merge($summary, [
                    'review_required' => true,
                    'scores_updated_at' => now(),
                    'academic_reviewed_by' => null,
                    'academic_reviewed_at' => null,
                    'academic_rejection_reason' => null,
                    'published_at' => null,
                    'status' => 'generated',
                ]))->save();
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

    public function approveAcademicReview($reportCardId)
    {
        $this->authorizeAcademicReview();

        $reportCard = ReportCard::with(['student', 'session', 'term', 'class'])->findOrFail($reportCardId);
        $this->authorizeReviewerClassAccess($reportCard);
        abort_unless($reportCard->isSubmittedForReview(), 403);

        $readinessErrors = $this->publicationReadinessErrors($reportCard, false);
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

    public function updatePublication(Request $request, $reportCardId)
    {
        $this->authorizeReportCardManagement();

        $reportCard = ReportCard::findOrFail($reportCardId);
        abort_unless(auth()->user()->isAdmin(), 403, 'Only admin can publish final report cards.');

        if ($request->boolean('published')) {
            $publishErrors = $this->publicationReadinessErrors($reportCard);

            if ($publishErrors->isNotEmpty()) {
                return redirect()->back()
                    ->withErrors(['published' => $publishErrors->implode(' ')])
                    ->withInput();
            }

            $reportCard->publish();
            $message = 'Report card published. Parents and students can view it only after fee clearance is approved.';
        } else {
            $reportCard->unpublish();
            $message = 'Report card unpublished. Parents and students can no longer view it.';
        }

        return redirect()->back()->with('success', $message);
    }

    private function publicationReadinessErrors(ReportCard $reportCard, bool $requireAcademicApproval = true): \Illuminate\Support\Collection
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
            $errors->push('Class teacher and head teacher remarks are required before publishing.');
        }

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
                ReportCard::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'session_id' => $session->id,
                        'term_id' => $term->id,
                    ],
                    array_merge($summary, [
                        'class_id' => $request->class_id,
                        'status' => 'generated',
                        'workflow_status' => ReportCard::WORKFLOW_DRAFT,
                        'review_required' => true,
                        'published_at' => null,
                        'scores_updated_at' => now(),
                        'days_school_opened' => 0,
                        'days_present' => 0,
                        'days_absent' => 0,
                        'attendance_percentage' => 0,
                        'class_teacher_name' => $this->defaultClassTeacherName($request->class_id),
                        'head_teacher_name' => '',
                        'next_term_begins' => $term->next_term_begins,
                    ])
                );
                
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
}
