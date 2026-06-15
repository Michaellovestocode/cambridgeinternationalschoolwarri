<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use App\Models\FormTeacher;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $ownedEarlyPrimaryClasses = $this->earlyPrimaryFormTeacherClassesFor($user);
        $allTeachingClasses = $teachingClasses
            ->merge($ownedEarlyPrimaryClasses)
            ->unique('id')
            ->sortBy('name')
            ->values();

        $assignedSubjects = $user->subjects()
            ->with(['classes' => fn ($query) => $query->withCount('students')->orderBy('name')])
            ->withCount('exams')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $ownedClassSubjects = collect();

        if ($ownedEarlyPrimaryClasses->isNotEmpty()) {
            $ownedClassIds = $ownedEarlyPrimaryClasses->pluck('id')->all();
            $ownedClassSubjects = Subject::where('is_active', true)
                ->whereHas('classes', fn ($query) => $query->whereIn('school_classes.id', $ownedClassIds))
                ->with(['classes' => fn ($query) => $query->withCount('students')->orderBy('name')])
                ->withCount('exams')
                ->orderBy('name')
                ->get();

            if ($ownedClassSubjects->isEmpty()) {
                $ownedClassSubjects = Subject::where('is_active', true)
                    ->with(['classes' => fn ($query) => $query->withCount('students')->orderBy('name')])
                    ->withCount('exams')
                    ->orderBy('name')
                    ->get();
            }
        }

        $subjects = $assignedSubjects
            ->merge($ownedClassSubjects)
            ->unique('id')
            ->sortBy('name')
            ->values()
            ->map(function (Subject $subject) use ($allTeachingClasses, $ownedEarlyPrimaryClasses) {
                $subjectClassIds = $subject->classes->pluck('id');
                $eligibleClasses = $allTeachingClasses->filter(function (SchoolClass $class) use ($ownedEarlyPrimaryClasses) {
                    if ($this->isEarlyYearsOrPrimaryClass($class)) {
                        return $ownedEarlyPrimaryClasses->contains('id', $class->id);
                    }

                    return true;
                })->values();

                $assignedClasses = $subjectClassIds->isEmpty()
                    ? $eligibleClasses
                    : $eligibleClasses->whereIn('id', $subjectClassIds)->values();

                $subject->setRelation('assignedClasses', $assignedClasses);

                return $subject;
            });

        return view('admin.subjects.my-subjects', [
            'subjects' => $subjects,
            'teachingClasses' => $allTeachingClasses,
            'ownedEarlyPrimaryClasses' => $ownedEarlyPrimaryClasses,
        ]);
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
        ]);

        Subject::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'],
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
            'is_active' => 'boolean',
        ]);

        $subject->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'],
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

        // Sync the teachers (this will add new and remove unchecked ones)
        $subject->teachers()->sync($validated['teachers']);

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

        // Sync the subjects
        $teacher->subjects()->sync($validated['subjects']);

        return redirect()->route('admin.teachers')
                       ->with('success', 'Subjects assigned to teacher successfully!');
    }
}
