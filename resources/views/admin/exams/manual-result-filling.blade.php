@extends('layouts.app')

@section('title', 'Manual Result Filling')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="rounded-3xl bg-gradient-to-r from-amber-500 to-orange-600 p-6 text-white shadow-xl">
        <p class="text-xs font-black uppercase tracking-[0.18em] text-white/75">Teacher Shortcut</p>
        <h1 class="mt-2 text-3xl font-black">Manual Result Filling</h1>
        <p class="mt-2 text-sm leading-6 text-white/85">
            Select the subject and class you teach, then continue straight to the score sheet.
        </p>
    </div>

    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.manual-result-filling.start') }}" class="rounded-3xl bg-white p-6 shadow-lg space-y-5">
        @csrf

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="subject_id" class="mb-2 block text-sm font-bold text-gray-700">Subject</label>
                <select id="subject_id" name="subject_id" required class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100">
                    <option value="">Select subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string) old('subject_id') === (string) $subject->id)>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="class_id" class="mb-2 block text-sm font-bold text-gray-700">Class</label>
                <select id="class_id" name="class_id" required class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100">
                    <option value="">Select subject first</option>
                </select>
            </div>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm leading-6 text-blue-900">
            Scores will be saved for {{ $activeSession?->name ?? 'the active session' }} and {{ $activeTerm?->name ?? 'the active term' }}.
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.dashboard') }}" class="rounded-2xl border border-gray-200 px-5 py-3 text-center text-sm font-black text-gray-700 hover:bg-gray-50">
                Back
            </a>
            <button type="submit" class="rounded-2xl bg-amber-600 px-6 py-3 text-sm font-black text-white shadow-lg hover:bg-amber-700">
                Continue to Score Sheet
            </button>
        </div>
    </form>
</div>

<script>
const classesBySubject = @json($classesBySubject);
const subjectSelect = document.getElementById('subject_id');
const classSelect = document.getElementById('class_id');
const oldClassId = @json((string) old('class_id'));

function renderClasses() {
    const subjectId = subjectSelect.value;
    const classes = classesBySubject[subjectId] || [];
    classSelect.innerHTML = '';

    if (!classes.length) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = subjectId ? 'No class assigned for this subject' : 'Select subject first';
        classSelect.appendChild(option);
        return;
    }

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Select class';
    classSelect.appendChild(placeholder);

    classes.forEach((schoolClass) => {
        const option = document.createElement('option');
        option.value = schoolClass.id;
        option.textContent = schoolClass.display_name;
        option.selected = oldClassId && oldClassId === String(schoolClass.id);
        classSelect.appendChild(option);
    });
}

subjectSelect.addEventListener('change', renderClasses);
renderClasses();
</script>
@endsection
