@extends('layouts.app')

@section('title', 'Assign Classes to ' . $teacher->name)

@section('content')
<div class="max-w-4xl mx-auto">
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
                <label class="block text-sm font-medium text-gray-700 mb-4">Select Classes This Teacher Teaches</label>
                <p class="text-xs text-gray-600 mb-3">These classes will appear when the teacher creates an exam.</p>

                <div class="border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto">
                    @forelse($classes as $class)
                    <label class="flex items-center mb-3">
                        <input type="checkbox" name="classes[]" value="{{ $class->id }}"
                               {{ in_array($class->id, old('classes', $teacher->teachingClasses->pluck('id')->toArray())) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <span class="ml-3 text-sm text-gray-700">
                            <span class="font-medium">{{ $class->display_name }}</span>
                            @if($class->description)
                                <span class="text-xs text-gray-600 block">{{ $class->description }}</span>
                            @endif
                        </span>
                    </label>
                    @empty
                    <p class="text-gray-500">No classes found.</p>
                    @endforelse
                </div>
                @error('classes')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    This is separate from form teacher assignment. A teacher can be a form teacher for one class and teach different classes.
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
@endsection
