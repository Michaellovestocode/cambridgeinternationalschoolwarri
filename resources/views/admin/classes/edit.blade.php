@extends('layouts.app')

@section('title', 'Edit Class')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <div class="mb-6">
            <h1 class="text-3xl font-black text-gray-900">Edit Class</h1>
            <p class="text-gray-500 mt-1">Update the class name or description. Existing students and exams remain attached.</p>
        </div>

        <form action="{{ route('admin.class.update', $class) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Class Name *</label>
                <input type="text" name="name" value="{{ old('name', $class->name) }}" required
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-purple-500 transition"
                       placeholder="e.g., Year 11">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Description</label>
                <input type="text" name="description" value="{{ old('description', $class->description) }}"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-purple-500 transition"
                       placeholder="e.g., Science">
                @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-xl font-bold">Save Changes</button>
                <a href="{{ route('admin.classes') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-xl font-bold">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
