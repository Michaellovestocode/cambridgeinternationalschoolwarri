<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use App\Models\FormTeacher;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubjectController extends Controller
{
    /**
     * Display list of all subjects
     */
    public function index()
    {
        $subjects = Subject::withCount(['teachers', 'exams'])
                           ->latest()
                           ->paginate(20);
        return view('admin.subjects.index', compact('subjects'));
    }

    /**
     * Show the logged-in teacher's assigned subjects and classes.
     */
    public function mySubjects()
    {
        $user = Auth::user();
        abort_unless($user->isTeacher(), 403);

        $teachingClasses = $user->teachingClasses()
            ->withCount('students')
            ->orderBy('name')
            ->get();

        $ownedFormClasses = $this->formTeacherClassesFor($user);
        $ownedEarlyPrimaryClasses = $ownedFormClasses
            ->filter(fn (SchoolClass $class) => $this->isEarlyYearsOrPrimaryClass($class))
            ->values();
        $allTeachingClasses = $teachingClasses
            ->merge($ownedEarlyPrimaryClasses)
            ->unique('id')
            ->sortBy('name')
            ->values();

        $assignedSubjectsQuery = $user->subjects()
            ->with(['classes' => fn ($query) => $query->withCount('students')->orderBy('name')])
            ->withCount('exams')
            ->where('is_active', true)
            ->orderBy('name');

        if (Schema::hasTable('teacher_class_subject')) {
            $assignedSubjectsQuery->whereIn('subjects.id', $this->exactTeachingSubjectIds($user));
        }

        $assignedSubjects = $assignedSubjectsQuery->get();

        $ownedClassSubjects = collect();

        if ($ownedEarlyPrimaryClasses->isNotEmpty()) {
            $ownedClassIds = $ownedEarlyPrimaryClasses->pluck('id')->all();
            $ownedClassSubjects = Subject::where('is_active', true)
                ->where(function ($query) use ($ownedClassIds, $ownedEarlyPrimaryClasses) {
                    $query->whereHas('classes', fn ($classQuery) => $classQuery->whereIn('school_classes.id', $ownedClassIds));

                    foreach ($ownedEarlyPrimaryClasses as $class) {
                        $query->orWhereIn('class_level', $this->subjectClassLevelCandidates($class));
                    }
                })
                ->with(['classes' => fn ($query) => $query->withCount('students')->orderBy('name')])
                ->withCount('exams')
                ->orderBy('name')
                ->get();
        }

        $subjects = $assignedSubjects
            ->merge($ownedClassSubjects)
            ->unique('id')
            ->sortBy('name')
            ->values()
            ->map(function (Subject $subject) use ($user, $allTeachingClasses, $ownedEarlyPrimaryClasses, $assignedSubjects) {
                $subjectClassIds = $subject->classes->pluck('id');

                if (Schema::hasTable('teacher_class_subject')) {
                    $assignedClasses = $allTeachingClasses
                        ->whereIn('id', $this->exactTeachingClassIdsForSubject($user, (int) $subject->id))
                        ->values();
                } else {
                    $assignedClasses = $subjectClassIds->isEmpty()
                        ? $allTeachingClasses->filter(fn (SchoolClass $class) => $this->subjectMatchesClassLevel($subject, $class))->values()
                        : $allTeachingClasses->whereIn('id', $subjectClassIds)->values();
                }

                if ($assignedSubjects->contains('id', $subject->id)) {
                    $assignedClasses = $assignedClasses
                        ->merge($ownedEarlyPrimaryClasses)
                        ->unique('id')
                        ->sortBy('name')
                        ->values();
                }

                $subject->setRelation('assignedClasses', $assignedClasses);

                return $subject;
            })
            ->values();

        return view('admin.subjects.my-subjects', [
            'subjects' => $subjects,
            'teachingClasses' => $allTeachingClasses,
            'ownedEarlyPrimaryClasses' => $ownedEarlyPrimaryClasses,
        ]);
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

    private function exactTeachingSubjectIds(User $teacher): array
    {
        if (! Schema::hasTable('teacher_class_subject')) {
            return [];
        }

        return DB::table('teacher_class_subject')
            ->where('teacher_id', $teacher->id)
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function exactTeachingClassIdsForSubject(User $teacher, int $subjectId): array
    {
        if (! Schema::hasTable('teacher_class_subject')) {
            return [];
        }

        return DB::table('teacher_class_subject')
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $subjectId)
            ->pluck('school_class_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
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

    private function subjectMatchesClassLevel(Subject $subject, SchoolClass $class): bool
    {
        if (! filled($subject->class_level)) {
            return false;
        }

        $classLevel = strtolower(trim((string) $subject->class_level));
        $candidates = collect($this->subjectClassLevelCandidates($class))
            ->map(fn ($candidate) => strtolower(trim((string) $candidate)))
            ->all();

        return in_array($classLevel, $candidates, true);
    }

    /**
     * Show form to create new subject
     */
    public function create()
    {
        return view('admin.subjects.create');
    }

    /**
     * Store new subject
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
            'code' => 'nullable|string|max:50|unique:subjects,code',
            'description' => 'nullable|string',
            'class_level' => 'nullable|string|max:255',
        ]);

        Subject::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'],
            'class_level' => $validated['class_level'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.subjects.index')
                       ->with('success', 'Subject created successfully!');
    }

    /**
     * Show form to edit subject
     */
    public function edit($id)
    {
        $subject = Subject::findOrFail($id);
        return view('admin.subjects.edit', compact('subject'));
    }

    /**
     * Update subject
     */
    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name,' . $id,
            'code' => 'nullable|string|max:50|unique:subjects,code,' . $id,
            'description' => 'nullable|string',
            'class_level' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $subject->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'],
            'class_level' => $validated['class_level'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.subjects.index')
                       ->with('success', 'Subject updated successfully!');
    }

    /**
     * Delete subject
     */
    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return redirect()->route('admin.subjects.index')
                       ->with('success', 'Subject deleted successfully!');
    }

    /**
     * Show form to assign subjects to teachers
     */
    public function assignTeachers($subjectId)
    {
        $subject = Subject::with('teachers')->findOrFail($subjectId);
        $teachers = User::where('role', 'teacher')->get();
        
        return view('admin.subjects.assign', compact('subject', 'teachers'));
    }

    /**
     * Save teacher-subject assignments
     */
    public function updateTeachers(Request $request, $subjectId)
    {
        $subject = Subject::findOrFail($subjectId);

        $validated = $request->validate([
            'teachers' => 'required|array',
            'teachers.*' => 'exists:users,id',
        ]);

        $previousTeacherIds = $subject->teachers()->pluck('users.id')->map(fn ($id) => (int) $id)->all();

        // Sync the teachers (this will add new and remove unchecked ones)
        $subject->teachers()->sync($validated['teachers']);
        $this->syncExactLoadForSubjectTeacherSync($subject, $previousTeacherIds, $validated['teachers']);

        return redirect()->route('admin.subjects.index')
                       ->with('success', 'Teachers assigned to subject successfully!');
    }

    /**
     * Show form to assign subjects to a teacher
     */
    public function assignSubjects($teacherId)
    {
        $teacher = User::where('role', 'teacher')->with('subjects')->findOrFail($teacherId);
        $subjects = Subject::where('is_active', true)->get();
        
        return view('admin.subjects.assign-teacher', compact('teacher', 'subjects'));
    }

    /**
     * Save subject assignments for a teacher
     */
    public function updateSubjects(Request $request, $teacherId)
    {
        $teacher = User::where('role', 'teacher')->findOrFail($teacherId);

        $validated = $request->validate([
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $previousSubjectIds = $teacher->subjects()->pluck('subjects.id')->map(fn ($id) => (int) $id)->all();

        // Sync the subjects
        $teacher->subjects()->sync($validated['subjects']);
        $this->syncExactLoadForTeacherSubjectSync($teacher, $previousSubjectIds, $validated['subjects']);

        return redirect()->route('admin.teachers')
                       ->with('success', 'Subjects assigned to teacher successfully!');
    }

    private function syncExactLoadForSubjectTeacherSync(Subject $subject, array $previousTeacherIds, array $newTeacherIds): void
    {
        if (! Schema::hasTable('teacher_class_subject')) {
            return;
        }

        $newTeacherIds = collect($newTeacherIds)->map(fn ($id) => (int) $id)->unique()->values()->all();
        $removedTeacherIds = array_diff($previousTeacherIds, $newTeacherIds);

        if (! empty($removedTeacherIds)) {
            DB::table('teacher_class_subject')
                ->where('subject_id', $subject->id)
                ->whereIn('teacher_id', $removedTeacherIds)
                ->delete();
        }

        foreach (array_diff($newTeacherIds, $previousTeacherIds) as $teacherId) {
            $teacher = User::where('role', 'teacher')->find($teacherId);

            if ($teacher) {
                $this->backfillExactLoadForTeacherSubjects($teacher, [$subject->id]);
            }
        }
    }

    private function syncExactLoadForTeacherSubjectSync(User $teacher, array $previousSubjectIds, array $newSubjectIds): void
    {
        if (! Schema::hasTable('teacher_class_subject')) {
            return;
        }

        $newSubjectIds = collect($newSubjectIds)->map(fn ($id) => (int) $id)->unique()->values()->all();
        $removedSubjectIds = array_diff($previousSubjectIds, $newSubjectIds);

        if (! empty($removedSubjectIds)) {
            DB::table('teacher_class_subject')
                ->where('teacher_id', $teacher->id)
                ->whereIn('subject_id', $removedSubjectIds)
                ->delete();
        }

        $this->backfillExactLoadForTeacherSubjects($teacher, array_diff($newSubjectIds, $previousSubjectIds));
    }

    private function backfillExactLoadForTeacherSubjects(User $teacher, array $subjectIds): void
    {
        if (empty($subjectIds) || ! Schema::hasTable('teacher_class_subject')) {
            return;
        }

        $classIds = $teacher->teachingClasses()->pluck('school_classes.id')->map(fn ($id) => (int) $id);
        $now = now();

        foreach ($classIds as $classId) {
            foreach ($subjectIds as $subjectId) {
                DB::table('teacher_class_subject')->updateOrInsert(
                    [
                        'teacher_id' => $teacher->id,
                        'school_class_id' => $classId,
                        'subject_id' => (int) $subjectId,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}
