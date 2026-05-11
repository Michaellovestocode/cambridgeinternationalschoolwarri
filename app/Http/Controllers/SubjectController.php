<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
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
