@extends('layouts.app')

@section('title', 'Manage Sessions & Terms')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Sessions & Terms</h1>
            <p class="text-gray-600 mt-1">
                Set academic sessions, terms, and the "Next Term Begins" date for report cards.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Sessions Section -->
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Academic Sessions</h2>
        
        <form method="POST" action="{{ route('admin.academic-sessions.store') }}" class="grid md:grid-cols-4 gap-4 mb-6">
            @csrf
            <div>
                <label for="session_name" class="block text-sm font-medium text-gray-700 mb-2">Session Name *</label>
                <input type="text" id="session_name" name="name" value="{{ old('name') }}" required
                       placeholder="e.g., 2024/2025"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>
            <div>
                <label for="session_start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                <input type="date" id="session_start_date" name="start_date" value="{{ old('start_date') }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>
            <div>
                <label for="session_end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                <input type="date" id="session_end_date" name="end_date" value="{{ old('end_date') }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium">
                    Add Session
                </button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Start Date</th>
                        <th class="px-4 py-3 text-left">End Date</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr class="border-t border-gray-200">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $session->name }}</td>
                            <td class="px-4 py-3">{{ $session->start_date ? $session->start_date->format('d M Y') : 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $session->end_date ? $session->end_date->format('d M Y') : 'N/A' }}</td>
                            <td class="px-4 py-3">
                                @if($session->is_active)
                                    <span class="px-3 py-1 rounded-full text-xs bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if(!$session->is_active)
                                    <form method="POST" action="{{ route('admin.academic-sessions.activate', $session->id) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1 rounded-lg text-xs font-medium">
                                            Activate
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.academic-sessions.delete', $session->id) }}" class="inline" onsubmit="return confirm('Delete this session? All associated terms will be deleted.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded-lg text-xs font-medium">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No sessions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Terms Section -->
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Terms</h2>
        
        <form method="POST" action="{{ route('admin.terms.store') }}" class="grid md:grid-cols-5 gap-4 mb-6">
            @csrf
            <div>
                <label for="term_session_id" class="block text-sm font-medium text-gray-700 mb-2">Session *</label>
                <select id="term_session_id" name="session_id" required class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">Select session</option>
                    @foreach ($sessions as $session)
                        <option value="{{ $session->id }}" {{ old('session_id') == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="term_name" class="block text-sm font-medium text-gray-700 mb-2">Term Name *</label>
                <select id="term_name" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">Select term</option>
                    <option value="First Term" {{ old('name') == 'First Term' ? 'selected' : '' }}>First Term</option>
                    <option value="Second Term" {{ old('name') == 'Second Term' ? 'selected' : '' }}>Second Term</option>
                    <option value="Third Term" {{ old('name') == 'Third Term' ? 'selected' : '' }}>Third Term</option>
                </select>
            </div>
            <div>
                <label for="term_start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                <input type="date" id="term_start_date" name="start_date" value="{{ old('start_date') }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>
            <div>
                <label for="term_end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                <input type="date" id="term_end_date" name="end_date" value="{{ old('end_date') }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>
            <div>
                <label for="next_term_begins" class="block text-sm font-medium text-gray-700 mb-2">Next Term Begins *</label>
                <input type="date" id="next_term_begins" name="next_term_begins" value="{{ old('next_term_begins') }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>
            <div class="md:col-span-5 flex items-end">
                <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                    Add Term
                </button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="px-4 py-3 text-left">Term</th>
                        <th class="px-4 py-3 text-left">Session</th>
                        <th class="px-4 py-3 text-left">Start Date</th>
                        <th class="px-4 py-3 text-left">End Date</th>
                        <th class="px-4 py-3 text-left">Next Term Begins</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($terms as $term)
                        <tr class="border-t border-gray-200">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $term->name }}</td>
                            <td class="px-4 py-3">{{ $term->session->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $term->start_date ? $term->start_date->format('d M Y') : 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $term->end_date ? $term->end_date->format('d M Y') : 'N/A' }}</td>
                            <td class="px-4 py-3">
                                @if($term->next_term_begins)
                                    <span class="font-medium text-blue-700">{{ $term->next_term_begins->format('l, d M Y') }}</span>
                                @else
                                    <span class="text-gray-400">Not set</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($term->is_active)
                                    <span class="px-3 py-1 rounded-full text-xs bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if(!$term->is_active)
                                    <form method="POST" action="{{ route('admin.terms.activate', $term->id) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1 rounded-lg text-xs font-medium">
                                            Activate
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.terms.delete', $term->id) }}" class="inline" onsubmit="return confirm('Delete this term? All associated report cards will be deleted.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded-lg text-xs font-medium">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No terms found. Add a session first, then add terms.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Information Box -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-semibold text-blue-900 mb-2">How "Next Term Begins" Works</h3>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>• The "Next Term Begins" date is set by the admin when creating or editing a term.</li>
            <li>• This date automatically appears on all report cards for that term.</li>
            <li>• The form teacher no longer needs to fill this field - it's managed centrally by the admin.</li>
            <li>• To change the date, edit the term from the Terms table above.</li>
        </ul>
    </div>
</div>
@endsection