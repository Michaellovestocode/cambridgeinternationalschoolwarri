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
        $reportCards = ReportCard::with(['student', 'session', 'term', 'class'])
            ->when($user->isTeacher(), function ($query) use ($user) {
                $query->whereIn('class_id', $this->formTeacherClassIdsFor($user->id));
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
            ->latest()
            ->paginate(20);

        $reportCards->appends($request->query());

        $students = User::where('role', 'student')
            ->when($user->isTeacher(), function ($query) use ($user) {
                $query->whereIn('class_id', $this->formTeacherClassIdsFor($user->id));
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

        $classes = SchoolClass::when($user->isTeacher(), function ($query) use ($user) {
            $query->whereIn('id', $this->formTeacherClassIdsFor($user->id));
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

        if ($selectedClassId) {
            $this->authorizeClassAccess((int) $selectedClassId);
        }

        $classes = SchoolClass::when($user->isTeacher(), function ($query) use ($user) {
            $query->whereIn('id', $this->formTeacherClassIdsFor($user->id));
        })->orderBy('name')->get();

        $students = User::where('role', 'student')
            ->when($user->isTeacher(), function ($query) use ($user) {
                $query->whereIn('class_id', $this->formTeacherClassIdsFor($user->id));
            })
            ->when($selectedClassId, fn ($query) => $query->where('class_id', $selectedClassId))
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
                $subjects = Subject::active()->ordered()->get();
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

        $reportCard = ReportCard::with(['student.class', 'session', 'term'])
            ->findOrFail($reportCardId);
        $this->authorizeClassAccess($reportCard->class_id);
        
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
        
        return view('admin.report-cards.preview', compact('reportCard', 'scores', 'colors', 'schoolSettings', 'selectedColor'));
    }

    public function visualPreview($reportCardId)
    {
        $this->authorizeReportCardManagement();

        $reportCard = ReportCard::with(['student.class', 'session', 'term'])
            ->findOrFail($reportCardId);
        $this->authorizeClassAccess($reportCard->class_id);

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
        $this->authorizeClassAccess($reportCard->class_id);
        
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
        $this->authorizeClassAccess($reportCard->class_id);

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

        $reportCard->update($validated);

        return redirect()->route('admin.report-cards.preview', $reportCard->id)
            ->with('success', 'Report card details updated successfully.');
    }

    public function updatePublication(Request $request, $reportCardId)
    {
        $this->authorizeReportCardManagement();

        $reportCard = ReportCard::findOrFail($reportCardId);
        $this->authorizeClassAccess($reportCard->class_id);

        if ($request->boolean('published')) {
            $reportCard->publish();
            $message = 'Report card published. Parents and students can view it only after fee clearance is approved.';
        } else {
            $reportCard->unpublish();
            $message = 'Report card unpublished. Parents and students can no longer view it.';
        }

        return redirect()->back()->with('success', $message);
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

    private function authorizeReportCardManagement(): void
    {
        $user = auth()->user();

        abort_unless(
            $user->isAdmin() || ! empty($this->formTeacherClassIdsFor($user->id)),
            403,
            'Only admins and active form teachers can manage report cards.'
        );
    }

    private function formTeacherClassIdsFor(int $teacherId): array
    {
        return FormTeacher::where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->pluck('class_id')
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
