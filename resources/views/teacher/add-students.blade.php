@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Add Students to {{ $class->name }}</h2>
        <p class="text-gray-600">Manage students in your assigned class</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <ul class="mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap -mx-4">
        <!-- Add New Student - Disabled -->
        <div class="w-full md:w-1/2 px-4 mb-6">
            <div class="bg-white rounded-lg shadow-md border-2 border-yellow-400">
                <div class="bg-yellow-100 text-yellow-800 px-4 py-3 rounded-t-lg">
                    <h5 class="text-lg font-semibold mb-0">⚠️ Student Management Unavailable</h5>
                </div>
                <div class="p-4">
                    <p class="text-yellow-700 font-medium mb-4">
                        The student management feature is temporarily unavailable. This functionality should be accessed through the Admin Dashboard.
                    </p>
                    <a href="{{ route('teacher.scores.dashboard') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Go to Score Entry
                    </a>
                </div>
            </div>
        </div>

        <!-- Original Form - Commented Out -->
        <!--
        <div class="w-full md:w-1/2 px-4 mb-6">
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-blue-600 text-white px-4 py-3 rounded-t-lg">
                    <h5 class="text-lg font-semibold mb-0">Add Student to {{ $class->name }}</h5>
                </div>
                <div class="p-4">
                    @if ($availableStudents->isEmpty())
                        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                            All available students have already been added to this class.
                        </div>
                    @else
                        <form action="{{ route('teacher.form-teacher.store-student', $class->id) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="student_id" class="block text-gray-700 text-sm font-bold mb-2">Select Student</label>
                                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('student_id') border-red-500 @enderror" 
                                    id="student_id" name="student_id" required>
                                    <option value="">-- Choose a student --</option>
                                    @foreach ($availableStudents as $student)
                                        <option value="{{ $student->id }}">
                                            {{ $student->name }} ({{ $student->registration_number }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_id')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-plus"></i> Add Student
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Current Students - Commented Out -->
        <!--
        <div class="w-full md:w-1/2 px-4">
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-blue-500 text-white px-4 py-3 rounded-t-lg">
                    <h5 class="text-lg font-semibold mb-0">Students in {{ $class->name }}
                        @if ($studentsInClass)
                            <span class="bg-white text-blue-600 px-2 py-1 rounded-full text-sm ml-2">{{ count($studentsInClass) }}</span>
                        @endif
                    </h5>
                </div>
                <div class="p-4">
                    @if ($studentsInClass && count($studentsInClass) > 0)
                        <div class="space-y-2">
                            @php
                                $classStudents = \App\Models\User::whereIn('id', $studentsInClass)->orderBy('name')->get();
                            @endphp
                            @foreach ($classStudents as $student)
                                <div class="flex justify-between items-center bg-gray-50 p-3 rounded">
                                    <div class="flex items-center gap-3">
                                        @if($student->photo)
                                            <img src="{{ asset('storage/' . $student->photo) }}" alt="Profile Picture" class="w-12 h-12 rounded-full object-cover border-2 border-gray-300">
                                        @else
                                            <div class="w-12 h-12 rounded-full bg-gray-300 border-2 border-gray-300 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="font-semibold text-gray-800 mb-1">{{ $student->name }}</h6>
                                            <p class="text-gray-600 text-sm">{{ $student->registration_number }}</p>
                                            @if($student->date_of_birth)
                                                <p class="text-gray-600 text-sm">DOB: {{ \Carbon\Carbon::parse($student->date_of_birth)->format('M j, Y') }}</p>
                                            @endif
                                            @if($student->parent_phone_number)
                                                <p class="text-gray-600 text-sm">Parent: {{ $student->parent_phone_number }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <form action="{{ route('teacher.form-teacher.remove-student', [$class->id, $student->id]) }}" 
                                        method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm"
                                            onclick="return confirm('Are you sure you want to remove this student?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                            No students have been added to this class yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        -->
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('teacher.scores.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>
@endsection
