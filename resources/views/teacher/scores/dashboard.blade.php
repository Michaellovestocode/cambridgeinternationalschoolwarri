@extends('layouts.app')

@section('title', 'Score Entry Dashboard')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">📊 Score Entry Dashboard</h1>
        <p class="text-gray-600">Manage continuous assessment and exam scores for your students</p>
    </div>

    <!-- Session/Term Info -->
    @if($selectedSession && $selectedTerm)
    <div class="bg-blue-50 border border-blue-300 rounded-lg p-4 mb-6">
        <p class="text-blue-800">
            <strong>Viewing Session:</strong> {{ $selectedSession->name ?? 'N/A' }} | 
            <strong>Term:</strong> {{ $selectedTerm->name ?? 'N/A' }}
        </p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <form method="GET" action="{{ route('teacher.scores.dashboard') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="session_id" class="block text-sm font-semibold text-gray-700 mb-2">Session</label>
                <select id="session_id" name="session_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    @foreach($sessions as $session)
                    <option value="{{ $session->id }}" {{ (string) request('session_id', $selectedSession?->id) === (string) $session->id ? 'selected' : '' }}>
                        {{ $session->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="term_id" class="block text-sm font-semibold text-gray-700 mb-2">Term</label>
                <select id="term_id" name="term_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    @foreach($terms as $term)
                    <option value="{{ $term->id }}" {{ (string) request('term_id', $selectedTerm?->id) === (string) $term->id ? 'selected' : '' }}>
                        {{ $term->name }}{{ $term->session ? ' - ' . $term->session->name : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-semibold">
                    Apply
                </button>
                <a href="{{ route('teacher.scores.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-3 rounded-lg font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total Scores Entered</p>
                    <p class="text-4xl font-bold">{{ $totalScoresEntered ?? 0 }}</p>
                </div>
                <div class="text-5xl opacity-30">📝</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm">Pending Scores</p>
                    <p class="text-4xl font-bold">{{ $pendingScores ?? 0 }}</p>
                </div>
                <div class="text-5xl opacity-30">⏳</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Submitted Scores</p>
                    <p class="text-4xl font-bold">{{ $submittedScores ?? 0 }}</p>
                </div>
                <div class="text-5xl opacity-30">✅</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Total Subjects</p>
                    <p class="text-4xl font-bold">{{ count($teacherSubjects ?? []) }}</p>
                </div>
                <div class="text-5xl opacity-30">📚</div>
            </div>
        </div>
    </div>

    <!-- Main Actions -->
    <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Quick Actions</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Enter Scores -->
            <div class="border-2 border-blue-300 rounded-lg p-6 hover:shadow-lg transition">
                <div class="text-5xl mb-4">📝</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Enter Scores</h3>
                <p class="text-gray-600 mb-4">Start entering continuous assessment and exam scores for your students.</p>
                <a href="{{ route('teacher.scores.select') }}" class="inline-block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                    Begin Entering Scores
                </a>
            </div>

            <!-- View My Scores -->
            <div class="border-2 border-green-300 rounded-lg p-6 hover:shadow-lg transition">
                <div class="text-5xl mb-4">👁️</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">View My Scores</h3>
                <p class="text-gray-600 mb-4">Check all the scores you've entered and their current status.</p>
                <a href="{{ route('teacher.scores.my-scores') }}" class="inline-block w-full text-center bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                    View Scores
                </a>
            </div>

            <!-- Instructions -->
            <div class="border-2 border-purple-300 rounded-lg p-6 hover:shadow-lg transition">
                <div class="text-5xl mb-4">ℹ️</div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">How It Works</h3>
                <p class="text-gray-600 mb-4">Learn about the score entry system and best practices.</p>
                <button class="w-full text-center bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition" onclick="alert('Step 1: Select your class and subject\nStep 2: Enter CA1, CA2, CA3 (each out of 10)\nStep 3: Enter Exam score (out of 70)\nStep 4: Save as draft or submit for approval')">
                    View Instructions
                </button>
            </div>
        </div>
    </div>

    <!-- Score Entry Tips -->
    <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded p-6">
        <h3 class="text-lg font-bold text-yellow-800 mb-3">💡 Score Entry Tips</h3>
        <ul class="text-yellow-700 space-y-2">
            <li>✓ CA1, CA2, and CA3 are each out of 10 marks</li>
            <li>✓ Exam score is out of 70 marks</li>
            <li>✓ Total score per subject = 30 (CA) + 70 (Exam) = 100</li>
            <li>✓ Save scores as draft before final submission</li>
            <li>✓ Once submitted, scores cannot be modified</li>
        </ul>
    </div>
</div>

@endsection
