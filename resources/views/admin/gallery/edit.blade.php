@extends('layouts.blog-admin')

@section('title', 'Edit Gallery Album')
@section('page_heading', 'Edit Gallery Album')

@section('content')
    @include('admin.gallery._form', ['isEdit' => true])
@endsection
