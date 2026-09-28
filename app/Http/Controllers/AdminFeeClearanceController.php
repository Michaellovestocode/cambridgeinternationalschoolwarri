<?php

namespace App\Http\Controllers;

use App\Models\FeeClearance;
use App\Models\ClassFeeSchedule;
use App\Models\SchoolClass;
use App\Models\Session;
use App\Models\Term;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFeeClearanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $selectedSessionId = (int) $request->input('session_id', $activeSession?->id);
        $selectedTermId = (int) $request->input('term_id', $activeTerm?->id);
        $selectedClassId = $request->filled('class_id') ? (int) $request->input('class_id') : null;

        $students = User::where('role', 'student')
            ->with('class')
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->class_id))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('registration_number', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(25);

        $students->appends($request->query());

        $clearances = FeeClearance::with(['approver', 'payments.recorder'])
            ->where('session_id', $selectedSessionId)
            ->where('term_id', $selectedTermId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $classes = SchoolClass::orderBy('name')->get();
        $sessions = Session::orderByDesc('start_date')->get();
        $terms = Term::with('session')->orderByDesc('start_date')->get();
        $feeSchedule = $selectedClassId
            ? ClassFeeSchedule::where('class_id', $selectedClassId)->where('session_id', $selectedSessionId)->where('term_id', $selectedTermId)->first()
            : null;

        return view('admin.fee-clearances.index', compact(
            'students',
            'clearances',
            'classes',
            'sessions',
            'terms',
            'selectedSessionId',
            'selectedTermId',
            'selectedClassId',
            'feeSchedule'
        ));
    }

    public function update(Request $request, User $student)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        abort_unless($student->isStudent(), 404);

        $validated = $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'is_approved' => 'required|boolean',
            'uniform_due' => 'nullable|numeric|min:0',
            'books_due' => 'nullable|numeric|min:0',
            'hostel_due' => 'nullable|numeric|min:0',
            'lunch_due' => 'nullable|numeric|min:0',
            'enrolment_due' => 'nullable|numeric|min:0',
            'uniform_discount' => 'nullable|numeric|min:0',
            'books_discount' => 'nullable|numeric|min:0',
            'hostel_discount' => 'nullable|numeric|min:0',
            'lunch_discount' => 'nullable|numeric|min:0',
            'enrolment_discount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
        ]);

        foreach (['uniform', 'books', 'hostel', 'lunch', 'enrolment'] as $category) {
            abort_if(
                (float) ($validated[$category . '_discount'] ?? 0) > (float) ($validated[$category . '_due'] ?? 0),
                422,
                ucfirst($category) . ' discount cannot exceed the fee amount.'
            );
        }

        abort_if(
            (float) ($validated['enrolment_due'] ?? 0) > 0 && ! in_array($student->class?->level_number, [6, 9, 12], true),
            422,
            'Enrolment fees apply only to Years 6, 9, and 12.'
        );

        $isApproved = (bool) $validated['is_approved'];

        FeeClearance::updateOrCreate(
            [
                'student_id' => $student->id,
                'session_id' => $validated['session_id'],
                'term_id' => $validated['term_id'],
            ],
            [
                'is_approved' => $isApproved,
                'uniform_due' => $validated['uniform_due'] ?? 0,
                'books_due' => $validated['books_due'] ?? 0,
                'hostel_due' => $validated['hostel_due'] ?? 0,
                'lunch_due' => $validated['lunch_due'] ?? 0,
                'enrolment_due' => $validated['enrolment_due'] ?? 0,
                'uniform_discount' => $validated['uniform_discount'] ?? 0,
                'books_discount' => $validated['books_discount'] ?? 0,
                'hostel_discount' => $validated['hostel_discount'] ?? 0,
                'lunch_discount' => $validated['lunch_discount'] ?? 0,
                'enrolment_discount' => $validated['enrolment_discount'] ?? 0,
                'note' => $validated['note'] ?? null,
                'approved_by' => $isApproved ? auth()->id() : null,
                'approved_at' => $isApproved ? now() : null,
            ]
        );

        $message = $isApproved
            ? "Fee clearance approved for {$student->name}."
            : "Fee clearance revoked for {$student->name}.";

        return back()->with('success', $message);
    }

    public function storePayment(Request $request, User $student)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        abort_unless($student->isStudent(), 404);

        $data = $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'category' => 'required|in:uniform,books,hostel,lunch,enrolment',
            'amount' => 'required|numeric|gt:0',
            'paid_at' => 'required|date',
            'payment_reference' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ]);

        abort_if($data['category'] === 'enrolment' && ! in_array($student->class?->level_number, [6, 9, 12], true), 422, 'Enrolment fees apply only to Years 6, 9, and 12.');

        $clearance = FeeClearance::firstOrCreate(
            ['student_id' => $student->id, 'session_id' => $data['session_id'], 'term_id' => $data['term_id']],
            ['is_approved' => false]
        );

        $clearance->payments()->create([
            'category' => $data['category'],
            'amount' => $data['amount'],
            'paid_at' => $data['paid_at'],
            'payment_reference' => $data['payment_reference'] ?? null,
            'note' => $data['note'] ?? null,
            'recorded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Installment recorded.');
    }

    public function saveSchedule(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'uniform_amount' => 'nullable|numeric|min:0',
            'books_amount' => 'nullable|numeric|min:0',
            'hostel_amount' => 'nullable|numeric|min:0',
            'lunch_amount' => 'nullable|numeric|min:0',
            'enrolment_amount' => 'nullable|numeric|min:0',
        ]);

        $schoolClass = SchoolClass::findOrFail($data['class_id']);
        abort_if(
            (float) ($data['enrolment_amount'] ?? 0) > 0 && ! in_array($schoolClass->level_number, [6, 9, 12], true),
            422,
            'Enrolment fees apply only to Years 6, 9, and 12.'
        );

        $keys = ['uniform', 'books', 'hostel', 'lunch', 'enrolment'];
        $scheduleQuery = ClassFeeSchedule::where('class_id', $schoolClass->id)
            ->where('session_id', $data['session_id'])
            ->where('term_id', $data['term_id']);
        $previousSchedule = (clone $scheduleQuery)->first();
        $scheduleValues = ['updated_by' => auth()->id()];
        foreach ($keys as $key) {
            $scheduleValues[$key . '_amount'] = $data[$key . '_amount'] ?? 0;
        }

        DB::transaction(function () use ($scheduleQuery, $scheduleValues, $schoolClass, $data, $keys, $previousSchedule) {
            $schedule = $scheduleQuery->firstOrNew();
            $schedule->fill($scheduleValues);
            $schedule->class_id = $schoolClass->id;
            $schedule->session_id = $data['session_id'];
            $schedule->term_id = $data['term_id'];
            $schedule->save();

            User::where('role', 'student')->where('class_id', $schoolClass->id)->orderBy('id')->chunkById(100, function ($students) use ($keys, $schedule, $previousSchedule) {
                foreach ($students as $student) {
                    $clearance = FeeClearance::firstOrNew([
                        'student_id' => $student->id,
                        'session_id' => $schedule->session_id,
                        'term_id' => $schedule->term_id,
                    ]);
                    $wasUsingSchedule = (int) $clearance->fee_schedule_id === (int) $schedule->id;

                    foreach ($keys as $key) {
                        $dueField = $key . '_due';
                        $newAmount = (float) $schedule->{$key . '_amount'};
                        $currentAmount = (float) ($clearance->{$dueField} ?? 0);
                        $matchesPrevious = $previousSchedule
                            && round($currentAmount * 100) === round((float) $previousSchedule->{$key . '_amount'} * 100);

                        if (! $clearance->exists || (! $wasUsingSchedule && $currentAmount === 0.0) || ($wasUsingSchedule && $matchesPrevious)) {
                            $clearance->{$dueField} = $newAmount;
                        }
                        $discountField = $key . '_discount';
                        $clearance->{$discountField} = min((float) ($clearance->{$discountField} ?? 0), (float) $clearance->{$dueField});
                    }

                    $clearance->fee_schedule_id = $schedule->id;
                    if (! $clearance->exists) {
                        $clearance->is_approved = false;
                    }
                    $clearance->save();
                }
            });
        });

        return back()->with('success', 'Class fee schedule saved and applied. Zero fee amounts were filled; non-zero student-specific amounts were preserved.');
    }
}
