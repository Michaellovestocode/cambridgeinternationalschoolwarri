@extends('layouts.app')

@section('title', 'Form Teacher Dashboard')

@section('content')
<div class="max-w-6xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Form Teacher Dashboard</h2>

    @if($data->isEmpty())
    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-6 py-4 rounded-lg text-center">
        <p class="font-medium">You are not assigned as a form teacher for any class yet.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($data as $item)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6">
                <h3 class="text-xl font-bold">{{ $item['class']->name }}</h3>
                <p class="text-green-100 text-sm">{{ $item['class']->description }}</p>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-4">
                <!-- Stats -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-blue-50 rounded p-3 text-center">
                        <p class="text-blue-600 text-sm font-semibold">Students</p>
                        <p class="text-2xl font-bold text-blue-700">{{ $item['studentCount'] }}</p>
                    </div>
                    <div class="bg-purple-50 rounded p-3 text-center">
                        <p class="text-purple-600 text-sm font-semibold">Exams</p>
                        <p class="text-2xl font-bold text-purple-700">{{ $item['examCount'] }}</p>
                    </div>
                    <div class="bg-orange-50 rounded p-3 text-center">
                        <p class="text-orange-600 text-sm font-semibold">Attempts</p>
                        <p class="text-2xl font-bold text-orange-700">{{ $item['attemptCount'] }}</p>
                    </div>
                    <div class="bg-green-50 rounded p-3 text-center">
                        <p class="text-green-600 text-sm font-semibold">Avg Score</p>
                        <p class="text-2xl font-bold text-green-700">{{ $item['averageScore'] }}%</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col gap-2 pt-4 border-t">
                    <a href="{{ route('teacher.scores.dashboard') }}" 
                       class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded font-semibold text-center transition">
                        📊 Go to Score Entry
                    </a>
                    <a href="{{ route('admin.report-cards') }}" 
                       class="w-full bg-amber-500 hover:bg-amber-600 text-white py-2 rounded font-semibold text-center transition">
                        📄 Manage Report Cards
                    </a>
                    <a href="{{ route('teacher.scores.dashboard') }}" 
                       class="w-full bg-purple-500 hover:bg-purple-600 text-white py-2 rounded font-semibold text-center transition">
                        📝 Manage Scores
                    </a>
                    <a href="{{ route('teacher.scores.dashboard') }}" 
                       class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 rounded font-semibold text-center transition">
                        👥 Manage Students
                    </a>
                    <a href="{{ route('teacher.scores.dashboard') }}" 
                       class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded font-semibold text-center transition">
                        📥 View Scores
                    </a>
                    @if($data->count() > 0)
                    <a href="{{ route('teacher.scores.dashboard') }}" 
                       class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded font-semibold text-center transition">
                        📄 Teacher Dashboard
                    </a>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-3 border-t text-sm text-gray-600">
                Assigned since {{ $item['assignment']->assigned_date->format('d M Y') }}
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
