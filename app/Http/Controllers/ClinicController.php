<?php

namespace App\Http\Controllers;

use App\Models\ClinicVisit;
use App\Models\ClinicIncident;
use App\Models\ClinicInventoryItem;
use App\Models\ClinicInventoryTransaction;
use App\Models\ClinicSupplyRequest;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClinicController extends Controller
{
    public function dashboard()
    {
        $this->authorizeClinic();
        $today = today();
        $weekStart = now()->startOfWeek();

        $todayVisits = ClinicVisit::with(['person.class', 'student.class', 'recorder'])
            ->whereDate('visited_at', $today)
            ->latest('visited_at')
            ->get();

        return view('clinic.dashboard', [
            'todayVisits' => $todayVisits,
            'todayCount' => $todayVisits->count(),
            'weekCount' => ClinicVisit::where('visited_at', '>=', $weekStart)->count(),
            'inClinicCount' => ClinicVisit::whereDate('visited_at', $today)->where('outcome', 'remained_in_clinic')->count(),
            'sentHomeCount' => ClinicVisit::whereDate('visited_at', $today)->where('outcome', 'sent_home')->count(),
            'lowStockCount' => ClinicInventoryItem::where('is_active', true)->whereColumn('quantity', '<=', 'minimum_quantity')->count(),
        ]);
    }

    public function students(Request $request)
    {
        $this->authorizeClinic();
        $search = trim((string) $request->input('search', ''));

        $students = User::where('role', 'student')
            ->with('class')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($studentQuery) use ($search) {
                    $studentQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%")
                        ->orWhereHas('class', fn ($classQuery) => $classQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('clinic.students.index', compact('students', 'search'));
    }

    public function student(User $student)
    {
        $this->authorizeClinic();
        abort_unless($student->role === 'student', 404);

        $student->load('class');
        $visits = ClinicVisit::with('recorder')
            ->where('student_id', $student->id)
            ->latest('visited_at')
            ->paginate(20);

        return view('clinic.students.show', compact('student', 'visits'));
    }

    public function createVisit(Request $request)
    {
        $this->authorizeClinic();
        $student = $request->filled('student_id')
            ? User::with('class')->where('role', 'student')->findOrFail($request->student_id)
            : null;
        $classes = SchoolClass::with(['students' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();
        $staff = User::whereIn('role', ['teacher', 'non_teaching_staff', 'nurse', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('clinic.visits.create', compact('student', 'classes', 'staff'));
    }

    public function storeVisit(Request $request)
    {
        $this->authorizeClinic();

        $validated = $request->validate([
            'patient_type' => ['required', 'in:student,staff,not_listed'],
            'class_id' => ['nullable', 'exists:school_classes,id'],
            'person_id' => ['nullable', 'exists:users,id'],
            'patient_name' => ['nullable', 'string', 'max:255'],
            'patient_identifier' => ['nullable', 'string', 'max:100'],
            'visited_at' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'symptoms' => ['nullable', 'string', 'max:5000'],
            'temperature' => ['nullable', 'numeric', 'between:30,45'],
            'vital_notes' => ['nullable', 'string', 'max:1000'],
            'observation' => ['nullable', 'string', 'max:5000'],
            'action_taken' => ['nullable', 'string', 'max:5000'],
            'medication_administered' => ['nullable', 'string', 'max:1000'],
            'parent_contacted' => ['nullable', 'boolean'],
            'parent_contacted_at' => ['nullable', 'date'],
            'outcome' => ['required', 'in:returned_to_class,remained_in_clinic,sent_home,referred_to_hospital,other'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $person = null;
        if ($validated['patient_type'] === 'student') {
            $person = User::whereKey($validated['person_id'] ?? null)->where('role', 'student')->first();
            abort_unless($person && (int) $person->class_id === (int) ($validated['class_id'] ?? 0), 422, 'Select a student from the selected class.');
        } elseif ($validated['patient_type'] === 'staff') {
            $person = User::whereKey($validated['person_id'] ?? null)->whereIn('role', ['teacher', 'non_teaching_staff', 'nurse', 'admin'])->first();
            abort_unless($person, 422, 'Select a valid staff member.');
        } else {
            abort_unless(filled($validated['patient_name'] ?? null), 422, 'Enter the name of the person not listed.');
        }

        $visit = ClinicVisit::create([
            ...$validated,
            'student_id' => $validated['patient_type'] === 'student' ? $person->id : null,
            'person_id' => $person?->id,
            'recorded_by' => Auth::id(),
            'parent_contacted' => $request->boolean('parent_contacted'),
        ]);

        return redirect()->route('clinic.visits.show', $visit)
            ->with('success', 'Clinic visit recorded successfully.');
    }

    public function showVisit(ClinicVisit $visit)
    {
        $this->authorizeClinic();
        $visit->load(['student.class', 'recorder']);

        return view('clinic.visits.show', compact('visit'));
    }

    public function incidents()
    {
        $this->authorizeClinic();
        $incidents = ClinicIncident::with(['student.class', 'reporter'])->latest('incident_at')->paginate(25);
        return view('clinic.incidents.index', compact('incidents'));
    }

    public function createIncident(Request $request)
    {
        $this->authorizeClinic();
        $student = $request->filled('student_id') ? User::with('class')->where('role', 'student')->findOrFail($request->student_id) : null;
        return view('clinic.incidents.create', compact('student'));
    }

    public function storeIncident(Request $request)
    {
        $this->authorizeClinic();
        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'], 'incident_at' => ['required', 'date'],
            'incident_type' => ['required', 'string', 'max:100'], 'location' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'], 'injury_details' => ['nullable', 'string', 'max:5000'],
            'action_taken' => ['nullable', 'string', 'max:5000'], 'referred_to' => ['nullable', 'string', 'max:255'],
            'parent_contacted' => ['nullable', 'boolean'], 'parent_contacted_at' => ['nullable', 'date'],
        ]);
        abort_unless(User::whereKey($validated['student_id'])->where('role', 'student')->exists(), 422);
        $incident = ClinicIncident::create([...$validated, 'reported_by' => Auth::id(), 'parent_contacted' => $request->boolean('parent_contacted')]);
        return redirect()->route('clinic.incidents.index')->with('success', 'Incident report saved.');
    }

    public function inventory()
    {
        $this->authorizeClinic();
        $items = ClinicInventoryItem::where('is_active', true)->orderBy('name')->get();
        $requests = ClinicSupplyRequest::with(['requester', 'reviewer'])->latest()->take(30)->get();
        return view('clinic.inventory.index', compact('items', 'requests'));
    }

    public function storeInventoryItem(Request $request)
    {
        $this->authorizeClinic();
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'category' => ['nullable', 'string', 'max:100'], 'quantity' => ['required', 'numeric', 'min:0'], 'minimum_quantity' => ['required', 'numeric', 'min:0'], 'unit' => ['required', 'string', 'max:50']]);
        ClinicInventoryItem::create($data);
        return back()->with('success', 'Inventory item added.');
    }

    public function storeInventoryTransaction(Request $request, ClinicInventoryItem $item)
    {
        $this->authorizeClinic();
        $data = $request->validate([
            'type' => ['required', 'in:received,used,adjusted'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $change = $data['type'] === 'used' ? -abs((float) $data['quantity']) : abs((float) $data['quantity']);
        abort_if($change < 0 && (float) $item->quantity + $change < 0, 422, 'Quantity used cannot exceed stock remaining.');

        ClinicInventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'recorded_by' => Auth::id(),
            ...$data,
        ]);
        $item->increment('quantity', $change);

        return back()->with('success', 'Inventory quantity updated.');
    }

    public function storeSupplyRequest(Request $request)
    {
        $this->authorizeClinic();
        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'quantity_requested' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:50'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
        ClinicSupplyRequest::create([...$data, 'requested_by' => Auth::id()]);
        return back()->with('success', 'Supply request submitted to administration.');
    }

    public function reviewSupplyRequest(Request $request, ClinicSupplyRequest $supplyRequest)
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected,fulfilled'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $supplyRequest->update([...$data, 'reviewed_by' => Auth::id(), 'reviewed_at' => now()]);
        return back()->with('success', 'Supply request updated.');
    }

    private function authorizeClinic(): void
    {
        abort_unless(Auth::user()?->isAdmin() || Auth::user()?->isNurse(), 403);
    }
}
