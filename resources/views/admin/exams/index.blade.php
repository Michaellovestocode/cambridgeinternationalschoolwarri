@extends('layouts.app')

@section('title', 'Manage Exams')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Manage Exams</h2>
        <a href="{{ route('admin.exam.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
            + Create New Exam
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @forelse($exams as $exam)
        <div class="border-b p-6 hover:bg-gray-50">
            <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-800">{{ $exam->title }}</h3>
                    <p class="text-gray-600 mt-1">{{ $exam->description }}</p>
                </div>
                <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1 rounded-full">
                    {{ $exam->subject }}
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
                <div class="text-sm">
                    <span class="text-gray-600">Duration:</span>
                    <p class="font-semibold">{{ $exam->duration_minutes }} mins</p>
                </div>
                <div class="text-sm">
                    <span class="text-gray-600">Total Marks:</span>
                    <p class="font-semibold">{{ $exam->total_marks }}</p>
                </div>
                <div class="text-sm">
                    <span class="text-gray-600">Pass Mark:</span>
                    <p class="font-semibold">{{ $exam->pass_mark }}</p>
                </div>
                <div class="text-sm">
                    <span class="text-gray-600">Questions:</span>
                    <p class="font-semibold">{{ $exam->questions->count() }}</p>
                </div>
                <div class="text-sm">
                    <span class="text-gray-600">Status:</span>
                    <p class="font-semibold">
                        @if($exam->is_active)
                        <span class="text-green-600">Active</span>
                        @else
                        <span class="text-red-600">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex gap-3 text-sm">
                <span class="text-gray-600">Classes:</span>
                @foreach($exam->classes as $class)
                <span class="bg-gray-100 px-2 py-1 rounded">{{ $class->display_name }}</span>
                @endforeach
            </div>

            <div class="flex gap-2 mt-4 flex-wrap">
                <a href="{{ route('admin.exam.edit', $exam->id) }}" 
                   class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded">
                    ✏️ Edit Exam
                </a>
                <a href="{{ route('admin.exam.questions', $exam->id) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Manage Questions
                </a>
                <a href="{{ route('admin.exam.results', $exam->id) }}" 
                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                    View Results
                </a>
                <a href="{{ route('admin.exam.export.pdf', $exam->id) }}" 
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                    Export PDF
                </a>
                <a href="{{ route('admin.exam.export.word', $exam->id) }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    Export Word
                </a>
                <form method="POST" action="{{ route('admin.exam.delete', $exam->id) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this exam? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded">
                        🗑️ Delete Exam
                    </button>
                </form>
            </div>

            <div class="text-sm text-gray-500 mt-3">
                Created by: {{ $exam->creator->name }} | 
                Available: {{ $exam->start_date->format('d M Y') }} - {{ $exam->end_date->format('d M Y') }}
            </div>
        </div>
        @empty
        <div class="p-12 text-center">
            <div class="text-gray-400 text-6xl mb-4">📝</div>
            <p class="text-gray-600 text-lg mb-4">No exams created yet</p>
            <a href="{{ route('admin.exam.create') }}" 
               class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                Create Your First Exam
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection
