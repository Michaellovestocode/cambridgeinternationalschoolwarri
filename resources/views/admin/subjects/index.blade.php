@extends('layouts.app')

@section('title', 'Subjects Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">📚 Subjects Management</h1>
                <p class="text-blue-100 mt-1">Manage subjects and assign them to teachers</p>
            </div>
            <a href="{{ route('admin.subjects.create') }}" class="bg-white text-blue-600 hover:bg-blue-50 px-6 py-2 rounded-lg font-semibold">
                + Add New Subject
            </a>
        </div>
    </div>

    @if(($pendingRejectionRequests ?? collect())->isNotEmpty())
    <div class="rounded-lg bg-white shadow">
        <div class="border-b p-6">
            <h2 class="text-xl font-bold text-gray-800">Pending Subject Removal Requests</h2>
        </div>
        <div class="divide-y">
            @foreach($pendingRejectionRequests as $requestRow)
                @php($className = trim(($requestRow->class_name ?? '') . ' ' . ($requestRow->class_description ?? '')))
                <div class="flex flex-col gap-3 p-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="font-bold text-gray-900">{{ $requestRow->teacher_name }} wants to remove {{ $requestRow->subject_name }}</p>
                        <p class="text-sm text-gray-600">Class: {{ $className !== '' ? $className : 'All assigned classes' }}</p>
                        @if($requestRow->reason)
                            <p class="mt-1 text-sm text-gray-500">{{ $requestRow->reason }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.subjects.rejection-requests.approve', $requestRow->id) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit" onclick="return confirm('Approve this removal request?')" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">
                            Approve Removal
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Subjects Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">All Subjects</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Code</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Teachers</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Exams</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($subjects as $index => $subject)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $subjects->firstItem() + $loop->index }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $subject->name }}</div>
                            @if($subject->description)
                                <div class="text-xs text-gray-600">{{ Str::limit($subject->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($subject->code)
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    {{ $subject->code }}
                                </span>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-semibold text-blue-600">{{ $subject->teachers_count }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-semibold text-green-600">{{ $subject->exams_count }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($subject->is_active)
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    Active
                                </span>
                            @else
                                <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.subjects.edit', $subject->id) }}" 
                                   class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition">
                                    ✏️ Edit
                                </a>
                                <a href="{{ route('admin.subjects.assign-teachers', $subject->id) }}" 
                                   class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-purple-100 text-purple-800 hover:bg-purple-200 transition">
                                    👨‍🏫 Teachers
                                </a>
                                <form action="{{ route('admin.subjects.destroy', $subject->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-red-100 text-red-800 hover:bg-red-200 transition"
                                            onclick="return confirm('Are you sure you want to delete this subject? This action cannot be undone.')">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No subjects found. <a href="{{ route('admin.subjects.create') }}" class="text-blue-600 hover:underline">Create one</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($subjects->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $subjects->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
