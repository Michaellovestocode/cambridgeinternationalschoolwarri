@extends('layouts.app')

@section('title', 'Edit Term')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Term</h1>
            <p class="text-gray-600 mt-1">Update the term dates or change the Next Term Begins date for report cards.</p>
        </div>
        <a href="{{ route('admin.academic-sessions.index') }}" class="text-sm text-blue-600 hover:underline">Back to Sessions & Terms</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <form method="POST" action="{{ route('admin.terms.update', $term->id) }}" class="grid md:grid-cols-5 gap-4">
            @csrf
            @method('PUT')

            <div>
                <label for="term_session_id" class="block text-sm font-medium text-gray-700 mb-2">Session *</label>
                <select id="term_session_id" name="session_id" required class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">Select session</option>
                    @foreach ($sessions as $session)
                        <option value="{{ $session->id }}" {{ old('session_id', $term->session_id) == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="term_name" class="block text-sm font-medium text-gray-700 mb-2">Term Name *</label>
                <select id="term_name" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">Select term</option>
                    @foreach (['First Term', 'Second Term', 'Third Term', 'Autumn', 'Spring', 'Summer'] as $termName)
                        <option value="{{ $termName }}" {{ old('name', $term->name) === $termName ? 'selected' : '' }}>{{ $termName }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="term_start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                <input type="date" id="term_start_date" name="start_date" value="{{ old('start_date', $term->start_date?->format('Y-m-d')) }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label for="term_end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                <input type="date" id="term_end_date" name="end_date" value="{{ old('end_date', $term->end_date?->format('Y-m-d')) }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label for="next_term_begins" class="block text-sm font-medium text-gray-700 mb-2">Next Term Begins *</label>
                <input type="date" id="next_term_begins" name="next_term_begins" value="{{ old('next_term_begins', $term->next_term_begins?->format('Y-m-d')) }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div class="md:col-span-5 flex justify-end mt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection