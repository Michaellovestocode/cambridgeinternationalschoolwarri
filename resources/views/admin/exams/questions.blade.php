@extends('layouts.app')

@section('title', 'Manage Questions')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ session('error') }}</div>
        @endif
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $exam->title }}</h2>
                <p class="text-gray-600">{{ $exam->subject }} - {{ $exam->duration_minutes }} minutes</p>
            </div>
            <a href="{{ route('admin.exams') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                Back to Exams
            </a>
        </div>
        <div class="grid grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-600">Total Questions:</span>
                <p class="font-semibold text-lg">{{ $exam->questions->count() }}</p>
            </div>
            <div>
                <span class="text-gray-600">Total Marks:</span>
                <p class="font-semibold text-lg">{{ $exam->questions->sum('marks') }} / {{ $exam->total_marks }}</p>
            </div>
            <div>
                <span class="text-gray-600">Status:</span>
                <p class="font-semibold text-lg">
                    @if($exam->questions->sum('marks') == $exam->total_marks)
                    <span class="text-green-600">✓ Complete</span>
                    @else
                    <span class="text-yellow-600">⚠ Incomplete</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6" x-data="{ open: {{ old('body') ? 'true' : 'false' }} }">
        <button type="button"
                @click="open = !open"
                class="w-full flex items-center justify-between text-left">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Passages / Stories</h3>
                <p class="text-sm text-gray-500 mt-1">Use this only for comprehension passages, stories, poems, or extracts with follow-up questions.</p>
            </div>
            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm font-semibold">
                <span x-show="!open">Open</span>
                <span x-show="open">Hide</span>
            </span>
        </button>

        <div x-show="open" class="mt-5">
        <form action="{{ route('admin.exam.passage.store', $exam->id) }}" method="POST" class="space-y-4 mb-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Passage Title <span class="text-gray-500">(Optional)</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="Example: Passage A, Comprehension Story, Section B">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Passage Text *</label>
                @include('admin.exams.partials.rich-question-editor', [
                    'name' => 'body',
                    'value' => old('body'),
                    'placeholder' => 'Paste the story, poem, extract, or comprehension passage here'
                ])
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                Add Passage
            </button>
        </form>

        @if($exam->passages->count() > 0)
        <div class="space-y-4">
            @foreach($exam->passages as $passage)
            <div class="border rounded-lg p-4 bg-gray-50">
                <form action="{{ route('admin.exam.passage.update', $passage->id) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <input type="text" name="title" value="{{ old('title', $passage->title) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                           placeholder="Passage title">
                    @include('admin.exams.partials.rich-question-editor', [
                        'name' => 'body',
                        'value' => old('body', $passage->body),
                        'placeholder' => 'Passage text'
                    ])
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">
                        Save Passage
                    </button>
                </form>
                <form action="{{ route('admin.exam.passage.delete', $passage->id) }}" method="POST"
                      onsubmit="return confirm('Delete this passage? Its questions will become standalone.')" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg font-semibold text-sm">
                        Delete Passage
                    </button>
                </form>
                <p class="text-xs text-gray-500 mt-3">{{ $passage->questions->count() }} attached {{ Str::plural('question', $passage->questions->count()) }}</p>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-500">No passages yet. Add one when a story or comprehension text has multiple follow-up questions.</p>
        @endif
        </div>
    </div>

    @if($exam->questions->sum('marks') < $exam->total_marks)
    <section class="bg-white rounded-lg shadow p-6" id="bulk-objective-import">
        <div class="mb-4">
            <h3 class="text-xl font-bold text-gray-800">Paste objective questions in bulk</h3>
            <p class="mt-1 text-sm text-gray-600">Paste the numbered questions as they are, including A–D choices and “Answer A”. The importer arranges them into editable questions before saving.</p>
        </div>

        @if($errors->has('questions'))
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-bold">The questions were not saved.</p>
                @foreach($errors->get('questions') as $error)<p class="mt-1">{{ $error }}</p>@endforeach
            </div>
        @endif

        <form id="bulk-objective-form" action="{{ route('admin.exam.questions.bulk-import', $exam->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="objective-source" class="mb-2 block text-sm font-semibold text-gray-700">Paste the full question set</label>
                <textarea id="objective-source" name="source_text" rows="10" maxlength="200000" required
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 font-mono text-sm focus:border-green-500 focus:ring-green-500"
                    placeholder="1. Question text? A. First choice B. Second choice C. Third choice D. Fourth choice. Answer B&#10;2. Next question? (A) First (B) Second (C) Third (D) Fourth. Answer A">{{ old('source_text') }}</textarea>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="w-full sm:w-48">
                    <label for="bulk-default-marks" class="mb-1 block text-sm font-semibold text-gray-700">Default marks per question</label>
                    <input id="bulk-default-marks" type="number" min="0.01" max="1000" step="0.01" value="1" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                </div>
                <button id="parse-objective-questions" type="button" class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700">Arrange questions</button>
                <p class="text-xs text-gray-500">Exam marks remaining: {{ rtrim(rtrim(number_format((float) $exam->total_marks - (float) $exam->questions->sum('marks'), 2, '.', ''), '0'), '.') }}</p>
            </div>

            <div id="objective-parse-status" aria-live="polite" class="hidden rounded-lg p-4 text-sm"></div>
            <div id="objective-question-preview" class="space-y-3"></div>

            <button id="save-bulk-objective-questions" type="submit" disabled class="hidden rounded-lg bg-green-600 px-6 py-3 font-bold text-white enabled:hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50">
                Save all objective questions
            </button>
        </form>
    </section>

    <script>
    (() => {
        const form = document.getElementById('bulk-objective-form');
        if (!form) return;

        const source = document.getElementById('objective-source');
        const parseButton = document.getElementById('parse-objective-questions');
        const defaultMarks = document.getElementById('bulk-default-marks');
        const preview = document.getElementById('objective-question-preview');
        const status = document.getElementById('objective-parse-status');
        const saveButton = document.getElementById('save-bulk-objective-questions');
        const restoredQuestions = @json(old('questions', []));
        let readyToSave = false;

        const setStatus = (message, isError = false) => {
            status.textContent = message;
            status.className = `rounded-lg p-4 text-sm ${isError ? 'border border-red-300 bg-red-50 text-red-800' : 'border border-emerald-300 bg-emerald-50 text-emerald-800'}`;
        };

        const makeInput = (label, name, value, type = 'text') => {
            const wrap = document.createElement('label');
            wrap.className = 'block min-w-0';
            const caption = document.createElement('span');
            caption.className = 'mb-1 block text-xs font-bold text-gray-600';
            caption.textContent = label;
            const input = document.createElement(type === 'textarea' ? 'textarea' : 'input');
            input.name = name;
            input.value = value ?? '';
            input.required = true;
            input.className = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:ring-green-500';
            if (type === 'number') {
                input.type = 'number';
                input.min = '0.01';
                input.max = '1000';
                input.step = '0.01';
            }
            if (type === 'textarea') {
                input.rows = 2;
            }
            wrap.append(caption, input);
            return wrap;
        };

        const renderQuestions = (questions, issues = []) => {
            preview.replaceChildren();
            saveButton.disabled = true;
            saveButton.classList.add('hidden');
            readyToSave = false;

            questions.forEach((question, index) => {
                const card = document.createElement('fieldset');
                card.className = 'rounded-xl border border-gray-200 bg-gray-50 p-4';
                const legend = document.createElement('legend');
                legend.className = 'px-2 font-bold text-gray-800';
                legend.textContent = `Question ${index + 1}`;
                card.appendChild(legend);

                const questionInput = makeInput('Question', `questions[${index}][question_text]`, question.question_text, 'textarea');
                card.appendChild(questionInput);

                const options = document.createElement('div');
                options.className = 'mt-3 grid gap-3 md:grid-cols-2';
                ['A', 'B', 'C', 'D'].forEach((letter) => {
                    options.appendChild(makeInput(`Option ${letter}`, `questions[${index}][options][${letter}]`, question.options?.[letter]));
                });
                card.appendChild(options);

                const controls = document.createElement('div');
                controls.className = 'mt-3 grid gap-3 sm:grid-cols-[1fr,180px]';
                const answerLabel = document.createElement('label');
                answerLabel.className = 'block';
                const answerCaption = document.createElement('span');
                answerCaption.className = 'mb-1 block text-xs font-bold text-gray-600';
                answerCaption.textContent = 'Supplied correct answer — please verify';
                const answer = document.createElement('select');
                answer.name = `questions[${index}][correct_answer]`;
                answer.required = true;
                answer.className = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm';
                ['A', 'B', 'C', 'D'].forEach((letter) => {
                    const option = document.createElement('option');
                    option.value = letter;
                    option.textContent = letter;
                    option.selected = letter === question.correct_answer;
                    answer.appendChild(option);
                });
                answerLabel.append(answerCaption, answer);
                controls.append(answerLabel, makeInput('Marks', `questions[${index}][marks]`, question.marks ?? defaultMarks.value, 'number'));
                card.appendChild(controls);
                preview.appendChild(card);
            });

            if (issues.length) {
                const list = document.createElement('ul');
                list.className = 'mt-2 list-inside list-disc';
                issues.forEach((issue) => {
                    const item = document.createElement('li');
                    item.textContent = issue;
                    list.appendChild(item);
                });
                status.replaceChildren();
                const heading = document.createElement('p');
                heading.textContent = `Parsed ${questions.length} question(s), but ${issues.length} source line(s) need attention. Nothing can be saved until all lines are arranged.`;
                status.append(heading, list);
                status.className = 'rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900';
                return;
            }

            if (!questions.length) {
                setStatus('No numbered questions were found. Start each question with a number such as “1.”.', true);
                return;
            }

            readyToSave = true;
            saveButton.disabled = false;
            saveButton.classList.remove('hidden');
            setStatus(`${questions.length} question(s) arranged. Review every question and supplied answer before saving.`);
        };

        const parseSource = () => {
            const text = source.value.replace(/\u00a0/g, ' ');
            const starts = [...text.matchAll(/^[ \t]*(\d+)[.)][ \t]*/gm)];
            const issues = [];
            const questions = [];

            if (!starts.length) {
                renderQuestions([], ['No numbered questions found.']);
                return;
            }

            const preamble = text.slice(0, starts[0].index).trim();
            if (preamble) issues.push('Text before question 1 could not be assigned to a question.');

            starts.forEach((start, index) => {
                const end = index + 1 < starts.length ? starts[index + 1].index : text.length;
                const number = start[1];
                let body = text.slice(start.index + start[0].length, end).replace(/\s+/g, ' ').trim();
                const answerMatch = body.match(/\bAnswer\s*[:\-]?\s*\(?\s*([A-D])\s*\)?[.!]?\s*$/i);
                if (!answerMatch) {
                    issues.push(`Question ${number}: could not find an ending “Answer A”, “Answer B”, “Answer C”, or “Answer D”.`);
                    return;
                }

                const answer = answerMatch[1].toUpperCase();
                body = body.slice(0, answerMatch.index).trim();
                const markerPattern = /\(\s*([A-D])\s*\)|\s+([A-D])(?:\s*\.)?(?=\s)/g;
                const markers = [...body.matchAll(markerPattern)].map((match) => ({
                    letter: (match[1] || match[2]).toUpperCase(),
                    index: match.index,
                    end: match.index + match[0].length,
                }));

                let optionStart = -1;
                for (let candidate = 0; candidate <= markers.length - 4; candidate++) {
                    if (markers.slice(candidate, candidate + 4).map((marker) => marker.letter).join('') === 'ABCD') {
                        optionStart = candidate;
                        break;
                    }
                }
                if (optionStart < 0) {
                    issues.push(`Question ${number}: expected four choices labelled A, B, C, and D.`);
                    return;
                }

                const choices = markers.slice(optionStart, optionStart + 4);
                const questionText = body.slice(0, choices[0].index).replace(/^\s*[.\s]+/, '').trim();
                const options = {};
                choices.forEach((choice, choiceIndex) => {
                    const nextIndex = choiceIndex < 3 ? choices[choiceIndex + 1].index : body.length;
                    const value = body.slice(choice.end, nextIndex).replace(/^[\s.]+|[\s.]+$/g, '').trim();
                    options[choice.letter] = value;
                });

                if (!questionText || ['A', 'B', 'C', 'D'].some((letter) => !options[letter])) {
                    issues.push(`Question ${number}: the question text or one of its four choices is empty.`);
                    return;
                }

                questions.push({
                    question_text: questionText,
                    options,
                    correct_answer: answer,
                    marks: defaultMarks.value || 1,
                });
            });

            renderQuestions(questions, issues);
        };

        parseButton.addEventListener('click', parseSource);
        form.addEventListener('submit', (event) => {
            if (!readyToSave) {
                event.preventDefault();
                setStatus('Arrange and review the questions before saving.', true);
            }
        });

        if (Array.isArray(restoredQuestions) && restoredQuestions.length) {
            renderQuestions(restoredQuestions);
        }
    })();
    </script>
    @endif

    <!-- Add Question Form -->
    @if($exam->questions->sum('marks') < $exam->total_marks)
    <div class="bg-white rounded-lg shadow p-6" x-data="{ questionType: 'multiple_choice' }">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Add New Question</h3>

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

       <form action="{{ route('admin.exam.question.store', $exam->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Attach to Passage <span class="text-gray-500">(Optional)</span></label>
        <select name="question_passage_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            <option value="">Standalone question</option>
            @foreach($exam->passages as $passage)
            <option value="{{ $passage->id }}" {{ old('question_passage_id') == $passage->id ? 'selected' : '' }}>
                {{ $passage->title ?: 'Passage ' . $loop->iteration }}
            </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Question Type *</label>
        <select name="question_type" x-model="questionType"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            <option value="multiple_choice">Multiple Choice (A/B/C/D)</option>
            <option value="theory">Theory/Essay</option>
            <option value="coding">Coding</option>
            <option value="fill_blank">Fill in the Blank</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Question Text *</label>
        @include('admin.exams.partials.rich-question-editor', ['value' => old('question_text')])
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Marks *</label>
        <input type="number" name="marks" value="{{ old('marks', 1) }}" min="0.01" step="0.01" required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
    </div>
    <!-- Image Upload (for all question types, especially coding/theory) -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Reference Image <span class="text-gray-500">(Optional)</span>
    </label>
    <div x-data="{ preview: null }" class="space-y-2">
        <input type="file" 
               name="image" 
               accept="image/*"
               @change="
                   const file = $event.target.files[0];
                   if (file) {
                       const reader = new FileReader();
                       reader.onload = (e) => { preview = e.target.result };
                       reader.readAsDataURL(file);
                   }
               "
               class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500">
        <p class="text-xs text-gray-500">Upload an image showing what students should design or reference. Max 5MB (JPG, PNG, GIF, WEBP)</p>

        <!-- Image Preview -->
        <div x-show="preview" class="mt-3 border rounded-lg overflow-hidden">
            <div class="bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 flex justify-between items-center">
                <span>Preview</span>
                <button type="button" 
                        @click="preview = null; $event.target.closest('div').querySelector('input[type=file]').value = ''"
                        class="text-red-500 hover:text-red-700">Remove</button>
            </div>
            <img :src="preview" alt="Preview" class="max-h-48 w-full object-contain bg-white p-2">
        </div>
    </div>
</div>

    <!-- Multiple Choice Options -->
    <div x-show="questionType === 'multiple_choice'" class="space-y-3">
        <label class="block text-sm font-medium text-gray-700">Options *</label>
        
        <div class="space-y-2">
            <div class="flex gap-2">
                <span class="font-bold text-gray-700 w-8">A.</span>
                <input type="text" name="options[A]" value="{{ old('options.A') }}"
                       :required="questionType === 'multiple_choice'"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Option A">
            </div>
            <div class="flex gap-2">
                <span class="font-bold text-gray-700 w-8">B.</span>
                <input type="text" name="options[B]" value="{{ old('options.B') }}"
                       :required="questionType === 'multiple_choice'"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Option B">
            </div>
            <div class="flex gap-2">
                <span class="font-bold text-gray-700 w-8">C.</span>
                <input type="text" name="options[C]" value="{{ old('options.C') }}"
                       :required="questionType === 'multiple_choice'"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Option C">
            </div>
            <div class="flex gap-2">
                <span class="font-bold text-gray-700 w-8">D.</span>
                <input type="text" name="options[D]" value="{{ old('options.D') }}"
                       :required="questionType === 'multiple_choice'"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Option D">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answer *</label>
            <select name="correct_answer"
                    :required="questionType === 'multiple_choice'"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">-- Select Correct Answer --</option>
                <option value="A" {{ old('correct_answer') == 'A' ? 'selected' : '' }}>A</option>
                <option value="B" {{ old('correct_answer') == 'B' ? 'selected' : '' }}>B</option>
                <option value="C" {{ old('correct_answer') == 'C' ? 'selected' : '' }}>C</option>
                <option value="D" {{ old('correct_answer') == 'D' ? 'selected' : '' }}>D</option>
            </select>
        </div>
    </div>

    <!-- Fill in the Blank Answer -->
    <template x-if="questionType === 'fill_blank'">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answer *</label>
            <input type="text" name="correct_answer" value="{{ old('correct_answer') }}"
                   required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                   placeholder="Enter the correct answer (case-insensitive)">
        </div>
    </template>

    <button type="submit" 
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
        Add Question
    </button>
</form>
    </div>
    @endif

    <!-- Questions List -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-800">Questions ({{ $exam->questions->count() }})</h3>
        </div>

        @php $lastPassageId = null; @endphp
        @forelse($exam->questions->sortBy('order') as $index => $question)
        @if($question->passage && $lastPassageId !== $question->question_passage_id)
        <div class="border-b p-6 bg-blue-50">
            <div class="text-xs font-bold uppercase text-blue-700 mb-2">{{ $question->passage->title ?: 'Passage' }}</div>
            <div class="prose max-w-none text-gray-800">{!! $question->passage->formattedBody() !!}</div>
        </div>
        @php $lastPassageId = $question->question_passage_id; @endphp
        @elseif(!$question->passage)
        @php $lastPassageId = null; @endphp
        @endif
        <div class="border-b p-6">
            <div class="flex justify-between items-start mb-3">
                <div class="flex items-start flex-1">
                    <span class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-semibold mr-3 flex-shrink-0">
                        {{ $index + 1 }}
                    </span>
                    <div class="flex-1">
                        <div class="text-gray-800 font-medium mb-2">{!! $question->formattedQuestionText() !!}</div>
                        <div class="flex gap-2 text-xs">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                {{ ucwords(str_replace('_', ' ', $question->question_type)) }}
                            </span>
                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">
                                {{ number_format((float) $question->marks, 2) }} {{ (float) $question->marks == 1.0 ? 'mark' : 'marks' }}
                            </span>
                            @if($question->passage)
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                {{ $question->passage->title ?: 'Passage' }}
                            </span>
                            @endif
                        </div>

                        @if($question->question_type === 'multiple_choice' && $question->options)
                        <div class="mt-3 space-y-1">
                            @foreach($question->options as $key => $option)
                            <p class="text-sm {{ $key === $question->correct_answer ? 'text-green-600 font-semibold' : 'text-gray-600' }}">
                                {{ $key }}. {{ $option }}
                                @if($key === $question->correct_answer)
                                <span class="text-xs">(Correct Answer)</span>
                                @endif
                            </p>
                            @endforeach
                        </div>
                        @endif

                        @if($question->question_type === 'fill_blank')
                        <p class="text-sm text-green-600 font-semibold mt-2">
                            Answer: {{ $question->correct_answer }}
                        </p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3 ml-4">
                    <a href="{{ route('admin.question.edit', $question->id) }}"
                       class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                        Edit
                    </a>

                    <form action="{{ route('admin.question.delete', $question->id) }}" method="POST"
                          onsubmit="return confirm('Delete this question?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="p-12 text-center">
            <div class="text-gray-400 text-6xl mb-4">❓</div>
            <p class="text-gray-600 text-lg">No questions added yet</p>
            <p class="text-gray-500 text-sm">Use the form above to add your first question</p>
        </div>
        @endforelse
    </div>

    @if($exam->questions->count() > 0)
    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
        <div class="flex justify-between items-center">
            <div>
                <h4 class="font-semibold text-green-800">Exam Ready!</h4>
                <p class="text-sm text-green-600">You have {{ $exam->questions->count() }} questions totaling {{ $exam->questions->sum('marks') }} marks</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.exam.results', $exam->id) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                    View Results
                </a>
                <a href="{{ route('admin.exams') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                    Done
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
