<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentalReport;
use App\Models\DevelopmentalReportRating;
use App\Models\DevelopmentalSkill;
use App\Models\FormTeacher;
use App\Models\SchoolClass;
use App\Models\SchoolSettings;
use App\Models\Session;
use App\Models\Term;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DevelopmentalReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeDevelopmentalReportAccess($request->user());

        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $selectedSessionId = $request->input('session_id', $activeSession?->id);
        $selectedTermId = $request->input('term_id', $activeTerm?->id);
        $classIds = $this->manageableClassIds($request->user());

        $classes = SchoolClass::whereIn('id', $classIds)->orderBy('name')->get();
        $selectedClassId = $request->input('class_id');

        if ($selectedClassId) {
            abort_unless(in_array((int) $selectedClassId, $classIds, true), 403);
        } else {
            $selectedClassId = $classes->first()?->id;
        }

        $students = collect();
        $reports = collect();

        if ($selectedClassId && $selectedSessionId && $selectedTermId) {
            $students = User::where('role', 'student')
                ->where('class_id', $selectedClassId)
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = trim($request->search);
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('registration_number', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->get();

            $reports = DevelopmentalReport::whereIn('student_id', $students->pluck('id'))
                ->where('session_id', $selectedSessionId)
                ->where('term_id', $selectedTermId)
                ->get()
                ->keyBy('student_id');
        }

        $publishableCount = $reports->where('status', DevelopmentalReport::STATUS_SUBMITTED)->count();
        $sessions = Session::orderByDesc('start_date')->get();
        $terms = Term::with('session')->orderByDesc('start_date')->get();

        return view('admin.developmental-reports.index', compact(
            'classes',
            'students',
            'reports',
            'sessions',
            'terms',
            'selectedClassId',
            'selectedSessionId',
            'selectedTermId',
            'publishableCount'
        ));
    }

    public function bulkPublish(Request $request)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->canReviewReportCards(), 403);

        $validated = $request->validate([
            'class_id' => ['required', 'exists:school_classes,id'],
            'session_id' => ['required', 'exists:academic_sessions,id'],
            'term_id' => ['required', 'exists:terms,id'],
        ]);

        abort_unless(in_array((int) $validated['class_id'], $this->manageableClassIds($request->user()), true), 403);

        $publishedCount = DevelopmentalReport::where('class_id', $validated['class_id'])
            ->where('session_id', $validated['session_id'])
            ->where('term_id', $validated['term_id'])
            ->where('status', DevelopmentalReport::STATUS_SUBMITTED)
            ->update([
                'status' => DevelopmentalReport::STATUS_PUBLISHED,
                'published_at' => now(),
                'published_by' => $request->user()->id,
            ]);

        return back()->with('success', "{$publishedCount} developmental report(s) published.");
    }

    public function edit(Request $request, User $student)
    {
        $this->authorizeDevelopmentalReportAccess($request->user());
        abort_unless($student->role === 'student' && $student->class, 404);
        $this->authorizeStudent($request->user(), $student);

        $session = Session::findOrFail($request->input('session_id', Session::getActive()?->id));
        $term = Term::findOrFail($request->input('term_id', Term::getActive()?->id));
        $report = $this->firstOrCreateReport($student, $session, $term);
        $report->load('ratings');

        $skillsBySection = DevelopmentalSkill::active()
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');
        $ratings = $report->ratings->pluck('rating', 'developmental_skill_id');
        $ratingLabels = DevelopmentalReport::ratingLabels();

        return view('admin.developmental-reports.edit', compact('student', 'report', 'skillsBySection', 'ratings', 'ratingLabels'));
    }

    public function update(Request $request, DevelopmentalReport $developmentalReport)
    {        $this->authorizeDevelopmentalReportAccess($request->user());        $this->authorizeDevelopmentalReportAccess($request->user());
        $developmentalReport->load(['student.class']);
        $this->authorizeReport($request->user(), $developmentalReport);

        abort_if($developmentalReport->isPublished() && ! $request->user()->isAdmin(), 403, 'Published reports can only be changed by an admin.');

        $validated = $request->validate([
            'days_school_opened' => ['nullable', 'integer', 'min:0', 'max:250'],
            'days_present' => ['nullable', 'integer', 'min:0', 'max:250'],
            'class_teacher_remark' => ['nullable', 'string', 'max:1200'],
            'authority_remark' => ['nullable', 'string', 'max:1200'],
            'ratings' => ['array'],
            'ratings.*' => ['nullable', 'in:Q0,Q1,Q2,Q3,Q4'],
            'submit' => ['nullable', 'boolean'],
        ]);

        $daysSchoolOpened = $validated['days_school_opened'] ?? null;
        $daysPresent = $validated['days_present'] ?? null;
        $daysAbsent = null;
        $attendancePercentage = null;

        if ($daysSchoolOpened !== null && $daysPresent !== null) {
            $daysAbsent = max(0, (int) $daysSchoolOpened - (int) $daysPresent);
            $attendancePercentage = $daysSchoolOpened > 0
                ? round(($daysPresent / $daysSchoolOpened) * 100, 2)
                : 0;
        }

        DB::transaction(function () use ($developmentalReport, $validated, $daysAbsent, $attendancePercentage, $request) {
            $developmentalReport->fill([
                'days_school_opened' => $validated['days_school_opened'] ?? null,
                'days_present' => $validated['days_present'] ?? null,
                'days_absent' => $daysAbsent,
                'attendance_percentage' => $attendancePercentage,
                'class_teacher_remark' => $validated['class_teacher_remark'] ?? null,
                'authority_remark' => $validated['authority_remark'] ?? $developmentalReport->authority_remark,
            ]);

            if ($request->boolean('submit')) {
                $this->snapshotSigners($developmentalReport);
                $developmentalReport->status = DevelopmentalReport::STATUS_SUBMITTED;
                $developmentalReport->submitted_at = now();
            }

            $developmentalReport->save();

            foreach (($validated['ratings'] ?? []) as $skillId => $rating) {
                DevelopmentalReportRating::updateOrCreate(
                    [
                        'developmental_report_id' => $developmentalReport->id,
                        'developmental_skill_id' => $skillId,
                    ],
                    ['rating' => $rating ?: null]
                );
            }
        });

        return redirect()
            ->route('admin.developmental-reports.edit', [
                'student' => $developmentalReport->student_id,
                'session_id' => $developmentalReport->session_id,
                'term_id' => $developmentalReport->term_id,
            ])
            ->with('success', $request->boolean('submit') ? 'Developmental report submitted.' : 'Developmental report saved.');
    }

    public function show(Request $request, DevelopmentalReport $developmentalReport)
    {
        $this->authorizeDevelopmentalReportAccess($request->user());
        $developmentalReport->load(['student.class', 'class.activeFormTeacher.teacher', 'session', 'term', 'ratings.skill']);
        $this->authorizeReport($request->user(), $developmentalReport);

        $skillsBySection = DevelopmentalSkill::active()->orderBy('sort_order')->get()->groupBy('section');
        $ratings = $developmentalReport->ratings->pluck('rating', 'developmental_skill_id');
        $ratingLabels = DevelopmentalReport::ratingLabels();
        $schoolSettings = SchoolSettings::getSettings();
        $renderMode = 'browser';

        return view('admin.developmental-reports.show', compact('developmentalReport', 'skillsBySection', 'ratings', 'ratingLabels', 'schoolSettings', 'renderMode'));
    }

    public function publish(Request $request, DevelopmentalReport $developmentalReport)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'authority_remark' => ['nullable', 'string', 'max:1200'],
        ]);

        $this->snapshotSigners($developmentalReport);
        $developmentalReport->update([
            'authority_remark' => $validated['authority_remark'] ?? $developmentalReport->authority_remark,
            'status' => DevelopmentalReport::STATUS_PUBLISHED,
            'published_at' => now(),
            'published_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Developmental report published.');
    }

    public function unpublish(Request $request, DevelopmentalReport $developmentalReport)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $developmentalReport->update([
            'status' => DevelopmentalReport::STATUS_SUBMITTED,
            'published_at' => null,
            'published_by' => null,
        ]);

        return back()->with('success', 'Developmental report un-published.');
    }

    public function download(Request $request, DevelopmentalReport $developmentalReport)
    {
        $this->authorizeDevelopmentalReportAccess($request->user());
        $developmentalReport->load(['student.class', 'session', 'term', 'ratings.skill']);
        $this->authorizeReport($request->user(), $developmentalReport);

        $skillsBySection = DevelopmentalSkill::active()->orderBy('sort_order')->get()->groupBy('section');
        $ratings = $developmentalReport->ratings->pluck('rating', 'developmental_skill_id');
        $ratingLabels = DevelopmentalReport::ratingLabels();
        $schoolSettings = SchoolSettings::getSettings();
        $renderMode = 'pdf';

        try {
            Pdf::setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

            $pdf = Pdf::loadView('admin.developmental-reports.show', compact('developmentalReport', 'skillsBySection', 'ratings', 'ratingLabels', 'schoolSettings', 'renderMode'))
                ->setPaper('a4', 'portrait');

            return $pdf->download('Developmental_Report_' . str_replace(' ', '_', $developmentalReport->student->name) . '.pdf');
        } catch (\Exception $e) {
            $msg = '[' . now() . '] Admin developmental PDF generation failed for user ' . $request->user()->id . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n";
            Log::error($msg);
            try {
                file_put_contents(storage_path('app/developmental_pdf_error.txt'), $msg, FILE_APPEND | LOCK_EX);
            } catch (\Exception $_) {
                // ignore write failures
            }

            abort(500, 'Unable to generate developmental report PDF. Please contact the administrator.');
        }
    }

    private function firstOrCreateReport(User $student, Session $session, Term $term): DevelopmentalReport
    {
        $formTeacher = $student->class?->activeFormTeacher?->teacher;
        $authorityRole = $student->class?->reportAuthorityRole() ?? 'head_teacher';
        $authority = $this->authorityFor($authorityRole);

        return DevelopmentalReport::firstOrCreate(
            [
                'student_id' => $student->id,
                'session_id' => $session->id,
                'term_id' => $term->id,
            ],
            [
                'class_id' => $student->class_id,
                'form_teacher_id' => $formTeacher?->id,
                'authority_id' => $authority?->id,
                'authority_role' => $authorityRole,
                'status' => DevelopmentalReport::STATUS_DRAFT,
            ]
        );
    }

    private function snapshotSigners(DevelopmentalReport $report): void
    {
        $report->loadMissing(['class.activeFormTeacher.teacher']);

        $formTeacher = $report->class?->activeFormTeacher?->teacher;
        $authorityRole = $report->class?->reportAuthorityRole() ?? 'head_teacher';
        $authority = $this->authorityFor($authorityRole);

        $report->fill([
            'form_teacher_id' => $formTeacher?->id,
            'form_teacher_name' => $report->form_teacher_name ?: $formTeacher?->name,
            'form_teacher_signature' => $report->form_teacher_signature ?: $formTeacher?->signature,
            'form_teacher_signed_at' => $report->form_teacher_signed_at ?: now()->toDateString(),
            'authority_id' => $authority?->id,
            'authority_role' => $authorityRole,
            'authority_name' => $report->authority_name ?: $authority?->name,
            'authority_signature' => $report->authority_signature ?: $authority?->signature,
            'authority_signed_at' => $report->authority_signed_at ?: now()->toDateString(),
        ]);
    }

    private function authorityFor(string $role): ?User
    {
        return User::where('report_authority_role', $role)
            ->whereIn('role', ['admin', 'teacher'])
            ->orderBy('name')
            ->first();
    }

    private function manageableClassIds(User $user): array
    {
        $query = SchoolClass::query();

        if ($user->isTeacher() && ! $user->isAdmin()) {
            $classIds = [];

            $assigned = FormTeacher::where('teacher_id', $user->id)
                ->where('is_active', true)
                ->with('schoolClass')
                ->get()
                ->filter(fn ($assignment) => $assignment->schoolClass
                    && in_array($assignment->schoolClass->section_key, ['creche', 'other'], true)
                )
                ->pluck('class_id')
                ->all();

            $classIds = array_merge($classIds, $assigned);

            if ($user->canReviewReportCards()) {
                $classIds = array_merge($classIds, $user->reportReviewClasses()->pluck('school_classes.id')->toArray());
            }

            $query->whereIn('id', array_unique($classIds));
        }

        return $query->get()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function authorizeStudent(User $user, User $student): void
    {
        abort_unless(in_array((int) $student->class_id, $this->manageableClassIds($user), true), 403);
    }

    private function authorizeReport(User $user, DevelopmentalReport $report): void
    {
        abort_unless(in_array((int) $report->class_id, $this->manageableClassIds($user), true), 403);
    }

    private function authorizeDevelopmentalReportAccess(User $user): void
    {
        if ($user->isAdmin() || $user->canReviewReportCards() || $user->canFillDevelopmentalReports()) {
            return;
        }

        abort_unless(false, 403, 'You do not have permission to access developmental reports.');
    }
}
