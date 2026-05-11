@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Student</h2>

        <form action="{{ route('admin.student.update', $student->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $student->name) }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Registration Number *</label>
                <input type="text" name="registration_number" value="{{ old('registration_number', $student->registration_number) }}" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                @error('registration_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Class *</label>
                <select name="class_id" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                        {{ $class->display_name }}
                    </option>
                    @endforeach
                </select>
                @error('class_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                @if($student->photo)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $student->photo) }}" alt="Current Photo" class="w-20 h-20 rounded-full object-cover">
                    </div>
                @endif
                <input type="file" name="photo" accept="image/*"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                @error('photo')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                <input type="text" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('d/m/Y')) }}"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="DD/MM/YYYY" inputmode="numeric">
                <p class="text-xs text-gray-500 mt-1">Use day/month/year, for example 24/04/2012.</p>
                @error('date_of_birth')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Parent Phone Number</label>
                <input type="tel" name="parent_phone_number" value="{{ old('parent_phone_number', $student->parent_phone_number) }}"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="e.g., +2348012345678">
                @error('parent_phone_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sex</label>
                <select name="sex" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">-- Select Sex --</option>
                    <option value="male" {{ old('sex', $student->sex) == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('sex', $student->sex) == 'female' ? 'selected' : '' }}>Female</option>
                </select>
                @error('sex')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
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
                    Update Student
                </button>
                <a href="{{ route('admin.students') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
