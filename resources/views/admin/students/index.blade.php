@extends('layouts.app')

@section('title', 'Manage Students')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600">Manage Students</h2>
            <p class="text-gray-600 mt-1 text-sm">View and manage all students by class</p>
        </div>
        <a href="{{ route('admin.student.create') }}" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:shadow-lg text-white px-6 py-3 rounded-xl font-bold transform hover:scale-105 transition">
            ➕ Add New Student
        </a>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold opacity-90">Total Students</div>
                    <div class="text-4xl font-black mt-2">{{ $students->count() }}</div>
                </div>
                <div class="text-5xl opacity-30">👥</div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold opacity-90">Total Classes</div>
                    <div class="text-4xl font-black mt-2">{{ $classes->count() }}</div>
                </div>
                <div class="text-5xl opacity-30">📚</div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold opacity-90">Unassigned</div>
                    <div class="text-4xl font-black mt-2">{{ $students->whereNull('class_id')->count() }}</div>
                </div>
                <div class="text-5xl opacity-30">⚠️</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-6">
        <form method="GET" action="{{ route('admin.students') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search Student</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}"
                       placeholder="Search by student name or registration number"
                       class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-purple-500">
            </div>
            <div>
                <label for="class_id" class="block text-sm font-semibold text-gray-700 mb-2">Class Filter</label>
                <select id="class_id" name="class_id" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-purple-500">
                    <option value="">All classes</option>
                    <option value="unassigned" {{ request('class_id') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>
                        {{ $class->display_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3 flex flex-wrap gap-3">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-3 rounded-xl font-semibold">
                    Search
                </button>
                <a href="{{ route('admin.students') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-3 rounded-xl font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Classes Section -->
    <div>
        <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-4 py-2 rounded-lg mr-3 font-black">All Classes</span>
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($classes as $class)
            @php
                $classStudents = $students->where('class_id', $class->id);
            @endphp
            @continue($classStudents->isEmpty())
            
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Card Header -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4">
                    <h4 class="text-xl font-bold">{{ $class->display_name }}</h4>
                    <p class="text-sm opacity-90">
                        {{ $class->description }}
                    </p>
                </div>

                <!-- Student Count -->
                <div class="bg-green-50 p-3 border-b">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-700">Total Students:</span>
                        <span class="text-2xl font-bold text-green-600">{{ $classStudents->count() }}</span>
                    </div>
                </div>

                <!-- Students List -->
                <div class="max-h-96 overflow-y-auto">
                    @forelse($classStudents as $student)
                    <div class="p-4 border-b hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">{{ $student->name }}</p>
                                <p class="text-sm text-gray-600">{{ $student->registration_number }}</p>
                            </div>
                            <div class="flex gap-2 ml-2">
                                <a href="{{ route('admin.student.edit', $student->id) }}" 
                                   class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition" 
                                   title="Edit Student">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('admin.student.delete', $student->id) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete {{ $student->name }}? This action cannot be undone.')" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200 transition" 
                                            title="Delete Student">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-gray-500">
                        <p class="text-4xl mb-2">📚</p>
                        <p class="text-sm">No students yet</p>
                    </div>
                    @endforelse
                </div>

                <!-- Card Footer -->
                @if($classStudents->count() > 0)
                <div class="bg-gray-50 p-3 text-center">
                    <a href="{{ route('admin.student.create') }}" 
                       class="text-sm text-green-600 hover:text-green-800 font-semibold">
                        + Add Student to {{ $class->display_name }}
                    </a>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        @if($students->whereNotNull('class_id')->isEmpty())
        <div class="bg-white rounded-2xl shadow p-8 text-center text-gray-500">
            No students matched your current search.
        </div>
        @endif
    </div>

    <!-- Unassigned Students -->
    @if($students->whereNull('class_id')->count() > 0)
    <div>
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <span class="bg-orange-600 text-white px-3 py-1 rounded-lg mr-2">⚠️ Unassigned</span>
            Students Without Class
        </h3>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($students->whereNull('class_id') as $student)
                <div class="border border-orange-300 bg-orange-50 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $student->name }}</p>
                            <p class="text-sm text-gray-600">{{ $student->registration_number }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.student.edit', $student->id) }}" 
                               class="text-blue-600 hover:text-blue-800" title="Assign Class">
                                ✏️
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
