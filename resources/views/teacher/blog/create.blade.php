@extends('layouts.app')

@section('title', 'Submit Blog Article')

@section('content')
<div class="bg-white rounded-3xl shadow-lg p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900">Submit Blog Article</h1>
        <p class="text-gray-500 mt-1">Your article will be reviewed by admin before it appears on the website.</p>
    </div>
    @include('teacher.blog._form')
</div>
@endsection
