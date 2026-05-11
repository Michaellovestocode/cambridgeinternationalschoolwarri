<?php

namespace App\Http\Controllers;

use App\Models\FeeClearance;
use App\Models\SchoolClass;
use App\Models\Session;
use App\Models\Term;
use App\Models\User;
use Illuminate\Http\Request;

class AdminFeeClearanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $activeSession = Session::getActive();
        $activeTerm = Term::getActive();
        $selectedSessionId = (int) $request->input('session_id', $activeSession?->id);
        $selectedTermId = (int) $request->input('term_id', $activeTerm?->id);

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

        $clearances = FeeClearance::with('approver')
            ->where('session_id', $selectedSessionId)
            ->where('term_id', $selectedTermId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $classes = SchoolClass::orderBy('name')->get();
        $sessions = Session::orderByDesc('start_date')->get();
        $terms = Term::with('session')->orderByDesc('start_date')->get();

        return view('admin.fee-clearances.index', compact(
            'students',
            'clearances',
            'classes',
            'sessions',
            'terms',
            'selectedSessionId',
            'selectedTermId'
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
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_reference' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ]);

        $isApproved = (bool) $validated['is_approved'];

        FeeClearance::updateOrCreate(
            [
                'student_id' => $student->id,
                'session_id' => $validated['session_id'],
                'term_id' => $validated['term_id'],
            ],
            [
                'is_approved' => $isApproved,
                'amount_paid' => $validated['amount_paid'] ?? null,
                'payment_reference' => $validated['payment_reference'] ?? null,
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
}
