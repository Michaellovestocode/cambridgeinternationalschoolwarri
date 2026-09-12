<?php

namespace App\Http\Controllers;

use App\Models\LearningQuestion;
use App\Models\LearningSession;
use App\Models\LearningComment;
use App\Models\LearningAttachment;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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

    public function createTopic()
    {
        $subjects = $this->availableSubjects();
        $classes = $this->availableClasses();

        return view('admin.learning-sessions.create-topic', compact('subjects', 'classes'));
    }

    public function storeTopic(Request $request)
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'topic' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'lesson_content' => ['required', 'string'],
            'learning_goals' => ['nullable', 'string'],
            'estimated_minutes' => ['required', 'integer', 'min:1', 'max:300'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $this->ensureAllowedAssignment((int) $data['subject_id'], (int) $data['school_class_id']);
        $session = LearningSession::create([
            ...$data,
            'created_by' => Auth::id(),
            'assessment_type' => 'lesson',
            'assessment_format' => 'theory',
            'is_published' => $request->boolean('is_published'),
            'show_answers_to_students' => false,
        ]);

        return redirect()->route('admin.learning-sessions.edit', $session)
            ->with('success', 'Learning topic created. You can now upload materials and join the class discussion.');
    }

    public function store(Request $request)
    {
        $data = $this->validatedSessionData($request);
        $this->ensureAllowedAssignment((int) $data['subject_id'], (int) $data['school_class_id']);
        $data['created_by'] = Auth::id();
        $data['is_published'] = $request->boolean('is_published');
        $data['show_answers_to_students'] = $request->boolean('show_answers_to_students');

        $session = DB::transaction(function () use ($data, $request) {
            $session = LearningSession::create($data);
            $this->storeInlineQuestions($request, $session);

            return $session;
        });

        return redirect()
            ->route('admin.learning-sessions.edit', $session)
            ->with('success', 'Learning session created. Add practice questions below.');
    }

    public function edit(LearningSession $learningSession)
    {
        $this->authorizeSession($learningSession);

        $subjects = $this->availableSubjects($learningSession);
        $classes = $this->availableClasses($learningSession);
        $learningSession->load(['subject', 'schoolClass', 'questions', 'attachments', 'comments' => fn ($query) => $query->whereNull('parent_id')->with(['user', 'replies.user'])->latest()]);
        $feedbackCounts = $learningSession->feedback()
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.learning-sessions.edit', compact('learningSession', 'subjects', 'classes', 'feedbackCounts'));
    }

    public function uploadAttachment(Request $request, LearningSession $learningSession)
    {
        $this->authorizeSession($learningSession);
        $validated = $request->validate([
            'attachment' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,gif,webp'],
        ]);

        $file = $validated['attachment'];
        $learningSession->attachments()->create([
            'uploaded_by' => Auth::id(),
            'name' => $file->getClientOriginalName(),
            'path' => $file->store('learning-attachments', 'public'),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return back()->with('success', 'Learning material uploaded.');
    }

    public function comment(Request $request, LearningSession $learningSession)
    {
        $this->authorizeSession($learningSession);
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
            'parent_id' => ['nullable', 'integer', 'exists:learning_comments,id'],
        ]);

        if (! empty($validated['parent_id']) && ! LearningComment::whereKey($validated['parent_id'])->where('learning_session_id', $learningSession->id)->exists()) {
            abort(422, 'That discussion thread does not belong to this lesson.');
        }

        LearningComment::create([
            'learning_session_id' => $learningSession->id,
            'user_id' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Teacher reply posted.');
    }

    public function moderateComment(Request $request, LearningComment $comment)
    {
        $this->authorizeSession($comment->learningSession);
        $action = $request->validate(['action' => ['required', 'in:pin,hide,show']])['action'];
        $comment->update([
            'is_pinned' => $action === 'pin' ? ! $comment->is_pinned : $comment->is_pinned,
            'is_hidden' => $action === 'hide' ? true : ($action === 'show' ? false : $comment->is_hidden),
        ]);

        return back()->with('success', 'Discussion updated.');
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
            'marks' => ['required', 'numeric', 'min:0.01'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'option_a' => [$questionType === 'theory' ? 'nullable' : 'required', 'string', 'max:1000'],
            'option_b' => [$questionType === 'theory' ? 'nullable' : 'required', 'string', 'max:1000'],
            'option_c' => ['nullable', 'string', 'max:1000'],
            'option_d' => ['nullable', 'string', 'max:1000'],
            'options' => ['nullable', 'array'],
            'correct_option' => [$questionType === 'theory' ? 'nullable' : 'required', 'in:A,B,C,D'],
            'explanation' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $options = [];
        if ($questionType === 'objective') {
            $rawOptions = $request->input('options', []);
            $optionA = $rawOptions['A'] ?? $data['option_a'] ?? null;
            $optionB = $rawOptions['B'] ?? $data['option_b'] ?? null;
            $optionC = $rawOptions['C'] ?? $data['option_c'] ?? null;
            $optionD = $rawOptions['D'] ?? $data['option_d'] ?? null;

            $options = array_filter([
                'A' => $optionA,
                'B' => $optionB,
                'C' => $optionC,
                'D' => $optionD,
            ], fn ($option) => filled($option));

            if (empty($data['correct_option']) || ! array_key_exists($data['correct_option'], $options)) {
                return back()
                    ->withErrors(['correct_option' => 'The correct option must match an option with text.'])
                    ->withInput();
            }
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('learning-question-images', 'public');
        }

        $learningSession->questions()->create([
            'question_text' => $data['question_text'],
            'question_type' => $questionType,
            'options' => $options,
            'correct_option' => $questionType === 'objective' ? $data['correct_option'] : null,
            'explanation' => $data['explanation'] ?? null,
            'order' => $data['order'] ?? ($learningSession->questions()->count() + 1),
            'marks' => (float) $data['marks'],
            'image_path' => $imagePath,
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
            'is_published' => ['nullable', 'boolean'],
            'show_answers_to_students' => ['nullable', 'boolean'],
            'questions' => ['nullable', 'array'],
            'questions.*.question_text' => ['nullable', 'string'],
            'questions.*.marks' => ['nullable', 'numeric', 'min:0.01'],
            'questions.*.question_type' => ['nullable', 'in:objective,theory'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.correct_option' => ['nullable', 'in:A,B,C,D'],
        ]);
    }

    private function storeInlineQuestions(Request $request, LearningSession $session): void
    {
        foreach ($request->input('questions', []) as $index => $question) {
            if (! filled($question['question_text'] ?? null)) {
                continue;
            }

            $questionType = $question['question_type'] ?? ($session->assessment_format === 'theory' ? 'theory' : 'objective');
            $options = $questionType === 'objective'
                ? array_filter($question['options'] ?? [], fn ($option) => filled($option))
                : [];

            if ($questionType === 'objective' && (! filled($question['correct_option'] ?? null) || ! array_key_exists($question['correct_option'], $options))) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "questions.{$index}.correct_option" => 'The correct option must match an option with text.',
                ]);
            }

            $session->questions()->create([
                'question_text' => $question['question_text'],
                'question_type' => $questionType,
                'options' => $options,
                'correct_option' => $questionType === 'objective' ? $question['correct_option'] : null,
                'order' => $index + 1,
                'marks' => (float) ($question['marks'] ?? 1),
            ]);
        }
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
