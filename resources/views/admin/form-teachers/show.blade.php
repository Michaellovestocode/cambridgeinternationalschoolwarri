@extends('layouts.app')

@section('title', 'Form Teacher Details')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Form Teacher Details</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.student.create', ['class_id' => $formTeacher->schoolClass->id]) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    + Add Student
                </a>
                <a href="{{ route('admin.form-teachers.edit', $formTeacher->id) }}" 
                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                    ✏️ Edit
                </a>
                <a href="{{ route('admin.form-teachers.index') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                    ← Back
                </a>
            </div>
        </div>

        <!-- Header Card -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6 mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-green-100 text-sm">Class</p>
                    <p class="text-2xl font-bold">{{ $formTeacher->schoolClass->display_name }}</p>
                    <p class="text-green-100 text-sm">{{ $formTeacher->schoolClass->description }}</p>
                </div>
                <div>
                    <p class="text-green-100 text-sm">Form Teacher</p>
                    <p class="text-2xl font-bold">{{ $formTeacher->teacher->name }}</p>
                    <p class="text-green-100 text-sm">{{ $formTeacher->teacher->email }}</p>
                </div>
            </div>
        </div>

        <!-- Key Information -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Assigned Date</p>
                <p class="text-xl font-bold text-blue-700">{{ $formTeacher->assigned_date->format('d M Y') }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Total Students</p>
                <p class="text-xl font-bold text-purple-700">{{ $students->count() }}</p>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Class Exams</p>
                <p class="text-xl font-bold text-orange-700">{{ $exams->count() }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                <p class="text-gray-600 text-sm">Status</p>
                <p class="text-xl font-bold {{ $formTeacher->is_active ? 'text-green-700' : 'text-gray-700' }}">
                    {{ $formTeacher->is_active ? 'Active' : 'Inactive' }}
                </p>
            </div>
        </div>

        <!-- Exam Results Summary -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Class Exam Results Summary</h3>
            
            @if($exams->isEmpty())
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                No exams assigned to this class yet.
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                            <th class="px-4 py-3 text-left">Exam Title</th>
                            <th class="px-4 py-3 text-left">Subject</th>
                            <th class="px-4 py-3 text-center">Total Marks</th>
                            <th class="px-4 py-3 text-center">Pass Mark</th>
                            <th class="px-4 py-3 text-center">Attempts</th>
                            <th class="px-4 py-3 text-center">Avg Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exams as $exam)
                        @php
                            $examAttempts = $attemptsByExam[$exam->id] ?? [];
                            $avgScore = count($examAttempts) > 0 
                                ? number_format(collect($examAttempts)->avg('score'), 2)
                                : 'N/A';
                        @endphp
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $exam->title }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $exam->subject }}</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $exam->total_marks }}</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $exam->pass_mark }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                    {{ count($examAttempts) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-green-700">{{ $avgScore }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Students List -->
        <div>
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-4">
                <h3 class="text-xl font-bold text-gray-800">Class Students</h3>
                <form method="GET" action="{{ route('admin.form-teachers.show', $formTeacher->id) }}" class="w-full md:w-auto flex gap-2">
                    <input type="text" name="student_search" value="{{ request('student_search') }}"
                           placeholder="Search student name, email, or reg no"
                           class="w-full md:w-80 border border-gray-300 rounded-lg px-4 py-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                        Search
                    </button>
                    @if(request()->filled('student_search'))
                    <a href="{{ route('admin.form-teachers.show', $formTeacher->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-semibold">
                        Reset
                    </a>
                    @endif
                </form>
            </div>
            
            @if($students->isEmpty())
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                No students matched this search.
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($students as $student)
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200">
                    <p class="font-bold text-gray-800">{{ $student->name }}</p>
                    <p class="text-sm text-gray-600">{{ $student->email }}</p>
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>Reg No:</strong> {{ $student->registration_number ?? 'N/A' }}
                    </p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
