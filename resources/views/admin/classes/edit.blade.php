@extends('layouts.app')

@section('title', 'Edit Class')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <div class="mb-6">
            <h1 class="text-3xl font-black text-gray-900">Edit Class</h1>
            <p class="text-gray-500 mt-1">Update the class details and the subjects offered by learners in this class.</p>
        </div>

        <form action="{{ route('admin.class.update', $class) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Class Name *</label>
                <input type="text" name="name" value="{{ old('name', $class->name) }}" required
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-purple-500 transition"
                       placeholder="e.g., Year 11">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Description</label>
                <input type="text" name="description" value="{{ old('description', $class->description) }}"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-purple-500 transition"
                       placeholder="e.g., Science">
                @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <div class="flex flex-wrap items-end justify-between gap-3 mb-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Subjects Offered</label>
                        <p class="text-sm text-gray-500 mt-1">These subjects control which classes appear when a teacher creates CBT tests or exams.</p>
                    </div>
                    <span class="text-xs font-bold uppercase text-gray-400">{{ $subjects->count() }} active subjects</span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 max-h-[32rem] overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-4">
                    @forelse($subjects as $subject)
                        <label class="flex items-start gap-3 rounded-lg border border-gray-100 bg-white p-3 hover:border-purple-300">
                            <input type="checkbox" name="subjects[]" value="{{ $subject->id }}"
                                   {{ in_array($subject->id, old('subjects', $class->subjects->pluck('id')->toArray())) ? 'checked' : '' }}
                                   class="mt-1 rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200">
                            <span>
                                <span class="block text-sm font-bold text-gray-800">{{ $subject->name }}</span>
                                <span class="block text-xs text-gray-500">
                                    {{ $subject->code ?: 'No code' }}
                                    @if($subject->class_level)
                                        - {{ ucfirst(str_replace('_', ' ', $subject->class_level)) }}
                                    @endif
                                </span>
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500">No active subjects found.</p>
                    @endforelse
                </div>
                @error('subjects')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-xl font-bold">Save Changes</button>
                <a href="{{ route('admin.classes') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-xl font-bold">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
