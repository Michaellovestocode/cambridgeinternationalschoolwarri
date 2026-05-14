@extends('layouts.app')

@section('title', 'Manage Classes')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600">Manage Classes</h2>
            <p class="text-gray-600 mt-1 text-sm">Create and organize school classes</p>
        </div>
    </div>

    <!-- Add New Class Form -->
    <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg border border-gray-100 p-8">
        <h3 class="text-2xl font-bold text-gray-900 mb-6">➕ Add New Class</h3>
        
        <form action="{{ route('admin.class.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Class Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-purple-500 transition"
                       placeholder="e.g., Year 7 Stars">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Description</label>
                <input type="text" name="description" value="{{ old('description') }}"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-purple-500 transition"
                       placeholder="e.g., Junior Secondary">
                @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:shadow-lg text-white px-6 py-3 rounded-xl font-bold transform hover:scale-105 transition">
                    Add Class
                </button>
            </div>
        </form>
    </div>

    <!-- Classes List -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-4">
            <h3 class="text-white font-bold text-lg">📚 All Classes ({{ $classes->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 border-b-2 border-gray-100">
                    <tr>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">#</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Class Name</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Description</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Students</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Exams</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($classes as $index => $class)
                    <tr class="hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 transition">
                        <td class="px-8 py-4 whitespace-nowrap text-sm font-semibold text-gray-600">{{ $index + 1 }}</td>
                        <td class="px-8 py-4 whitespace-nowrap">
                            <div class="font-bold text-gray-900 text-base">{{ $class->display_name }}</div>
                        </td>
                        <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-600">{{ $class->description ?? '—' }}</td>
                        <td class="px-8 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-700">
                                {{ $class->students_count }}
                            </span>
                        </td>
                        <td class="px-8 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700">
                                {{ $class->exams_count }}
                            </span>
                        </td>
                        <td class="px-8 py-4 whitespace-nowrap text-sm">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.class.edit', $class) }}" class="bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-2 rounded-lg font-semibold transition">Edit</a>
                                <form action="{{ route('admin.class.delete', $class->id) }}" method="POST" 
                                      onsubmit="return confirm('Delete this class?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-lg font-semibold transition">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-16 text-center">
                            <div class="text-gray-400 text-lg">📭 No classes yet</div>
                            <p class="text-gray-500 text-sm mt-1">Add one above to get started</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
