@extends('layouts.app')

@section('title', 'Form Teacher Management')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Form Teacher Assignments</h2>
            <a href="{{ route('admin.form-teachers.create') }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                + Assign Form Teacher
            </a>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
        @endif

        <form method="GET" action="{{ route('admin.form-teachers.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Class or Teacher</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}"
                       placeholder="Search by teacher name, email, reg no, or class"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">All statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="md:col-span-3 flex flex-wrap gap-3">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-lg font-semibold">
                    Search
                </button>
                <a href="{{ route('admin.form-teachers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-3 rounded-lg font-semibold">
                    Reset
                </a>
            </div>
        </form>

        @if($formTeachers->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded text-center">
            <p class="font-medium">No form teachers assigned yet.</p>
            <p class="text-sm mt-1">
                <a href="{{ route('admin.form-teachers.create') }}" class="text-yellow-600 hover:text-yellow-800 underline">
                    Assign a form teacher to a class
                </a>
            </p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                        <th class="px-6 py-3 text-left font-semibold">#</th>
                        <th class="px-6 py-3 text-left font-semibold">Class</th>
                        <th class="px-6 py-3 text-left font-semibold">Form Teacher</th>
                        <th class="px-6 py-3 text-left font-semibold">Assigned Date</th>
                        <th class="px-6 py-3 text-left font-semibold">Status</th>
                        <th class="px-6 py-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formTeachers as $ft)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-700">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-800">{{ $ft->schoolClass->display_name }}</span>
                            <span class="text-gray-500 text-sm block">{{ $ft->schoolClass->description }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-800">{{ $ft->teacher->name }}</span>
                            <span class="text-gray-500 text-sm block">{{ $ft->teacher->email }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-700">
                            {{ $ft->assigned_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($ft->is_active)
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                Active
                            </span>
                            @else
                            <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold">
                                Inactive
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('admin.form-teachers.show', $ft->id) }}" 
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm"
                                   title="View Details">
                                    👁️ View
                                </a>
                                <a href="{{ route('admin.form-teachers.edit', $ft->id) }}" 
                                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm"
                                   title="Edit">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('admin.form-teachers.destroy', $ft->id) }}" 
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Are you sure you want to remove this form teacher?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                        🗑️ Remove
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $formTeachers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
