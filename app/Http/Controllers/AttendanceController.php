<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\StaffAttendanceEvent;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendParentAttendanceSms;

class AttendanceController extends Controller
{
    private const RESUMPTION_TIME = '07:15:00';
    private const CLOSING_TIME = '16:00:00';

    public function scanner()
    {
        $this->authorizeAttendanceManager();

        $today = today();
        $records = AttendanceRecord::with(['user.class'])
            ->whereDate('attendance_date', $today)
            ->latest('updated_at')
            ->take(12)
            ->get();

        return view('admin.attendance.scanner', [
            'records' => $records,
            'stats' => $this->dailyStats($today),
            'resumptionTime' => self::RESUMPTION_TIME,
            'closingTime' => self::CLOSING_TIME,
        ]);
    }

    public function staffPush(Request $request): JsonResponse
    {
        $configuredKey = (string) config('services.staff_attendance.key');
        $providedKey = (string) $request->header('X-Staff-Attendance-Key');
        abort_unless($configuredKey !== '' && $providedKey !== '' && hash_equals($configuredKey, $providedKey), 401);

        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:100'],
            'user_id' => ['required_without:enroll_id', 'nullable', 'string', 'max:100'],
            'enroll_id' => ['required_without:user_id', 'nullable', 'string', 'max:100'],
            'timestamp' => ['required', 'date'],
            'direction' => ['nullable', 'in:in,out,IN,OUT'],
            'event_id' => ['nullable', 'string', 'max:255'],
        ]);

        $machineUserId = (string) ($validated['enroll_id'] ?? $validated['user_id']);
        $eventId = $validated['event_id'] ?? hash('sha256', implode('|', [
            $validated['device_id'], $machineUserId, $validated['timestamp'], $validated['direction'] ?? '',
        ]));

        if (StaffAttendanceEvent::where('event_id', $eventId)->exists()) {
            return response()->json(['ok' => true, 'duplicate' => true]);
        }

        $user = User::where('attendance_machine_user_id', $machineUserId)
            ->whereIn('role', ['admin', 'teacher', 'non_teaching_staff'])
            ->first();

        abort_unless($user, 422, 'Machine user ID is not assigned to staff.');

        $punchedAt = Carbon::parse($validated['timestamp']);
        $record = AttendanceRecord::firstOrNew([
            'user_id' => $user->id,
            'attendance_date' => $punchedAt->toDateString(),
        ]);
        $direction = strtolower((string) ($validated['direction'] ?? ''));

        if ($direction === 'out' || ($direction === '' && $record->check_in_at)) {
            $record->fill([
                'check_out_at' => $record->check_out_at ?: $punchedAt,
                'departure_status' => $punchedAt->format('H:i:s') < self::CLOSING_TIME
                    ? AttendanceRecord::DEPARTURE_EARLY
                    : AttendanceRecord::DEPARTURE_NORMAL,
                'source' => 'f-g495',
                'machine_id' => $validated['device_id'],
            ]);
        } else {
            $record->fill([
                'check_in_at' => $record->check_in_at ?: $punchedAt,
                'arrival_status' => $punchedAt->format('H:i:s') <= self::RESUMPTION_TIME
                    ? AttendanceRecord::ARRIVAL_ON_TIME
                    : AttendanceRecord::ARRIVAL_LATE,
                'source' => 'f-g495',
                'machine_id' => $validated['device_id'],
            ]);
        }

        $record->save();
        StaffAttendanceEvent::create([
            'user_id' => $user->id,
            'machine_id' => $validated['device_id'],
            'machine_user_id' => $machineUserId,
            'event_id' => $eventId,
            'punched_at' => $punchedAt,
            'direction' => $direction ?: null,
            'payload' => $request->all(),
            'attendance_record_id' => $record->id,
        ]);

        return response()->json(['ok' => true, 'user' => $user->name, 'event_id' => $eventId]);
    }

    public function admsPush(Request $request)
    {
        $deviceId = (string) ($request->query('SN') ?: $request->input('SN', 'f-g495-1'));
        $allowedDeviceId = (string) config('services.staff_attendance.device_id');

        abort_unless($allowedDeviceId === '' || hash_equals($allowedDeviceId, $deviceId), 401);

        if ($request->isMethod('get')) {
            return response('GET OPTION FROM: ' . $deviceId . "\nStamp=0\n", 200)
                ->header('Content-Type', 'text/plain');
        }

        $lines = preg_split('/\r\n|\r|\n/', trim((string) $request->getContent()));
        foreach ($lines as $line) {
            $fields = preg_split('/[\t,]+/', trim($line));
            if (count($fields) < 2 || ! preg_match('/^\d{1,20}$/', $fields[0])) {
                continue;
            }

            $machineUserId = (string) $fields[0];
            $punchedAt = isset($fields[1]) ? Carbon::parse($fields[1]) : null;
            if (! $punchedAt) {
                continue;
            }

            $direction = strtolower((string) ($fields[2] ?? ''));
            $eventId = hash('sha256', implode('|', [$deviceId, $machineUserId, $punchedAt->toIso8601String(), $direction]));
            if (StaffAttendanceEvent::where('event_id', $eventId)->exists()) {
                continue;
            }

            $user = User::where('attendance_machine_user_id', $machineUserId)
                ->whereIn('role', ['admin', 'teacher', 'non_teaching_staff'])
                ->first();
            if (! $user) {
                Log::warning('F-G495 attendance user is not mapped', compact('deviceId', 'machineUserId'));
                continue;
            }

            $record = AttendanceRecord::firstOrNew([
                'user_id' => $user->id,
                'attendance_date' => $punchedAt->toDateString(),
            ]);
            $isOut = in_array($direction, ['1', 'out', 'checkout', 'check-out'], true)
                || ($direction === '' && $record->check_in_at);

            if ($isOut) {
                $record->fill([
                    'check_out_at' => $record->check_out_at ?: $punchedAt,
                    'departure_status' => $punchedAt->format('H:i:s') < self::CLOSING_TIME ? AttendanceRecord::DEPARTURE_EARLY : AttendanceRecord::DEPARTURE_NORMAL,
                ]);
            } else {
                $record->fill([
                    'check_in_at' => $record->check_in_at ?: $punchedAt,
                    'arrival_status' => $punchedAt->format('H:i:s') <= self::RESUMPTION_TIME ? AttendanceRecord::ARRIVAL_ON_TIME : AttendanceRecord::ARRIVAL_LATE,
                ]);
            }

            $record->fill(['source' => 'f-g495-adms', 'machine_id' => $deviceId])->save();
            StaffAttendanceEvent::create([
                'user_id' => $user->id,
                'machine_id' => $deviceId,
                'machine_user_id' => $machineUserId,
                'event_id' => $eventId,
                'punched_at' => $punchedAt,
                'direction' => $direction ?: null,
                'payload' => ['raw' => $line, 'query' => $request->query()],
                'attendance_record_id' => $record->id,
            ]);
        }

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    public function staffReport(Request $request)
    {
        $this->authorizeAttendanceManager();
        $date = $request->filled('date') ? Carbon::parse($request->input('date')) : today();
        $staff = User::whereIn('role', ['admin', 'teacher', 'non_teaching_staff'])->with(['attendanceRecords' => fn ($query) => $query->whereDate('attendance_date', $date)])->orderBy('name')->get();

        return view('admin.attendance.staff', compact('date', 'staff'));
    }

    public function scan(Request $request): JsonResponse
    {
        $this->authorizeAttendanceManager();

        $validated = $request->validate([
            'card_uid' => ['required', 'string', 'max:255'],
        ]);

        $cardUid = trim($validated['card_uid']);
        $user = User::with('class')
            ->where('attendance_card_uid', $cardUid)
            ->whereIn('role', ['admin', 'teacher', 'student', 'non_teaching_staff'])
            ->first();

        if (! $user) {
            return response()->json([
                'ok' => false,
                'message' => 'Card not assigned to any student or staff member.',
            ], 404);
        }

        $now = now();
        $today = $now->toDateString();
        $record = AttendanceRecord::firstOrNew([
            'user_id' => $user->id,
            'attendance_date' => $today,
        ]);

        if (! $record->check_in_at) {
            $record->fill([
                'check_in_at' => $now,
                'arrival_status' => $now->format('H:i:s') <= self::RESUMPTION_TIME
                    ? AttendanceRecord::ARRIVAL_ON_TIME
                    : AttendanceRecord::ARRIVAL_LATE,
                'checked_in_by' => Auth::id(),
            ])->save();

            // Dispatch SMS notification to parents about check-in
            SendParentAttendanceSms::dispatch($user->id, $record->id, 'checkin');

            return response()->json($this->scanResponse($record->fresh('user.class'), 'Check-in recorded.'));
        }

        if (! $record->check_out_at) {
            // Prevent accidental immediate check-out right after check-in
            if ($record->check_in_at && $now->diffInSeconds($record->check_in_at) < 10) {
                // Log ignored scan for auditing and debugging
                Log::info('Ignored attendance scan due to recent check-in', [
                    'user_id' => $user->id,
                    'card_uid' => $cardUid,
                    'seconds_since_checkin' => $now->diffInSeconds($record->check_in_at),
                ]);

                return response()->json($this->scanResponse($record->fresh('user.class'), 'Scan ignored: recent check-in.'), 200);
            }

            $record->fill([
                'check_out_at' => $now,
                'departure_status' => $now->format('H:i:s') < self::CLOSING_TIME
                    ? AttendanceRecord::DEPARTURE_EARLY
                    : AttendanceRecord::DEPARTURE_NORMAL,
                'checked_out_by' => Auth::id(),
            ])->save();

            // Dispatch SMS notification to parents about check-out
            SendParentAttendanceSms::dispatch($user->id, $record->id, 'checkout');

            return response()->json($this->scanResponse($record->fresh('user.class'), 'Check-out recorded.'));
        }

        return response()->json($this->scanResponse($record->fresh('user.class'), 'Already checked in and out today.'));
    }

    public function today(Request $request)
    {
        $this->authorizeAttendanceManager();

        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : today();

        $people = $this->attendancePeopleQuery($request)->orderBy('name')->get();
        $records = AttendanceRecord::with(['user.class'])
            ->whereDate('attendance_date', $date)
            ->whereIn('user_id', $people->pluck('id'))
            ->get()
            ->keyBy('user_id');
        $groupedPeople = $people->groupBy(fn (User $person) => $this->attendanceSectionLabel($person));

        return view('admin.attendance.today', [
            'date' => $date,
            'people' => $people,
            'groupedPeople' => $groupedPeople,
            'records' => $records,
            'classes' => $this->classesForFilters(),
            'sections' => $this->attendanceSectionDefinitions(),
            'filters' => [
                'role' => $request->string('role')->value(''),
                'class_id' => $request->string('class_id')->value(''),
                'section' => $request->string('section')->value(''),
                'date' => $date->toDateString(),
            ],
            'stats' => $this->dailyStats($date, $people),
        ]);
    }

    public function monthly(Request $request)
    {
        $this->authorizeAttendanceManager();

        $month = $request->filled('month')
            ? Carbon::parse($request->input('month') . '-01')
            : today();

        $people = $this->attendancePeopleQuery($request)->orderBy('name')->get();
        $workingDays = $this->workingDaysForMonth($month);
        $records = AttendanceRecord::whereBetween('attendance_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->whereIn('user_id', $people->pluck('id'))
            ->get()
            ->groupBy('user_id');

        $summaries = $people->map(function (User $person) use ($records, $workingDays) {
            $personRecords = $records->get($person->id, collect());
            $present = $personRecords->whereNotNull('check_in_at')->count();
            $late = $personRecords->where('arrival_status', AttendanceRecord::ARRIVAL_LATE)->count();
            $early = $personRecords->where('departure_status', AttendanceRecord::DEPARTURE_EARLY)->count();

            return [
                'person' => $person,
                'present' => $present,
                'late' => $late,
                'early' => $early,
                'absent' => max($workingDays->count() - $present, 0),
                'average_check_in' => $this->averageCheckIn($personRecords),
                'section_label' => $this->attendanceSectionLabel($person),
            ];
        });
        $groupedSummaries = $summaries->groupBy('section_label');

        return view('admin.attendance.monthly', [
            'month' => $month,
            'summaries' => $summaries,
            'groupedSummaries' => $groupedSummaries,
            'workingDaysCount' => $workingDays->count(),
            'classes' => $this->classesForFilters(),
            'sections' => $this->attendanceSectionDefinitions(),
            'filters' => [
                'role' => $request->string('role')->value(''),
                'class_id' => $request->string('class_id')->value(''),
                'section' => $request->string('section')->value(''),
                'month' => $month->format('Y-m'),
            ],
        ]);
    }

    public function people(Request $request)
    {
        $this->authorizeAttendanceManager();

        $people = $this->attendancePeopleQuery($request)
            ->with('class')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.attendance.people', [
            'people' => $people,
            'classes' => $this->classesForFilters(),
            'sections' => $this->attendanceSectionDefinitions(),
            'filters' => [
                'search' => $request->string('search')->value(''),
                'role' => $request->string('role')->value(''),
                'class_id' => $request->string('class_id')->value(''),
                'section' => $request->string('section')->value(''),
            ],
        ]);
    }

    public function updatePerson(Request $request, User $user)
    {
        $this->authorizeAttendanceManager();
        abort_unless($user->participatesInAttendance(), 404);

        $validated = $request->validate([
            'attendance_card_uid' => ['nullable', 'string', 'max:255', 'unique:users,attendance_card_uid,' . $user->id],
            'attendance_machine_user_id' => ['nullable', 'string', 'max:100', 'unique:users,attendance_machine_user_id,' . $user->id],
            'attendance_section' => ['nullable', 'string', 'max:100'],
            'can_manage_attendance' => ['nullable', 'boolean'],
        ]);

        $user->update([
            'attendance_card_uid' => filled($validated['attendance_card_uid'] ?? null)
                ? trim($validated['attendance_card_uid'])
                : null,
            'attendance_machine_user_id' => filled($validated['attendance_machine_user_id'] ?? null)
                ? trim($validated['attendance_machine_user_id'])
                : null,
            'attendance_section' => $validated['attendance_section'] ?? null,
            'can_manage_attendance' => $request->boolean('can_manage_attendance'),
        ]);

        return back()->with('success', 'Attendance access updated.');
    }

    public function createNonTeachingStaff()
    {
        $this->authorizeAttendanceManager();

        return view('admin.attendance.create-non-teaching-staff');
    }

    public function storeNonTeachingStaff(Request $request)
    {
        $this->authorizeAttendanceManager();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'registration_number' => ['required', 'string', 'unique:users,registration_number'],
            'attendance_card_uid' => ['nullable', 'string', 'max:255', 'unique:users,attendance_card_uid'],
            'attendance_section' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'registration_number' => $validated['registration_number'],
            'attendance_card_uid' => $validated['attendance_card_uid'] ?? null,
            'attendance_section' => $validated['attendance_section'] ?? null,
            'password' => $validated['password'],
            'role' => 'non_teaching_staff',
        ]);

        return redirect()->route('admin.attendance.people')->with('success', 'Non-teaching staff profile created.');
    }

    public function myAttendance(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->participatesInAttendance(), 403);

        $month = $request->filled('month')
            ? Carbon::parse($request->input('month') . '-01')
            : today();

        $records = $user->attendanceRecords()
            ->whereBetween('attendance_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->latest('attendance_date')
            ->get();

        $workingDays = $this->workingDaysForMonth($month);

        return view('attendance.my', [
            'month' => $month,
            'records' => $records,
            'workingDaysCount' => $workingDays->count(),
            'summary' => [
                'present' => $records->whereNotNull('check_in_at')->count(),
                'late' => $records->where('arrival_status', AttendanceRecord::ARRIVAL_LATE)->count(),
                'early' => $records->where('departure_status', AttendanceRecord::DEPARTURE_EARLY)->count(),
            ],
        ]);
    }

    private function scanResponse(AttendanceRecord $record, string $message): array
    {
        return [
            'ok' => true,
            'message' => $message,
            'person' => [
                'name' => $record->user->name,
                'role' => str_replace('_', ' ', $record->user->role),
                'class' => $record->user->class?->display_name,
                'registration_number' => $record->user->registration_number,
            ],
            'record' => [
                'check_in' => $record->check_in_at?->format('g:i A'),
                'check_out' => $record->check_out_at?->format('g:i A'),
                'arrival_status' => $record->arrival_status,
                'departure_status' => $record->departure_status,
            ],
        ];
    }

    private function attendancePeopleQuery(Request $request)
    {
        $section = $request->string('section')->value('');

        return User::query()
            ->with('class')
            ->whereIn('role', ['admin', 'teacher', 'student', 'non_teaching_staff'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%")
                        ->orWhere('attendance_card_uid', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->input('role')))
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->input('class_id')))
            ->when($section !== '', function ($query) use ($section) {
                $classIds = SchoolClass::all()
                    ->filter(fn (SchoolClass $class) => $class->section_key === $section)
                    ->pluck('id');

                $query->where(function ($sub) use ($section, $classIds) {
                    $sub->where('attendance_section', $section);

                    if ($classIds->isNotEmpty()) {
                        $sub->orWhere(function ($studentSub) use ($classIds) {
                            $studentSub->where('role', 'student')->whereIn('class_id', $classIds);
                        });
                    }
                });
            });
    }

    private function dailyStats(Carbon $date, ?Collection $people = null): array
    {
        $people ??= User::whereIn('role', ['admin', 'teacher', 'student', 'non_teaching_staff'])->get();
        $records = AttendanceRecord::whereDate('attendance_date', $date)
            ->whereIn('user_id', $people->pluck('id'))
            ->get();

        $present = $records->whereNotNull('check_in_at')->count();

        return [
            'expected' => $people->count(),
            'present' => $present,
            'absent' => max($people->count() - $present, 0),
            'late' => $records->where('arrival_status', AttendanceRecord::ARRIVAL_LATE)->count(),
            'early' => $records->where('departure_status', AttendanceRecord::DEPARTURE_EARLY)->count(),
        ];
    }

    private function workingDaysForMonth(Carbon $month): Collection
    {
        return collect(CarbonPeriod::create($month->copy()->startOfMonth(), $month->copy()->endOfMonth()))
            ->filter(fn (Carbon $date) => $date->isWeekday() && $date->lte(today()));
    }

    private function averageCheckIn(Collection $records): ?string
    {
        $times = $records->whereNotNull('check_in_at')
            ->map(fn (AttendanceRecord $record) => $record->check_in_at->copy()->startOfDay()->diffInSeconds($record->check_in_at));

        if ($times->isEmpty()) {
            return null;
        }

        return Carbon::today()->addSeconds((int) round($times->average()))->format('g:i A');
    }

    private function authorizeAttendanceManager(): void
    {
        abort_unless(Auth::user()?->canManageAttendance(), 403);
    }

    private function attendanceSectionDefinitions(): array
    {
        return [
            'creche' => 'Creche / Early Years',
            'primary' => 'Primary Section',
            'junior_secondary' => 'Junior Secondary',
            'senior_secondary' => 'Senior Secondary',
            'admin_office' => 'Admin Office',
            'security' => 'Security',
            'drivers' => 'Drivers',
            'cleaners' => 'Cleaners',
            'kitchen' => 'Kitchen / Catering',
            'ict' => 'ICT',
            'health' => 'Health / Nurse',
            'maintenance' => 'Maintenance',
            'other' => 'Other',
        ];
    }

    private function attendanceSectionLabel(User $person): string
    {
        if ($person->isStudent() && $person->class) {
            return $person->class->section_label;
        }

        $section = $person->attendance_section ?: 'other';

        return $this->attendanceSectionDefinitions()[$section] ?? 'Other';
    }

    private function classesForFilters(): Collection
    {
        return SchoolClass::all()
            ->sort(fn (SchoolClass $first, SchoolClass $second) => $first->classSortKey() <=> $second->classSortKey())
            ->values();
    }
}
