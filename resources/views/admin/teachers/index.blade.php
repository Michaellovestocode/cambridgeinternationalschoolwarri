@extends('layouts.app')

@section('title', 'Manage Teachers')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Manage Teachers</h2>
        <a href="{{ route('admin.teacher.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
            + Add New Teacher
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Subjects</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Classes</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Exams</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($teachers as $index => $teacher)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-600">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $teacher->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $teacher->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                {{ $teacher->subjects->count() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-cyan-100 text-cyan-800">
                                {{ $teacher->teachingClasses->count() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                {{ $teacher->exams->count() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex gap-1 justify-center items-center">
                                <a href="{{ route('admin.subjects.assign-subjects', $teacher->id) }}" 
                                   class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-purple-100 text-purple-800 hover:bg-purple-200 transition" title="Assign Subjects">
                                    📚
                                </a>
                                <a href="{{ route('admin.teacher.assign-classes', $teacher->id) }}" 
                                   class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-cyan-100 text-cyan-800 hover:bg-cyan-200 transition" title="Assign Classes">
                                    Classes
                                </a>
                                <a href="{{ route('admin.teacher.edit', $teacher->id) }}" 
                                   class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800 hover:bg-blue-200 transition" title="Edit Teacher">
                                    ✏️
                                </a>
                                <form action="{{ route('admin.teacher.delete', $teacher->id) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this teacher? This action cannot be undone.')" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800 hover:bg-red-200 transition" 
                                            title="Delete Teacher">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            No teachers yet. Click "Add New Teacher" to get started.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
        </table>
    </div>
</div>
@endsection
