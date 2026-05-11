@extends('layouts.app')

@section('title', 'Assign Form Teacher')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Assign Form Teacher to Class</h2>

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.form-teachers.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Class *</label>
                <select name="class_id" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        required>
                    <option value="">-- Choose a Class --</option>
                    @foreach($unassignedClasses as $class)
                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->display_name }}
                    </option>
                    @endforeach
                </select>
                @error('class_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @if($unassignedClasses->isEmpty())
                <p class="text-yellow-600 text-sm mt-2">⚠️ All classes already have form teachers assigned.</p>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Teacher *</label>
                <select name="teacher_id" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        required>
                    <option value="">-- Choose a Teacher --</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->name }} ({{ $teacher->email }})
                    </option>
                    @endforeach
                </select>
                @error('teacher_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @if($teachers->isEmpty())
                <p class="text-yellow-600 text-sm mt-2">⚠️ No teachers found in the system.</p>
                @endif
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-blue-800 text-sm">
                    <strong>ℹ️ Note:</strong> The form teacher will have access to class results, attendance, and student records through their dashboard.
                </p>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold"
                        {{ $unassignedClasses->isEmpty() ? 'disabled' : '' }}>
                    Assign Form Teacher
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
