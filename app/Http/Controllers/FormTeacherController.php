<?php

namespace App\Http\Controllers;

use App\Models\FormTeacher;
use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class FormTeacherController extends Controller
{
    /**
     * Display a listing of form teacher assignments
     */
    public function index(Request $request)
    {
        $formTeachers = FormTeacher::with(['teacher', 'schoolClass'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('teacher', function ($teacherQuery) use ($search) {
                        $teacherQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('registration_number', 'like', '%' . $search . '%');
                    })->orWhereHas('schoolClass', function ($classQuery) use ($search) {
                        $classQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('description', 'like', '%' . $search . '%');
                    });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $formTeachers->appends($request->query());

        return view('admin.form-teachers.index', compact('formTeachers'));
    }

    /**
     * Show the form for creating a new form teacher assignment
     */
    public function create()
    {
        // Get classes that don't have a form teacher assigned
        $unassignedClasses = SchoolClass::whereDoesntHave('formTeacher')
            ->orderBy('name')
            ->get();

        // Get all teachers
        $teachers = User::where('role', 'teacher')
            ->orderBy('name')
            ->get();

        return view('admin.form-teachers.create', compact('unassignedClasses', 'teachers'));
    }

    /**
     * Store a newly created form teacher assignment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:school_classes,id|unique:form_teachers,class_id',
        ], [
            'class_id.unique' => 'This class already has a form teacher assigned.',
        ]);

        $validated['assigned_date'] = now();
        $validated['is_active'] = true;

        FormTeacher::create($validated);

        return redirect()->route('admin.form-teachers.index')
            ->with('success', 'Form teacher assigned successfully!');
    }

    /**
     * Display the specified form teacher assignment
     */
    public function show(FormTeacher $formTeacher, Request $request)
    {
        $formTeacher->load(['teacher', 'schoolClass']);

        $students = $formTeacher->schoolClass
            ->students()
            ->when($request->filled('student_search'), function ($query) use ($request) {
                $search = trim($request->student_search);
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('registration_number', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->get();

        $exams = $formTeacher->schoolClass
            ->exams()
            ->orderByDesc('start_date')
            ->get();

        $attemptsByExam = [];
        foreach ($exams as $exam) {
            $attemptsByExam[$exam->id] = $exam->attempts()
                ->whereIn('user_id', $students->pluck('id'))
                ->whereIn('status', ['submitted', 'graded'])
                ->get()
                ->map(function ($attempt) {
                    return [
                        'score' => $attempt->total_score,
                    ];
                })
                ->all();
        }

        return view('admin.form-teachers.show', compact('formTeacher', 'students', 'exams', 'attemptsByExam'));
    }

    public function myLearners(Request $request)
    {
        $formTeacher = $this->activeAssignmentFor($request);

        $students = $formTeacher->schoolClass
            ->students()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('registration_number', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->get();

        return view('teacher.form-teacher.learners', compact('formTeacher', 'students'));
    }

    public function updateLearnerName(Request $request, User $student)
    {
        $formTeacher = $this->activeAssignmentFor($request);

        abort_unless(
            $student->role === 'student' && (int) $student->class_id === (int) $formTeacher->class_id,
            403,
            'You can only edit learners in your assigned class.'
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $student->update([
            'name' => $validated['name'],
        ]);

        return back()->with('success', "{$student->name}'s name has been updated.");
    }

    private function activeAssignmentFor(Request $request): FormTeacher
    {
        $formTeacher = FormTeacher::with('schoolClass')
            ->where('teacher_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        abort_unless($formTeacher, 403, 'Only active form teachers can manage class learners.');

        return $formTeacher;
    }

    /**
     * Show the form for editing the specified form teacher assignment
     */
    public function edit(FormTeacher $formTeacher)
    {
        $formTeacher->load(['teacher', 'schoolClass']);

        // Get all teachers
        $teachers = User::where('role', 'teacher')
            ->orderBy('name')
            ->get();

        return view('admin.form-teachers.edit', compact('formTeacher', 'teachers'));
    }

    /**
     * Update the specified form teacher assignment
     */
    public function update(Request $request, FormTeacher $formTeacher)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $formTeacher->update($validated);

        return redirect()->route('admin.form-teachers.index')
            ->with('success', 'Form teacher assignment updated successfully!');
    }

    /**
     * Remove the specified form teacher assignment (soft delete or just delete)
     */
    public function destroy(FormTeacher $formTeacher)
    {
        $className = $formTeacher->schoolClass->name;
        
        $formTeacher->delete();

        return redirect()->route('admin.form-teachers.index')
            ->with('success', "Form teacher removed from {$className}!");
    }
}
