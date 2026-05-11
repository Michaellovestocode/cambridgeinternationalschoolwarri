@extends('layouts.app')

@section('title', 'Assign Teachers to ' . $subject->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">👥 Assign Teachers</h2>
            <p class="text-gray-600 mt-2">Subject: <strong>{{ $subject->name }}</strong></p>
        </div>

        <form action="{{ route('admin.subjects.update-teachers', $subject->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Select Teachers *</label>
                <div class="border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto">
                    @forelse($teachers as $teacher)
                    <div class="flex items-center mb-3">
                        <input type="checkbox" name="teachers[]" value="{{ $teacher->id }}"
                               {{ $subject->teachers->contains($teacher->id) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <label class="ml-3 text-sm text-gray-700">
                            <div class="font-medium">{{ $teacher->name }}</div>
                            <div class="text-xs text-gray-600">{{ $teacher->email }} ({{ $teacher->registration_number }})</div>
                        </label>
                    </div>
                    @empty
                    <p class="text-gray-500">No teachers found.</p>
                    @endforelse
                </div>
                @error('teachers')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold">
                    Assign Teachers
                </button>
                <a href="{{ route('admin.subjects.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
