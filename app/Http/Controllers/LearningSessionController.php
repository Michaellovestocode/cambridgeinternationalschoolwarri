<?php

namespace App\Http\Controllers;

use App\Models\LearningQuestion;
use App\Models\LearningSession;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LearningSessionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $sessions = LearningSession::with(['subject', 'schoolClass', 'creator'])
            ->withCount('questions')
            ->when(! $user->isAdmin(), fn ($query) => $query->where('created_by', $user->id))
            ->latest()
            ->paginate(15);

        return view('admin.learning-sessions.index', compact('sessions'));
    }

    public function create()
    {
        $subjects = $this->availableSubjects();
        $classes = $this->availableClasses();

        return view('admin.learning-sessions.create', compact('subjects', 'classes'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedSessionData($request);
        $this->ensureAllowedAssignment((int) $data['subject_id'], (int) $data['school_class_id']);
        $data['created_by'] = Auth::id();
        $data['is_published'] = $request->boolean('is_published');

        $session = LearningSession::create($data);

        return redirect()
            ->route('admin.learning-sessions.edit', $session)
            ->with('success', 'Learning session created. Add practice questions below.');
    }

    public function edit(LearningSession $learningSession)
    {
        $this->authorizeSession($learningSession);

        $subjects = $this->availableSubjects($learningSession);
        $classes = $this->availableClasses($learningSession);
        $learningSession->load(['subject', 'schoolClass', 'questions']);

        return view('admin.learning-sessions.edit', compact('learningSession', 'subjects', 'classes'));
    }

    public function update(Request $request, LearningSession $learningSession)
    {
        $data = $this->validatedSessionData($request);
        $this->authorizeSession($learningSession);
        $this->ensureAllowedAssignment((int) $data['subject_id'], (int) $data['school_class_id']);
        $data['is_published'] = $request->boolean('is_published');

        $learningSession->update($data);

        return redirect()
            ->route('admin.learning-sessions.edit', $learningSession)
            ->with('success', 'Learning session updated.');
    }

    public function destroy(LearningSession $learningSession)
    {
        $this->authorizeSession($learningSession);

        $learningSession->delete();

        return redirect()
            ->route('admin.learning-sessions.index')
            ->with('success', 'Learning session deleted.');
    }

    public function storeQuestion(Request $request, LearningSession $learningSession)
    {
        $this->authorizeSession($learningSession);

        $data = $request->validate([
            'question_text' => ['required', 'string'],
            'option_a' => ['required', 'string', 'max:1000'],
            'option_b' => ['required', 'string', 'max:1000'],
            'option_c' => ['nullable', 'string', 'max:1000'],
            'option_d' => ['nullable', 'string', 'max:1000'],
            'correct_option' => ['required', 'in:A,B,C,D'],
            'explanation' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $options = array_filter([
            'A' => $data['option_a'],
            'B' => $data['option_b'],
            'C' => $data['option_c'] ?? null,
            'D' => $data['option_d'] ?? null,
        ], fn ($option) => filled($option));

        if (! array_key_exists($data['correct_option'], $options)) {
            return back()
                ->withErrors(['correct_option' => 'The correct option must have option text.'])
                ->withInput();
        }

        $learningSession->questions()->create([
            'question_text' => $data['question_text'],
            'options' => $options,
            'correct_option' => $data['correct_option'],
            'explanation' => $data['explanation'] ?? null,
            'order' => $data['order'] ?? ($learningSession->questions()->count() + 1),
        ]);

        return redirect()
            ->route('admin.learning-sessions.edit', $learningSession)
            ->with('success', 'Practice question added.');
    }

    public function destroyQuestion(LearningQuestion $question)
    {
        $session = $question->learningSession;
        $this->authorizeSession($session);

        $question->delete();

        return redirect()
            ->route('admin.learning-sessions.edit', $session)
            ->with('success', 'Practice question deleted.');
    }

    private function validatedSessionData(Request $request): array
    {
        return $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'topic' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'lesson_content' => ['nullable', 'string'],
            'learning_goals' => ['nullable', 'string'],
            'estimated_minutes' => ['required', 'integer', 'min:1', 'max:300'],
        ]);
    }

    private function availableSubjects(?LearningSession $currentSession = null)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return Subject::active()->ordered()->get();
        }

        $subjects = $user->subjects()
            ->where('is_active', true)
            ->ordered()
            ->get();

        if ($currentSession && $currentSession->subject && ! $subjects->contains('id', $currentSession->subject_id)) {
            $subjects->push($currentSession->subject);
        }

        return $subjects->sortBy('name')->values();
    }

    private function availableClasses(?LearningSession $currentSession = null)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return SchoolClass::orderBy('name')->get();
        }

        $classIds = $user->formTeacherAssignments()
            ->where('is_active', true)
            ->pluck('class_id');

        $classes = SchoolClass::whereIn('id', $classIds)
            ->orderBy('name')
            ->get();

        if ($currentSession && $currentSession->schoolClass && ! $classes->contains('id', $currentSession->school_class_id)) {
            $classes->push($currentSession->schoolClass);
        }

        return $classes->sortBy('name')->values();
    }

    private function ensureAllowedAssignment(int $subjectId, int $classId): void
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return;
        }

        $teachesSubject = $user->subjects()
            ->where('subjects.id', $subjectId)
            ->exists();

        $assignedToClass = $user->formTeacherAssignments()
            ->where('is_active', true)
            ->where('class_id', $classId)
            ->exists();

        if (! $teachesSubject || ! $assignedToClass) {
            abort(403, 'You can only create lessons for assigned subject and class combinations.');
        }
    }

    private function authorizeSession(LearningSession $learningSession): void
    {
        $user = Auth::user();

        if (! $user->isAdmin() && $learningSession->created_by !== $user->id) {
            abort(403);
        }
    }
}
