@extends('layouts.app')

@section('title', 'Assign Classes to ' . $teacher->name)

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Assign Classes</h2>
            <p class="text-gray-600 mt-2">Teacher: <strong>{{ $teacher->name }}</strong></p>
            <p class="text-gray-600">Email: {{ $teacher->email }}</p>
        </div>

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.teacher.update-classes', $teacher->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Select Classes and Exact Subjects This Teacher Teaches</label>
                <p class="text-xs text-gray-600 mb-3">Tick a class, then tick only the subjects this teacher handles in that class. CBT tests and exams will use these exact pairings.</p>

                @if($subjects->isEmpty())
                    <div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm font-semibold text-yellow-800">
                        Assign subjects to this teacher first, then return here to map those subjects to classes.
                    </div>
                @endif

                <div class="grid gap-4">
                    @forelse($classes as $class)
                        @php
                            $classSelected = in_array($class->id, old('classes', $teacher->teachingClasses->pluck('id')->toArray()));
                            $selectedSubjects = collect(old("teaching_load.{$class->id}", $teachingLoad[$class->id] ?? []))
                                ->map(fn ($id) => (int) $id)
                                ->all();
                        @endphp
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4" data-class-card>
                            <label class="flex items-start gap-3">
                                <input type="checkbox" name="classes[]" value="{{ $class->id }}"
                                       {{ $classSelected ? 'checked' : '' }}
                                       class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       data-class-toggle>
                                <span class="text-sm text-gray-700">
                                    <span class="font-bold text-gray-900">{{ $class->display_name }}</span>
                                    @if($class->description)
                                        <span class="text-xs text-gray-600 block">{{ $class->description }}</span>
                                    @endif
                                </span>
                            </label>

                            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3" data-subject-list>
                                @forelse($subjects as $subject)
                                    <label class="flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-700 border border-gray-100">
                                        <input type="checkbox" name="teaching_load[{{ $class->id }}][]" value="{{ $subject->id }}"
                                               {{ in_array($subject->id, $selectedSubjects, true) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                               data-subject-checkbox>
                                        <span>{{ $subject->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-sm text-gray-500">No subjects assigned to this teacher.</p>
                                @endforelse
                            </div>
                        </div>
                    @empty
                    <p class="text-gray-500">No classes found.</p>
                    @endforelse
                </div>
                @error('classes')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    This is separate from form teacher assignment. A teacher can be a form teacher for one class and teach different subjects in different teaching classes.
                </p>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold">
                    Save Classes
                </button>
                <a href="{{ route('admin.teachers') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-class-card]').forEach(function (card) {
        const classToggle = card.querySelector('[data-class-toggle]');
        const subjectChecks = Array.from(card.querySelectorAll('[data-subject-checkbox]'));

        function syncSubjects() {
            subjectChecks.forEach(function (checkbox) {
                checkbox.disabled = !classToggle.checked;
                checkbox.closest('label').classList.toggle('opacity-50', !classToggle.checked);
            });
        }

        classToggle.addEventListener('change', syncSubjects);
        syncSubjects();
    });
});
</script>
@endsection
