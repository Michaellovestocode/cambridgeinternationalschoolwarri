<?php

namespace App\Http\Controllers;

use App\Models\LearningQuestion;
use App\Models\LearningSession;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    public function create(Request $request)
    {
        $subjects = $this->availableSubjects();
        $classes = $this->availableClasses();

        $selectedType = in_array($request->query('assessment_type'), ['classwork', 'assignment', 'quiz', 'test'], true)
            ? $request->query('assessment_type')
            : 'quiz';

        $selectedFormat = in_array($request->query('assessment_format'), ['objective', 'theory', 'mixed'], true)
            ? $request->query('assessment_format')
            : 'objective';

        return view('admin.learning-sessions.create', compact('subjects', 'classes', 'selectedType', 'selectedFormat'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedSessionData($request);
        $this->ensureAllowedAssignment((int) $data['subject_id'], (int) $data['school_class_id']);
        $data['created_by'] = Auth::id();
        $data['is_published'] = $request->boolean('is_published');
        $data['show_answers_to_students'] = $request->boolean('show_answers_to_students');

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
        $data['show_answers_to_students'] = $request->boolean('show_answers_to_students');

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

        $questionType = $request->input('question_type', $learningSession->assessment_format === 'theory' ? 'theory' : 'objective');

        $data = $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['nullable', 'in:objective,theory'],
            'option_a' => [$questionType === 'theory' ? 'nullable' : 'required', 'string', 'max:1000'],
            'option_b' => [$questionType === 'theory' ? 'nullable' : 'required', 'string', 'max:1000'],
            'option_c' => ['nullable', 'string', 'max:1000'],
            'option_d' => ['nullable', 'string', 'max:1000'],
            'correct_option' => [$questionType === 'theory' ? 'nullable' : 'required', 'in:A,B,C,D'],
            'explanation' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $options = [];
        if ($questionType === 'objective') {
            $options = array_filter([
                'A' => $data['option_a'],
                'B' => $data['option_b'],
                'C' => $data['option_c'] ?? null,
                'D' => $data['option_d'] ?? null,
            ], fn ($option) => filled($option));

            if (empty($data['correct_option']) || ! array_key_exists($data['correct_option'], $options)) {
                return back()
                    ->withErrors(['correct_option' => 'The correct option must match an option with text.'])
                    ->withInput();
            }
        }

        $learningSession->questions()->create([
            'question_text' => $data['question_text'],
            'question_type' => $questionType,
            'options' => $options,
            'correct_option' => $questionType === 'objective' ? $data['correct_option'] : null,
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
            'assessment_type' => ['nullable', 'in:classwork,assignment,quiz,test'],
            'assessment_format' => ['nullable', 'in:objective,theory,mixed'],
            'show_answers_to_students' => ['nullable', 'boolean'],
        ]);
    }

    private function availableSubjects(?LearningSession $currentSession = null)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return Subject::active()->ordered()->get();
        }

        $subjectIds = $this->exactTeachingSubjectIds($user);
        $subjects = empty($subjectIds)
            ? $user->subjects()->where('is_active', true)->ordered()->get()
            : Subject::whereIn('id', $subjectIds)
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

        $classIds = $this->exactTeachingClassIds($user);
        $classes = empty($classIds)
            ? $user->teachingClasses()->orderBy('name')->get()->merge($user->formTeacherAssignments()->where('is_active', true)->with('schoolClass')->get()->map->schoolClass->filter())
            : SchoolClass::whereIn('id', $classIds)->orderBy('name')->get();

        if ($currentSession && $currentSession->schoolClass && ! $classes->contains('id', $currentSession->school_class_id)) {
            $classes->push($currentSession->schoolClass);
        }

        return $classes->unique('id')->sortBy('name')->values();
    }

    private function ensureAllowedAssignment(int $subjectId, int $classId): void
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return;
        }

        if ($this->exactTeachingLoadIsAvailable()) {
            $allowed = DB::table('teacher_class_subject')
                ->where('teacher_id', $user->id)
                ->where('subject_id', $subjectId)
                ->where('school_class_id', $classId)
                ->exists();

            if (! $allowed) {
                abort(403, 'You can only create lessons for assigned subject and class combinations.');
            }

            return;
        }

        $teachesSubject = $user->subjects()->where('subjects.id', $subjectId)->exists();
        $assignedToClass = $user->teachingClasses()->whereKey($classId)->exists()
            || $user->formTeacherAssignments()->where('is_active', true)->where('class_id', $classId)->exists();

        if (! $teachesSubject || ! $assignedToClass) {
            abort(403, 'You can only create lessons for assigned subject and class combinations.');
        }
    }

    private function exactTeachingLoadIsAvailable(): bool
    {
        return Schema::hasTable('teacher_class_subject');
    }

    private function exactTeachingSubjectIds($user): array
    {
        if (! $this->exactTeachingLoadIsAvailable()) {
            return [];
        }

        return DB::table('teacher_class_subject')
            ->where('teacher_id', $user->id)
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function exactTeachingClassIds($user): array
    {
        if (! $this->exactTeachingLoadIsAvailable()) {
            return [];
        }

        return DB::table('teacher_class_subject')
            ->where('teacher_id', $user->id)
            ->pluck('school_class_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function authorizeSession(LearningSession $learningSession): void
    {
        $user = Auth::user();

        if (! $user->isAdmin() && $learningSession->created_by !== $user->id) {
            abort(403);
        }
    }
}
