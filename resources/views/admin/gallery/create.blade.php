@extends('layouts.blog-admin')

@section('title', 'Create Gallery Album')
@section('page_heading', 'Create Gallery Album')

@section('content')
    @include('admin.gallery._form', ['isEdit' => false])
@endsection
