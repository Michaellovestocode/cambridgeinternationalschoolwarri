@extends('layouts.app')

@section('title', 'Learning Sessions')

@push('styles')
<style>
    @media (max-width: 767px) {
        .learning-session-hero {
            border-radius: 1.25rem;
            padding: 1.25rem;
        }

        .learning-session-hero h1 {
            font-size: 2rem;
            line-height: 2.25rem;
        }

        .learning-session-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .learning-session-table {
            min-width: 700px;
        }

        .learning-session-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .learning-session-actions a,
        .learning-session-actions form,
        .learning-session-actions button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <div class="learning-session-hero bg-gradient-to-r from-emerald-600 to-cyan-700 text-white rounded-lg shadow p-6">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold">Learning Sessions</h1>
                <p class="text-emerald-50 mt-1">Create lesson content and practice questions for students.</p>
            </div>
            <a href="{{ route('teacher.assessment-studio') }}" class="bg-white/15 hover:bg-white/20 text-white px-4 py-2 rounded-lg font-semibold border border-white/20">
                Assessment Studio
            </a>
            <a href="{{ route('admin.learning-sessions.create') }}" class="bg-white text-emerald-700 hover:bg-emerald-50 px-6 py-2 rounded-lg font-semibold">
                + New Session
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="learning-session-table-wrap overflow-x-auto">
            <table class="learning-session-table w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Session</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Class</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase">Questions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($sessions as $session)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $session->title }}</div>
                            <div class="text-sm text-gray-600">{{ $session->topic }} • {{ $session->estimated_minutes }} mins</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full bg-cyan-100 px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-cyan-800">
                                {{ $session->assessment_type ?? 'quiz' }}
                            </span>
                            <div class="mt-1 text-[11px] uppercase tracking-wide text-gray-500">{{ $session->assessment_format ?? 'objective' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $session->subject->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $session->schoolClass->display_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-center font-bold text-cyan-700">{{ $session->questions_count }}</td>
                        <td class="px-6 py-4">
                            @if($session->is_published)
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">Published</span>
                            @else
                                <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">Draft</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="learning-session-actions flex flex-wrap gap-2">
                                <a href="{{ route('admin.learning-sessions.edit', $session) }}" class="bg-blue-100 text-blue-800 hover:bg-blue-200 px-3 py-1 rounded-md text-sm font-medium text-center">Edit</a>
                                @if($session->is_published)
                                    <a href="{{ route('student.learning.show', $session) }}" class="bg-emerald-100 text-emerald-800 hover:bg-emerald-200 px-3 py-1 rounded-md text-sm font-medium text-center">View</a>
                                @endif
                                <form action="{{ route('admin.learning-sessions.destroy', $session) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-100 text-red-800 hover:bg-red-200 px-3 py-1 rounded-md text-sm font-medium w-full" onclick="return confirm('Delete this learning session?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            No learning sessions yet. <a href="{{ route('admin.learning-sessions.create') }}" class="text-blue-600 hover:underline">Create the first one</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
        <div class="px-6 py-4 border-t">{{ $sessions->links() }}</div>
        @endif
    </div>
</div>
@endsection
