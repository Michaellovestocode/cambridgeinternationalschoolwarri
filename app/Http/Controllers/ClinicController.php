<?php

namespace App\Http\Controllers;

use App\Models\ClinicVisit;
use App\Models\ClinicIncident;
use App\Models\ClinicInventoryItem;
use App\Models\ClinicInventoryTransaction;
use App\Models\ClinicSupplyRequest;
use App\Models\ClinicTermHealthCheck;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ClinicController extends Controller
{
    public function dashboard()
    {
        $this->authorizeClinic();
        $today = today();
        $weekStart = now()->startOfWeek();

        $todayVisitsQuery = ClinicVisit::with(['person.class', 'student.class', 'recorder']);
        $this->scopeToCurrentClinicUser($todayVisitsQuery, 'recorded_by');
        $todayVisits = $todayVisitsQuery
            ->whereDate('visited_at', $today)
            ->latest('visited_at')
            ->get();

        $weekVisitsQuery = ClinicVisit::query();
        $this->scopeToCurrentClinicUser($weekVisitsQuery, 'recorded_by');

        $inClinicVisitsQuery = ClinicVisit::query();
        $this->scopeToCurrentClinicUser($inClinicVisitsQuery, 'recorded_by');

        $sentHomeVisitsQuery = ClinicVisit::query();
        $this->scopeToCurrentClinicUser($sentHomeVisitsQuery, 'recorded_by');

        return view('clinic.dashboard', [
            'todayVisits' => $todayVisits,
            'todayCount' => $todayVisits->count(),
            'weekCount' => $weekVisitsQuery->where('visited_at', '>=', $weekStart)->count(),
            'inClinicCount' => $inClinicVisitsQuery->whereDate('visited_at', $today)->where('outcome', 'remained_in_clinic')->count(),
            'sentHomeCount' => $sentHomeVisitsQuery->whereDate('visited_at', $today)->where('outcome', 'sent_home')->count(),
            'lowStockCount' => ClinicInventoryItem::where('is_active', true)->whereColumn('quantity', '<=', 'minimum_quantity')->count(),
        ]);
    }

    public function records(Request $request)
    {
        $this->authorizeClinic();

        $type = $request->string('type', 'visits')->value();
        abort_unless(in_array($type, ['visits', 'health-checks', 'incidents'], true), 404);

        $period = $request->string('period')->value();
        abort_unless($period === '' || in_array($period, ['today', 'week', 'current', 'sent-home'], true), 404);
        $search = trim((string) $request->input('search', ''));

        if ($type === 'health-checks') {
            $records = ClinicTermHealthCheck::with(['student.class', 'recorder']);
            $this->scopeToCurrentClinicUser($records, 'recorded_by');
            $this->applyPeriodFilter($records, $period, 'checked_at');
            $records->when($search !== '', fn ($query) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', "%{$search}%")));
            $records->latest('checked_at');
        } elseif ($type === 'incidents') {
            $records = ClinicIncident::with(['student.class', 'reporter']);
            $this->scopeToCurrentClinicUser($records, 'reported_by');
            $this->applyPeriodFilter($records, $period, 'incident_at');
            $records->when($search !== '', fn ($query) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', "%{$search}%")));
            $records->latest('incident_at');
        } else {
            $records = ClinicVisit::with(['person.class', 'student.class', 'recorder']);
            $this->scopeToCurrentClinicUser($records, 'recorded_by');
            $this->applyPeriodFilter($records, $period, 'visited_at');
            if ($period === 'current') {
                $records->where('outcome', 'remained_in_clinic');
            }
            if ($period === 'sent-home') {
                $records->where('outcome', 'sent_home');
            }
            $records->when($search !== '', function ($query) use ($search) {
                $query->where(function ($visitQuery) use ($search) {
                    $visitQuery->where('patient_name', 'like', "%{$search}%")
                        ->orWhereHas('person', fn ($personQuery) => $personQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', "%{$search}%"));
                });
            });
            $records->latest('visited_at');
        }

        return view('clinic.records.index', [
            'records' => $records->paginate(25)->withQueryString(),
            'type' => $type,
            'period' => $period,
            'search' => $search,
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

    public function healthChecks()
    {
        $this->authorizeClinic();

        $students = User::where('role', 'student')
            ->with('class')
            ->orderBy('name')
            ->get();

        $recentChecks = ClinicTermHealthCheck::with(['student.class', 'recorder'])
            ->latest('checked_at')
            ->take(15)
            ->get();

        return view('clinic.health-checks.index', compact('students', 'recentChecks'));
    }

    public function storeHealthCheck(Request $request)
    {
        $this->authorizeClinic();

        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'term_label' => ['required', 'string', 'max:100'],
            'check_type' => ['required', 'string', 'max:100'],
            'hostel_name' => ['nullable', 'string', 'max:255'],
            'temperature' => ['nullable', 'numeric', 'between:30,45'],
            'pulse' => ['nullable', 'integer', 'between:20,250'],
            'weight_kg' => ['nullable', 'numeric', 'between:1,300'],
            'respiration' => ['nullable', 'integer', 'between:1,100'],
            'blood_pressure' => ['nullable', 'string', 'max:30'],
            'health_notes' => ['nullable', 'string', 'max:2000'],
            'remark' => ['nullable', 'string', 'max:255'],
            'clearance_status' => ['required', 'in:normal,needs_observation,referred'],
            'checked_at' => ['required', 'date'],
        ]);

        abort_unless(User::whereKey($validated['student_id'])->where('role', 'student')->exists(), 422, 'Select a valid student.');

        ClinicTermHealthCheck::create([...$validated, 'recorded_by' => Auth::id()]);

        return redirect()->route('clinic.health-checks.index')->with('success', 'Health check saved successfully.');
    }

    public function storeHealthChecks(Request $request)
    {
        $this->authorizeClinic();

        $validator = Validator::make($request->all(), [
            'term_label' => ['required', 'string', 'max:100'],
            'check_type' => ['required', 'string', 'max:100'],
            'hostel_name' => ['nullable', 'string', 'max:255'],
            'checked_at' => ['required', 'date'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.student_id' => ['nullable', 'integer', 'exists:users,id'],
            'rows.*.temperature' => ['nullable', 'numeric', 'between:30,45'],
            'rows.*.pulse' => ['nullable', 'integer', 'between:20,250'],
            'rows.*.weight_kg' => ['nullable', 'numeric', 'between:1,300'],
            'rows.*.respiration' => ['nullable', 'integer', 'between:1,100'],
            'rows.*.blood_pressure' => ['nullable', 'string', 'max:30'],
            'rows.*.remark' => ['nullable', 'string', 'max:255'],
            'rows.*.clearance_status' => ['nullable', 'in:normal,needs_observation,referred'],
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach ($request->input('rows', []) as $index => $row) {
                $hasMeasurements = collect($row)->except(['student_id', 'clearance_status'])
                    ->contains(fn ($value) => filled($value));

                if ($hasMeasurements && ! filled($row['student_id'] ?? null)) {
                    $validator->errors()->add("rows.{$index}.student_id", 'Select the student for every completed row.');
                }
            }
        });

        $data = $validator->validate();
        $rows = collect($data['rows'])->filter(fn ($row) => filled($row['student_id'] ?? null))->values();

        abort_if($rows->isEmpty(), 422, 'Enter at least one student in the vital-sign register.');
        abort_unless(User::whereIn('id', $rows->pluck('student_id'))->where('role', 'student')->count() === $rows->pluck('student_id')->unique()->count(), 422, 'Every row must contain a valid student.');
        abort_if($rows->pluck('student_id')->duplicates()->isNotEmpty(), 422, 'A student can only appear once in the same register.');

        DB::transaction(function () use ($data, $rows) {
            $rows->each(function ($row) use ($data) {
                ClinicTermHealthCheck::create([
                    'student_id' => $row['student_id'],
                    'recorded_by' => Auth::id(),
                    'term_label' => $data['term_label'],
                    'check_type' => $data['check_type'],
                    'hostel_name' => $data['hostel_name'] ?? null,
                    'temperature' => $row['temperature'] ?? null,
                    'pulse' => $row['pulse'] ?? null,
                    'weight_kg' => $row['weight_kg'] ?? null,
                    'respiration' => $row['respiration'] ?? null,
                    'blood_pressure' => $row['blood_pressure'] ?? null,
                    'remark' => filled($row['remark'] ?? null) ? $row['remark'] : 'Normal',
                    'clearance_status' => $row['clearance_status'] ?? 'normal',
                    'checked_at' => $data['checked_at'],
                ]);
            });
        });

        return redirect()->route('clinic.health-checks.index')->with('success', "{$rows->count()} vital-sign record(s) saved successfully.");
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
        $classes = collect();
        $classData = [];
        $staffData = [];

        try {
            $classes = SchoolClass::with(['students' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get();
            $classData = $classes->mapWithKeys(function ($class) {
                return [$class->id => $class->students->map(function ($person) {
                    return [
                        'id' => $person->id,
                        'name' => $person->name,
                        'identifier' => $person->registration_number,
                    ];
                })->values()->all()];
            })->all();
            $staffData = User::whereIn('role', ['teacher', 'non_teaching_staff', 'nurse', 'admin'])
                ->orderBy('name')
                ->get(['id', 'name', 'role'])
                ->map(function ($person) {
                    return [
                        'id' => $person->id,
                        'name' => $person->name,
                        'role' => ucwords(str_replace('_', ' ', $person->role)),
                    ];
                })->values()->all();
        } catch (Throwable $exception) {
            report($exception);
        }

        return view('clinic.visits.create', compact('student', 'classes', 'classData', 'staffData'));
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

    private function scopeToCurrentClinicUser($query, string $column): void
    {
        if (Auth::user()?->isNurse()) {
            $query->where($column, Auth::id());
        }
    }

    private function applyPeriodFilter($query, string $period, string $dateColumn): void
    {
        if ($period === 'today' || $period === 'current' || $period === 'sent-home') {
            $query->whereDate($dateColumn, today());
        }

        if ($period === 'week') {
            $query->where($dateColumn, '>=', now()->startOfWeek());
        }
    }
}
