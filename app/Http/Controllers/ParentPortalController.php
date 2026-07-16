<?php

namespace App\Http\Controllers;

use App\Models\AdmissionEnquiry;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Message;
use App\Models\ReportCard;
use App\Services\ReportCardRenderService;
use Illuminate\Support\Facades\Auth;
use App\Models\DevelopmentalReport;
use Barryvdh\DomPDF\Facade\Pdf;

class ParentPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:parent');
    }

    public function dashboard()
    {
        $parent = Auth::user();
        $children = $parent->children()->with('class')->get();
        $childIds = $children->pluck('id');
        $classIds = $children->pluck('class_id')->filter()->unique()->values();

        $attempts = ExamAttempt::with(['exam', 'user'])
            ->whereIn('user_id', $childIds)
            ->latest()
            ->get();

        $reportCards = ReportCard::with(['class', 'student', 'session', 'term'])
            ->whereIn('student_id', $childIds)
            ->published()
            ->feeCleared()
            ->latest()
            ->take(6)
            ->get();

        $classExams = Exam::query()
            ->with([
                'classes:id,name',
                'attempts' => fn ($query) => $query
                    ->whereIn('user_id', $childIds)
                    ->latest('started_at'),
            ])
            ->when($classIds->isNotEmpty(), function ($query) use ($classIds) {
                $query->whereHas('classes', function ($classQuery) use ($classIds) {
                    $classQuery->whereIn('school_classes.id', $classIds);
                });
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->where('is_active', true)
            ->where('grading_mode', 'auto')
            ->orderBy('start_date')
            ->get();

        $enquiries = AdmissionEnquiry::where('email', $parent->email)
            ->orWhere('parent_name', $parent->name)
            ->latest()
            ->take(5)
            ->get();

        $recentMessages = Message::with('sender')
            ->where('recipient_id', $parent->id)
            ->latest()
            ->take(5)
            ->get();

        $childExamOverview = $children->mapWithKeys(function ($child) use ($classExams) {
            $examItems = $classExams
                ->filter(fn ($exam) => $exam->classes->contains('id', $child->class_id))
                ->map(function ($exam) use ($child) {
                    $attempt = $exam->attempts->firstWhere('user_id', $child->id);
                    $status = $this->resolveExamStatus($exam, $attempt);

                    return (object) [
                        'id' => $exam->id,
                        'title' => $exam->title,
                        'subject' => $exam->subject,
                        'total_marks' => $exam->total_marks,
                        'start_date' => $exam->start_date,
                        'end_date' => $exam->end_date,
                        'duration_minutes' => $exam->duration_minutes,
                        'show_results_to_students' => $exam->show_results_to_students,
                        'attempt' => $attempt,
                        'status' => $status,
                        'status_label' => $status === 'graded' && !$exam->show_results_to_students
                            ? 'Taken'
                            : $this->statusLabel($status),
                    ];
                })
                ->values();

            return [
                $child->id => [
                    'next_exam' => $examItems->first(fn ($item) => in_array($item->status, ['upcoming', 'open', 'in_progress'], true)),
                    'recent_activity' => $examItems
                        ->filter(fn ($item) => in_array($item->status, ['graded', 'submitted', 'missed'], true))
                        ->sortByDesc('start_date')
                        ->take(3)
                        ->values(),
                    'schedule' => $examItems->take(5)->values(),
                    'upcoming_count' => $examItems->whereIn('status', ['upcoming', 'open', 'in_progress'])->count(),
                    'completed_count' => $examItems->whereIn('status', ['graded', 'submitted'])->count(),
                ],
            ];
        });

        $notifications = [
            'children' => $children->count(),
            'gradedAttempts' => $attempts->where('status', 'graded')->count(),
            'reportCards' => $reportCards->count(),
            'upcomingExams' => $childExamOverview->sum('upcoming_count'),
            'pendingEnquiries' => $enquiries->where('status', AdmissionEnquiry::STATUS_NEW)->count(),
        ];

        return view('parent.dashboard', compact(
            'children',
            'attempts',
            'reportCards',
            'enquiries',
            'recentMessages',
            'notifications',
            'childExamOverview'
        ));
    }

    public function previewReportCard(ReportCard $reportCard)
    {
        $parent = Auth::user();
        if (!$parent->children->contains('id', $reportCard->student_id)) {
            abort(403);
        }

        abort_unless($reportCard->isPublished(), 404);
        abort_unless($reportCard->hasFeeClearance(), 403, 'This report card is locked until school fee clearance is approved.');

        $reportCard->loadMissing('student');

        return app(ReportCardRenderService::class)->view($reportCard, [
            'title' => $reportCard->student->name . "'s Report Card",
            'back_url' => route('parent.dashboard'),
            'download_url' => route('parent.report-cards.download', $reportCard),
        ]);
    }

    public function previewDevelopmentalReport(DevelopmentalReport $developmentalReport)
    {
        $parent = Auth::user();
        if (! $parent->children->contains('id', $developmentalReport->student_id)) {
            abort(403);
        }

        abort_unless($developmentalReport->isPublished(), 404);

        $developmentalReport->loadMissing(['student', 'session', 'term', 'ratings.skill', 'class']);

        $skillsBySection = \App\Models\DevelopmentalSkill::active()->orderBy('sort_order')->get()->groupBy('section');
        $ratings = $developmentalReport->ratings->pluck('rating', 'developmental_skill_id');
        $ratingLabels = DevelopmentalReport::ratingLabels();
        $schoolSettings = \App\Models\SchoolSettings::getSettings();

        return view('admin.developmental-reports.show', compact('developmentalReport', 'skillsBySection', 'ratings', 'ratingLabels', 'schoolSettings'));
    }

    public function downloadDevelopmentalReport(DevelopmentalReport $developmentalReport)
    {
        $parent = Auth::user();
        if (! $parent->children->contains('id', $developmentalReport->student_id)) {
            abort(403);
        }

        abort_unless($developmentalReport->isPublished(), 404);

        $developmentalReport->load(['student.class', 'session', 'term', 'ratings.skill']);
        $skillsBySection = \App\Models\DevelopmentalSkill::active()->orderBy('sort_order')->get()->groupBy('section');
        $ratings = $developmentalReport->ratings->pluck('rating', 'developmental_skill_id');
        $ratingLabels = DevelopmentalReport::ratingLabels();
        $schoolSettings = \App\Models\SchoolSettings::getSettings();

        $pdf = Pdf::loadView('admin.developmental-reports.show', compact('developmentalReport', 'skillsBySection', 'ratings', 'ratingLabels', 'schoolSettings'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Developmental_Report_' . str_replace(' ', '_', $developmentalReport->student->name) . '.pdf');
    }

    public function downloadReportCard(ReportCard $reportCard)
    {
        $parent = Auth::user();
        if (!$parent->children->contains('id', $reportCard->student_id)) {
            abort(403);
        }

        abort_unless($reportCard->isPublished(), 404);
        abort_unless($reportCard->hasFeeClearance(), 403, 'This report card is locked until school fee clearance is approved.');

        return app(ReportCardRenderService::class)->download($reportCard);
    }

    private function resolveExamStatus(Exam $exam, ?ExamAttempt $attempt): string
    {
        if ($attempt?->isGraded()) {
            return 'graded';
        }

        if ($attempt?->isSubmitted()) {
            return 'submitted';
        }

        if ($attempt?->isInProgress()) {
            return 'in_progress';
        }

        if (now()->lt($exam->start_date)) {
            return 'upcoming';
        }

        if (now()->between($exam->start_date, $exam->end_date)) {
            return 'open';
        }

        return 'missed';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'graded' => 'Result Ready',
            'submitted' => 'Taken',
            'in_progress' => 'In Progress',
            'upcoming' => 'Upcoming',
            'open' => 'Open Now',
            'missed' => 'No Attempt Recorded',
            default => 'Unknown',
        };
    }
}
