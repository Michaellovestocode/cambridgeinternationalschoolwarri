@extends('layouts.app')

@section('title', 'Learning Sessions')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-emerald-600 to-cyan-700 text-white rounded-lg shadow p-6">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold">Learning Sessions</h1>
                <p class="text-emerald-50 mt-1">Create lesson content and practice questions for students.</p>
            </div>
            <a href="{{ route('admin.learning-sessions.create') }}" class="bg-white text-emerald-700 hover:bg-emerald-50 px-6 py-2 rounded-lg font-semibold">
                + New Session
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Session</th>
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
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.learning-sessions.edit', $session) }}" class="bg-blue-100 text-blue-800 hover:bg-blue-200 px-3 py-1 rounded-md text-sm font-medium">Edit</a>
                                <form action="{{ route('admin.learning-sessions.destroy', $session) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-100 text-red-800 hover:bg-red-200 px-3 py-1 rounded-md text-sm font-medium" onclick="return confirm('Delete this learning session?')">Delete</button>
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
