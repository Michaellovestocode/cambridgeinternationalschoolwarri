@extends('layouts.app')

@section('title', 'Create Learning Session')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Learning Session</h1>
                <p class="text-gray-600 text-sm">Add the lesson first, then you can attach questions.</p>
            </div>
            <a href="{{ route('admin.learning-sessions.index') }}" class="text-blue-600 hover:underline">Back</a>
        </div>

        @include('admin.learning-sessions.partials.form', [
            'action' => route('admin.learning-sessions.store'),
            'method' => 'POST',
            'learningSession' => null,
            'subjects' => $subjects,
            'classes' => $classes,
        ])
    </div>
</div>
@endsection
