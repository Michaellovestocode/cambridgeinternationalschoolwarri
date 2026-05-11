@extends('layouts.app')

@section('title', 'Edit Form Teacher')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Form Teacher Assignment</h2>
            <a href="{{ route('admin.form-teachers.index') }}" class="text-gray-600 hover:text-gray-800">
                ← Back
            </a>
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

        <form action="{{ route('admin.form-teachers.update', $formTeacher->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Class (Read-Only)</label>
                <input type="text" 
                       value="{{ $formTeacher->schoolClass->display_name }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100"
                       disabled>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select New Teacher *</label>
                <select name="teacher_id" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        required>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" 
                            {{ old('teacher_id', $formTeacher->teacher_id) == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->name }} ({{ $teacher->email }})
                    </option>
                    @endforeach
                </select>
                @error('teacher_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="border-t pt-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ old('is_active', $formTeacher->is_active) ? 'checked' : '' }}
                           class="mr-2 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <span class="font-medium">Form Teacher is Active</span>
                </label>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
                    Update Assignment
                </button>
                <a href="{{ route('admin.form-teachers.index') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
