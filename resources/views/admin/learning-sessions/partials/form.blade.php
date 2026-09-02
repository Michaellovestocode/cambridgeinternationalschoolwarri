@if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-lg p-4">
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" class="space-y-5">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Class</label>
            <select name="school_class_id" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                <option value="">Choose class</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected(old('school_class_id', $learningSession->school_class_id ?? '') == $class->id)>
                        {{ $class->display_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Subject</label>
            <select name="subject_id" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                <option value="">Choose subject</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(old('subject_id', $learningSession->subject_id ?? '') == $subject->id)>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Assessment Type</label>
            <select name="assessment_type" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                <option value="classwork" @selected(old('assessment_type', $learningSession->assessment_type ?? request('assessment_type', 'quiz')) == 'classwork')>Classwork</option>
                <option value="assignment" @selected(old('assessment_type', $learningSession->assessment_type ?? request('assessment_type', 'quiz')) == 'assignment')>Assignment</option>
                <option value="quiz" @selected(old('assessment_type', $learningSession->assessment_type ?? request('assessment_type', 'quiz')) == 'quiz')>Quiz</option>
                <option value="test" @selected(old('assessment_type', $learningSession->assessment_type ?? request('assessment_type', 'quiz')) == 'test')>Test</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Estimated Minutes</label>
            <input type="number" min="1" max="300" name="estimated_minutes" value="{{ old('estimated_minutes', $learningSession->estimated_minutes ?? 20) }}" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Assessment Format</label>
            <select id="assessment-format-select" name="assessment_format" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                <option value="objective" @selected(old('assessment_format', $learningSession->assessment_format ?? request('assessment_format', 'objective')) == 'objective')>Objective</option>
                <option value="theory" @selected(old('assessment_format', $learningSession->assessment_format ?? request('assessment_format', 'objective')) == 'theory')>Theory</option>
                <option value="mixed" @selected(old('assessment_format', $learningSession->assessment_format ?? request('assessment_format', 'objective')) == 'mixed')>Mixed</option>
            </select>
        </div>
        <div class="flex items-end">
            <div class="rounded-xl bg-cyan-50 border border-cyan-100 px-4 py-3 text-sm text-cyan-800 w-full">
                <strong>Type:</strong> {{ ucfirst(old('assessment_type', $learningSession->assessment_type ?? request('assessment_type', 'quiz'))) }}
                <span class="mx-2 text-cyan-400">•</span>
                <strong>Format:</strong> <span id="assessment-format-label">{{ ucfirst(old('assessment_format', $learningSession->assessment_format ?? 'objective')) }}</span>
            </div>
        </div>
    </div>

    <div id="assessment-format-template" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div id="template-objective" class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-cyan-100 px-2 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-cyan-700">Objective</span>
            </div>
            <p class="text-sm font-semibold text-slate-800">Multiple-choice format</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-600">
                <div class="rounded-xl border border-cyan-200 bg-white p-3">A. Option A</div>
                <div class="rounded-xl border border-cyan-200 bg-white p-3">B. Option B</div>
                <div class="rounded-xl border border-cyan-200 bg-white p-3">C. Option C</div>
                <div class="rounded-xl border border-cyan-200 bg-white p-3">D. Option D</div>
            </div>
            <p class="text-xs text-slate-500">Students choose the correct option. The system stores the correct answer and marks the response automatically.</p>
        </div>

        <div id="template-theory" class="hidden space-y-3">
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-violet-100 px-2 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-violet-700">Theory</span>
            </div>
            <p class="text-sm font-semibold text-slate-800">Written response format</p>
            <div class="rounded-xl border border-violet-200 bg-white p-4 text-sm text-slate-600">
                <p class="font-semibold text-violet-800 mb-2">Question prompt</p>
                <p>Explain the process...</p>
                <div class="mt-3 h-24 rounded-lg border border-dashed border-violet-300 bg-violet-50 p-3 text-violet-700">Students answer in a long text box.</div>
            </div>
            <p class="text-xs text-slate-500">This format is best for essays, explanations, structured writing, or extended responses.</p>
        </div>

        <div id="template-mixed" class="hidden space-y-3">
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-700">Mixed</span>
            </div>
            <p class="text-sm font-semibold text-slate-800">Objective + theory in one task</p>
            <div class="grid grid-cols-1 gap-3 text-sm text-slate-600">
                <div class="rounded-xl border border-emerald-200 bg-white p-3">Section 1: Objective questions</div>
                <div class="rounded-xl border border-emerald-200 bg-white p-3">Section 2: Theory / explanation questions</div>
            </div>
            <p class="text-xs text-slate-500">Use mixed format when you want a quick check and a written explanation in the same classroom task.</p>
        </div>
    </div>

    @if($subjects->isEmpty() || $classes->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4 text-sm">
            No available class and subject assignment was found for this account yet.
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Session Title</label>
            <input type="text" name="title" value="{{ old('title', $learningSession->title ?? '') }}" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Topic</label>
            <input type="text" name="topic" value="{{ old('topic', $learningSession->topic ?? '') }}" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">{{ old('description', $learningSession->description ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Learning Goals</label>
        <textarea name="learning_goals" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="One goal per line">{{ old('learning_goals', $learningSession->learning_goals ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Lesson Content</label>
        <textarea name="lesson_content" rows="10" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Write the teaching note, worked example, or explanation here.">{{ old('lesson_content', $learningSession->lesson_content ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="inline-flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3">
            <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300" @checked(old('is_published', $learningSession->is_published ?? false))>
            <span class="font-semibold text-gray-700">Publish for students</span>
        </label>

        <label class="inline-flex items-center gap-3 rounded-xl border border-violet-200 bg-violet-50 p-3">
            <input type="checkbox" name="show_answers_to_students" value="1" class="rounded border-gray-300" @checked(old('show_answers_to_students', $learningSession->show_answers_to_students ?? false))>
            <span class="font-semibold text-gray-700">Reveal answer script after marking</span>
        </label>
    </div>

    <div class="pt-2">
        <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-3 rounded-lg font-bold">
            Save Learning Session
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const assessmentFormatSelect = document.getElementById('assessment-format-select');
        const assessmentFormatLabel = document.getElementById('assessment-format-label');
        const objectiveTemplate = document.getElementById('template-objective');
        const theoryTemplate = document.getElementById('template-theory');
        const mixedTemplate = document.getElementById('template-mixed');

        if (!assessmentFormatSelect) return;

        const syncFormatTemplate = () => {
            const format = assessmentFormatSelect.value;
            const labels = { objective: 'Objective', theory: 'Theory', mixed: 'Mixed' };
            assessmentFormatLabel.textContent = labels[format] || 'Objective';

            objectiveTemplate.classList.toggle('hidden', format !== 'objective');
            theoryTemplate.classList.toggle('hidden', format !== 'theory');
            mixedTemplate.classList.toggle('hidden', format !== 'mixed');
        };

        assessmentFormatSelect.addEventListener('change', syncFormatTemplate);
        syncFormatTemplate();
    });
</script>
