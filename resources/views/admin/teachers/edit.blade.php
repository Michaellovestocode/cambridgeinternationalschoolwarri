@extends('layouts.app')

@section('title', 'Edit Teacher')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Teacher</h2>

        <form action="{{ route('admin.teacher.update', $teacher->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $teacher->name) }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input type="email" name="email" value="{{ old('email', $teacher->email) }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Staff ID *</label>
                <input type="text" name="registration_number" value="{{ old('registration_number', $teacher->registration_number) }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                @error('registration_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number</label>
                <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $teacher->whatsapp_number) }}"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="+234...">
                @error('whatsapp_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                @if($teacher->photo)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $teacher->photo) }}" alt="Current Photo" class="w-20 h-20 rounded-full object-cover">
                    </div>
                @endif
                <input type="file" name="photo" accept="image/*"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                @error('photo')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password (leave blank to keep current)</label>
                <input type="password" name="password"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="Enter new password or leave blank">
                @error('password')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
                    Update Teacher
                </button>
                <a href="{{ route('admin.teachers') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
