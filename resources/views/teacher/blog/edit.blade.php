@extends('layouts.app')

@section('title', 'Edit Blog Article')

@section('content')
<div class="bg-white rounded-3xl shadow-lg p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900">Edit Blog Article</h1>
        <p class="text-gray-500 mt-1">Update the article and submit it again for admin approval.</p>
    </div>
    @include('teacher.blog._form')
</div>
@endsection
