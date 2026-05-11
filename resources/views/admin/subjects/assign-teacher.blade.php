@extends('layouts.app')

@section('title', 'Assign Subjects to ' . $teacher->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">📚 Assign Subjects</h2>
            <p class="text-gray-600 mt-2">Teacher: <strong>{{ $teacher->name }}</strong></p>
            <p class="text-gray-600">Email: {{ $teacher->email }}</p>
        </div>

        <form action="{{ route('admin.subjects.update-subjects', $teacher->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Select Subjects *</label>
                <p class="text-xs text-gray-600 mb-3">The teacher can create exams only for selected subjects</p>
                
                <div class="border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto">
                    @forelse($subjects as $subject)
                    <div class="flex items-center mb-3">
                        <input type="checkbox" name="subjects[]" value="{{ $subject->id }}"
                               {{ $teacher->subjects->contains($subject->id) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <label class="ml-3 text-sm text-gray-700">
                            <div class="font-medium">{{ $subject->name }}</div>
                            <div class="text-xs text-gray-600">
                                @if($subject->code)Code: {{ $subject->code }}@endif
                                @if($subject->description) • {{ Str::limit($subject->description, 40) }}@endif
                            </div>
                        </label>
                    </div>
                    @empty
                    <p class="text-gray-500">No subjects found.</p>
                    @endforelse
                </div>
                @error('subjects')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <strong>Note:</strong> Teachers can only create exams for their assigned subjects. This helps organize the curriculum and prevents unauthorized exam creation.
                </p>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold">
                    Assign Subjects
                </button>
                <a href="{{ route('admin.teachers') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
