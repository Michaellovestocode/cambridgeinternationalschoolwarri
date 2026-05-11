@extends('layouts.app')

@section('title', 'Report Cards')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Report Cards</h1>
            <p class="text-gray-600 mt-1">
                Generate and manage report cards for the active session and term.
            </p>
        </div>
        <a href="{{ route('admin.report-cards.manual') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-lg font-medium text-center">
            Manual Report Card Builder
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <form method="GET" action="{{ route('admin.report-cards') }}" class="grid lg:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Student</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}"
                       placeholder="Name or registration number"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>
            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                <select id="student_id" name="student_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">All students</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" {{ (string) request('student_id') === (string) $student->id ? 'selected' : '' }}>
                            {{ $student->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="session_id" class="block text-sm font-medium text-gray-700 mb-2">Session</label>
                <select id="session_id" name="session_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    @foreach ($sessions as $session)
                        <option value="{{ $session->id }}" {{ (string) request('session_id', $selectedSession?->id) === (string) $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="term_id" class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                <select id="term_id" name="term_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" {{ (string) request('term_id', $selectedTerm?->id) === (string) $term->id ? 'selected' : '' }}>
                            {{ $term->name }}{{ $term->session ? ' - ' . $term->session->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                <select id="class_id" name="class_id" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">All classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>
                            {{ $class->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">All statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="generated" {{ request('status') === 'generated' ? 'selected' : '' }}>Generated</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>
            <div class="lg:col-span-4 flex flex-wrap gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-medium">
                    Search
                </button>
                <a href="{{ route('admin.report-cards') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-3 rounded-lg font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Generate For Student</h2>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse ($students as $student)
                    <div class="flex items-center justify-between border border-gray-200 rounded-lg p-4">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $student->name }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $student->registration_number }}{{ $student->class ? ' • ' . $student->class->display_name : '' }}
                            </p>
                        </div>
                        <a href="{{ route('admin.report-cards.generate', [
                            'student' => $student->id,
                            'session_id' => request('session_id', $selectedSession?->id),
                            'term_id' => request('term_id', $selectedTerm?->id),
                        ]) }}"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            Generate
                        </a>
                    </div>
                @empty
                    <p class="text-gray-600">No students available for report card generation.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Bulk Generate By Class</h2>
            <form action="{{ route('admin.report-cards.bulk') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="bulk_session_id" class="block text-sm font-medium text-gray-700 mb-2">Session</label>
                        <select id="bulk_session_id" name="session_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                            @foreach ($sessions as $session)
                                <option value="{{ $session->id }}" {{ (string) request('session_id', $selectedSession?->id) === (string) $session->id ? 'selected' : '' }}>
                                    {{ $session->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="bulk_term_id" class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                        <select id="bulk_term_id" name="term_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}" {{ (string) request('term_id', $selectedTerm?->id) === (string) $term->id ? 'selected' : '' }}>
                                    {{ $term->name }}{{ $term->session ? ' - ' . $term->session->name : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Class</label>
                    <select id="class_id" name="class_id" class="w-full border border-gray-300 rounded-lg px-4 py-3" required>
                        <option value="">Select class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-lg font-medium">
                    Generate Report Cards
                </button>
            </form>

            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Current Window</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-sm text-blue-700">Selected Session</p>
                        <p class="font-semibold text-blue-900">{{ $selectedSession?->name ?? 'Not set' }}</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-sm text-purple-700">Selected Term</p>
                        <p class="font-semibold text-purple-900">{{ $selectedTerm?->name ?? 'Not set' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Generated Report Cards</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="px-4 py-3 text-left">Student</th>
                        <th class="px-4 py-3 text-left">Class</th>
                        <th class="px-4 py-3 text-left">Session</th>
                        <th class="px-4 py-3 text-left">Term</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportCards as $reportCard)
                        <tr class="border-t border-gray-200">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $reportCard->student->name }}</td>
                            <td class="px-4 py-3">{{ $reportCard->class->display_name }}</td>
                            <td class="px-4 py-3">{{ $reportCard->session->name }}</td>
                            <td class="px-4 py-3">{{ $reportCard->term->name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs {{ $reportCard->isPublished() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($reportCard->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.report-cards.publication', $reportCard->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="published" value="{{ $reportCard->isPublished() ? 0 : 1 }}">
                                        <button type="submit"
                                                class="{{ $reportCard->isPublished() ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} px-4 py-2 rounded-lg text-sm font-medium">
                                            {{ $reportCard->isPublished() ? 'Hide' : 'Publish' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.report-cards.preview', $reportCard->id) }}"
                                       class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-medium">
                                        Open
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No report cards have been generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $reportCards->links() }}
        </div>
    </div>
</div>
@endsection
