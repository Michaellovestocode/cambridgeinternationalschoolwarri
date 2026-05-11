@extends('layouts.app')

@section('title', 'Edit Website Update')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Edit Website Update</h1>
            <p class="text-sm text-gray-500 mt-1">Adjust how this announcement appears on the homepage and in the ticker.</p>
        </div>
        <a href="{{ route('admin.announcements.index') }}" class="text-sm text-blue-600 hover:underline">Back to updates</a>
    </div>

    <div class="bg-white rounded-3xl shadow-lg p-6">
        @include('admin.announcements._form')
    </div>
</div>
@endsection
