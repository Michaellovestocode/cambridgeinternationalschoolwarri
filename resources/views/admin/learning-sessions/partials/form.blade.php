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

    @php($selectedClassIds = old('school_class_ids', $learningSession?->targetClasses?->pluck('id')->all() ?: ($learningSession?->school_class_id ? [$learningSession->school_class_id] : [])))
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
        <div>
            <label for="subject-id" class="block text-sm font-semibold text-gray-700 mb-1">1. Subject</label>
            <select id="subject-id" name="subject_id" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
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
            <select id="assessment-type-select" name="assessment_type" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
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

    <div id="class-assignment-panel" class="hidden rounded-2xl border border-cyan-100 bg-cyan-50/60 p-4 sm:p-5">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <label class="block text-sm font-bold text-gray-800">2. Classes / Arms</label>
                <p class="text-xs text-gray-600">Tick every arm taking the selected subject.</p>
            </div>
            <p id="class-assignment-count" class="text-xs font-semibold text-cyan-800"></p>
        </div>
        <div id="class-assignment-list" class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($classes as $class)
                <label data-class-id="{{ $class->id }}" class="hidden min-w-0 cursor-pointer items-center gap-3 rounded-xl border border-white bg-white px-3 py-3 text-sm font-medium text-gray-700 shadow-sm transition hover:border-cyan-300 hover:bg-cyan-50">
                    <input type="checkbox" name="school_class_ids[]" value="{{ $class->id }}" class="shrink-0 rounded border-gray-300 text-cyan-600 focus:ring-cyan-500" @checked(in_array($class->id, array_map('intval', $selectedClassIds), true))>
                    <span class="break-words">{{ $class->display_name }}</span>
                </label>
            @endforeach
        </div>
        <p id="no-classes-for-subject" class="mt-3 hidden text-sm font-semibold text-amber-800">You are not assigned to this subject for any class.</p>
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
                <strong>Type:</strong> <span id="assessment-type-label">{{ ucfirst(old('assessment_type', $learningSession->assessment_type ?? request('assessment_type', 'quiz'))) }}</span>
                <span class="mx-2 text-cyan-400">•</span>
                <strong>Format:</strong> <span id="assessment-format-label">{{ ucfirst(old('assessment_format', $learningSession->assessment_format ?? 'objective')) }}</span>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Question Builder</h3>
                <p class="text-xs text-slate-600">Add as many questions as you need before publishing the task.</p>
            </div>
            <button id="add-question-block" type="button" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                Add Another Question
            </button>
        </div>

        <div id="question-block-list" class="space-y-4">
            <div class="question-block rounded-2xl border border-cyan-200 bg-white p-4" data-index="0">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h4 class="font-bold text-slate-800">Question 1</h4>
                    <button type="button" class="remove-question hidden text-sm text-red-600 hover:underline">Remove</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Question</label>
                        <textarea name="questions[0][question_text]" rows="4" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Type the question here..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Marks</label>
                        <input type="number" name="questions[0][marks]" value="1" min="1" step="1" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div class="question-type-wrapper">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Question Type</label>
                        <select name="questions[0][question_type]" class="question-type-select w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                            <option value="objective">Objective / MCQ</option>
                            <option value="theory">Theory / Written</option>
                        </select>
                    </div>

                    <div class="objective-block space-y-3 {{ old('assessment_format', $learningSession->assessment_format ?? request('assessment_format', 'objective')) === 'theory' ? 'hidden' : '' }}">
                        <label class="block text-sm font-semibold text-gray-700">Options</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="flex items-center gap-2">
                                <span class="w-8 font-bold text-slate-700">A.</span>
                                <input type="text" name="questions[0][options][A]" class="flex-1 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Option A">
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-8 font-bold text-slate-700">B.</span>
                                <input type="text" name="questions[0][options][B]" class="flex-1 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Option B">
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-8 font-bold text-slate-700">C.</span>
                                <input type="text" name="questions[0][options][C]" class="flex-1 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Option C">
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-8 font-bold text-slate-700">D.</span>
                                <input type="text" name="questions[0][options][D]" class="flex-1 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Option D">
                            </div>
                        </div>

                        <div class="question-type-wrapper">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Correct Answer</label>
                            <select name="questions[0][correct_option]" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                                <option value="">-- Select correct answer --</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                    </div>

                    <div class="theory-block hidden rounded-xl border border-violet-200 bg-violet-50 p-3 text-sm text-violet-700">
                        Theory answer box appears here once the task is set to theory format.
                    </div>
                </div>
            </div>
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
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <button type="submit" name="publish" value="0" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-lg font-bold">
                {{ $method === 'POST' ? 'Save as Draft' : 'Save Draft' }}
            </button>
            <button type="submit" name="publish" value="1" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-bold">
                Publish for Students
            </button>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const subjectSelect = document.getElementById('subject-id');
        const classPanel = document.getElementById('class-assignment-panel');
        const classCount = document.getElementById('class-assignment-count');
        const noClasses = document.getElementById('no-classes-for-subject');
        const subjectClassIds = @json($subjectClassIds);

        const syncClassOptions = () => {
            const allowedIds = (subjectClassIds[subjectSelect?.value] || []).map(String);
            const hasSubject = Boolean(subjectSelect?.value);
            classPanel?.classList.toggle('hidden', !hasSubject);
            noClasses?.classList.toggle('hidden', !hasSubject || allowedIds.length > 0);
            classCount.textContent = allowedIds.length ? `${allowedIds.length} class${allowedIds.length === 1 ? '' : 'es'} available` : '';

            document.querySelectorAll('#class-assignment-list [data-class-id]').forEach((option) => {
                const allowed = allowedIds.includes(option.dataset.classId);
                option.classList.toggle('hidden', !allowed);
                option.classList.toggle('flex', allowed);
                const checkbox = option.querySelector('input');
                if (!allowed && checkbox) checkbox.checked = false;
            });
        };

        subjectSelect?.addEventListener('change', syncClassOptions);
        syncClassOptions();

        const list = document.getElementById('question-block-list');
        const addButton = document.getElementById('add-question-block');
        const assessmentFormatSelect = document.getElementById('assessment-format-select');
        const assessmentTypeSelect = document.getElementById('assessment-type-select');
        const assessmentTypeLabel = document.getElementById('assessment-type-label');
        const assessmentFormatLabel = document.getElementById('assessment-format-label');

        const syncAssessmentLabels = () => {
            if (assessmentTypeLabel && assessmentTypeSelect) {
                assessmentTypeLabel.textContent = assessmentTypeSelect.value.charAt(0).toUpperCase() + assessmentTypeSelect.value.slice(1);
            }
            if (assessmentFormatLabel && assessmentFormatSelect) {
                assessmentFormatLabel.textContent = assessmentFormatSelect.value.charAt(0).toUpperCase() + assessmentFormatSelect.value.slice(1);
            }
        };

        assessmentTypeSelect?.addEventListener('change', syncAssessmentLabels);

        if (list && addButton) {
            const makeQuestionBlock = (index) => {
                const block = document.createElement('div');
                block.className = 'question-block rounded-2xl border border-cyan-200 bg-white p-4';
                block.dataset.index = String(index);

                block.innerHTML = `
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h4 class="font-bold text-slate-800">Question ${index + 1}</h4>
                        <button type="button" class="remove-question text-sm text-red-600 hover:underline">Remove</button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Question</label>
                            <textarea name="questions[${index}][question_text]" rows="4" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Type the question here..."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Marks</label>
                            <input type="number" name="questions[${index}][marks]" value="1" min="1" step="1" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                        </div>
                        <div class="question-type-wrapper">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Question Type</label>
                            <select name="questions[${index}][question_type]" class="question-type-select w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                                <option value="objective">Objective / MCQ</option>
                                <option value="theory">Theory / Written</option>
                            </select>
                        </div>
                        <div class="objective-block space-y-3">
                            <label class="block text-sm font-semibold text-gray-700">Options</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="flex items-center gap-2"><span class="w-8 font-bold text-slate-700">A.</span><input type="text" name="questions[${index}][options][A]" class="flex-1 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Option A"></div>
                                <div class="flex items-center gap-2"><span class="w-8 font-bold text-slate-700">B.</span><input type="text" name="questions[${index}][options][B]" class="flex-1 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Option B"></div>
                                <div class="flex items-center gap-2"><span class="w-8 font-bold text-slate-700">C.</span><input type="text" name="questions[${index}][options][C]" class="flex-1 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Option C"></div>
                                <div class="flex items-center gap-2"><span class="w-8 font-bold text-slate-700">D.</span><input type="text" name="questions[${index}][options][D]" class="flex-1 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Option D"></div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Correct Answer</label>
                                <select name="questions[${index}][correct_option]" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-cyan-500">
                                    <option value="">-- Select correct answer --</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                </select>
                            </div>
                        </div>
                        <div class="theory-block hidden rounded-xl border border-violet-200 bg-violet-50 p-3 text-sm text-violet-700">
                            Theory answer box appears here once the task is set to theory format.
                        </div>
                    </div>
                `;

                const typeSelect = block.querySelector('.question-type-select');
                const objectiveBlock = block.querySelector('.objective-block');
                const theoryBlock = block.querySelector('.theory-block');
                const removeBtn = block.querySelector('.remove-question');

                const syncType = () => {
                    const isTheory = typeSelect.value === 'theory';
                    objectiveBlock.classList.toggle('hidden', isTheory);
                    theoryBlock.classList.add('hidden');
                };

                typeSelect.addEventListener('change', syncType);
                removeBtn.addEventListener('click', () => {
                    block.remove();
                    const blocks = list.querySelectorAll('.question-block');
                    blocks.forEach((item, i) => {
                        item.dataset.index = String(i);
                        const heading = item.querySelector('h4');
                        if (heading) heading.textContent = `Question ${i + 1}`;
                        item.querySelectorAll('textarea, input, select').forEach((field) => {
                            const oldName = field.getAttribute('name');
                            if (!oldName) return;
                            const updatedName = oldName.replace(/questions\[\d+\]/, `questions[${i}]`);
                            field.setAttribute('name', updatedName);
                        });
                    });
                    if (blocks.length <= 1) {
                        list.querySelectorAll('.remove-question').forEach((btn) => btn.classList.add('hidden'));
                    }
                });

                syncType();
                return block;
            };

            const syncAssessmentFormat = () => {
                const format = assessmentFormatSelect?.value || 'objective';
                list.querySelectorAll('.question-block').forEach((block) => {
                    const typeSelect = block.querySelector('.question-type-select');
                    const typeWrapper = block.querySelector('.question-type-wrapper');
                    const objectiveBlock = block.querySelector('.objective-block');

                    if (!typeSelect || !objectiveBlock) return;

                    const isMixed = format === 'mixed';
                    typeWrapper?.classList.toggle('hidden', !isMixed);

                    if (!isMixed) {
                        typeSelect.value = format;
                    }

                    objectiveBlock.classList.toggle('hidden', typeSelect.value === 'theory');
                });
            };

            assessmentFormatSelect?.addEventListener('change', syncAssessmentFormat);
            assessmentFormatSelect?.addEventListener('change', syncAssessmentLabels);
            syncAssessmentFormat();
            syncAssessmentLabels();

            addButton.addEventListener('click', () => {
                const blocks = list.querySelectorAll('.question-block');
                const nextIndex = blocks.length;
                const block = makeQuestionBlock(nextIndex);
                list.appendChild(block);
                syncAssessmentFormat();
                if (blocks.length >= 1) {
                    list.querySelectorAll('.remove-question').forEach((btn) => btn.classList.remove('hidden'));
                }
            });

            list.querySelectorAll('.remove-question').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const block = btn.closest('.question-block');
                    if (!block) return;
                    block.remove();
                    const blocks = list.querySelectorAll('.question-block');
                    blocks.forEach((item, i) => {
                        item.dataset.index = String(i);
                        const heading = item.querySelector('h4');
                        if (heading) heading.textContent = `Question ${i + 1}`;
                        item.querySelectorAll('textarea, input, select').forEach((field) => {
                            const oldName = field.getAttribute('name');
                            if (!oldName) return;
                            const updatedName = oldName.replace(/questions\[\d+\]/, `questions[${i}]`);
                            field.setAttribute('name', updatedName);
                        });
                    });
                    if (blocks.length <= 1) {
                        list.querySelectorAll('.remove-question').forEach((btn) => btn.classList.add('hidden'));
                    }
                });
            });
        }
    });
</script>
