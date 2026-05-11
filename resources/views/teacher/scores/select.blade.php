@extends('layouts.app')

@section('title', 'Select Class and Subject')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">📋 Select Class and Subject</h1>
        <p class="text-gray-600">Choose the class and subject to enter scores for</p>
    </div>

    <!-- Session/Term Info -->
    @if($activeSession && $activeTerm)
    <div class="bg-blue-50 border border-blue-300 rounded-lg p-4 mb-6">
        <p class="text-blue-800">
            <strong>Current Session:</strong> {{ $activeSession->name ?? 'N/A' }} | 
            <strong>Term:</strong> {{ $activeTerm->name ?? 'N/A' }}
        </p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('teacher.scores.enter') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Class Selection -->
            <div>
                <label for="class_id" class="block text-gray-700 font-bold mb-2">
                    <span class="text-red-500">*</span> Select Class
                </label>
                <select name="class_id" id="class_id" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 @error('class_id') border-red-500 @enderror" required>
                    <option value="">-- Choose a class --</option>
                    @forelse($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} - {{ $class->description ?? 'No description' }}
                        </option>
                    @empty
                        <option value="" disabled>No classes available</option>
                    @endforelse
                </select>
                @error('class_id')
                    <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Subject Selection -->
            <div>
                <label for="subject_id" class="block text-gray-700 font-bold mb-2">
                    <span class="text-red-500">*</span> Select Subject
                </label>
                <select name="subject_id" id="subject_id" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 @error('subject_id') border-red-500 @enderror" required>
                    <option value="">-- Choose a subject --</option>
                    @forelse($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }} ({{ $subject->code ?? 'N/A' }})
                        </option>
                    @empty
                        <option value="" disabled>No subjects available</option>
                    @endforelse
                </select>
                @error('subject_id')
                    <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-300 rounded-lg p-4">
                <p class="text-blue-800">
                    <strong>Note:</strong> You can enter and edit scores multiple times before submission. 
                    Once submitted, scores cannot be modified.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 pt-6">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    ➜ Continue to Score Entry
                </button>
                <a href="{{ route('teacher.scores.dashboard') }}" class="flex-1 text-center bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 px-6 rounded-lg transition">
                    ← Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="mt-8 bg-gray-50 rounded-lg p-6 border border-gray-300">
        <h3 class="text-lg font-bold text-gray-800 mb-3">❓ Need Help?</h3>
        <ul class="text-gray-700 space-y-2">
            <li>• Make sure you select both a class and a subject</li>
            <li>• You can only enter scores for classes and subjects you're assigned to</li>
            <li>• Contact the admin if you need access to additional classes</li>
        </ul>
    </div>
</div>

@endsection
