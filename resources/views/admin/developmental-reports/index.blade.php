@extends('layouts.app')

@section('title', 'Developmental Reports')

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl bg-gradient-to-r from-emerald-600 to-sky-600 p-6 text-white shadow-xl">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-white/75">Early Years</p>
                <h1 class="mt-2 text-3xl font-black">Pupil Developmental Reports</h1>
                <p class="mt-2 text-white/85">Form teachers fill developmental ratings for nursery learners.</p>
            </div>
            <a href="{{ route('admin.report-cards') }}" class="inline-flex rounded-2xl bg-white px-5 py-3 text-sm font-black text-sky-700 shadow-lg hover:bg-sky-50">Academic Reports</a>
        </div>
    </div>

    <div class="rounded-3xl bg-white p-5 shadow-lg">
        <form method="GET" action="{{ route('admin.developmental-reports.index') }}" class="grid gap-4 lg:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Class</label>
                <select name="class_id" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm">
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected((int) $selectedClassId === $class->id)>{{ $class->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Session</label>
                <select name="session_id" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm">
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" @selected((int) $selectedSessionId === $session->id)>{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Term</label>
                <select name="term_id" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm">
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" @selected((int) $selectedTermId === $term->id)>{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Search</label>
                <input name="search" value="{{ request('search') }}" placeholder="Learner name or number" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm">
            </div>
            <div class="flex items-end">
                <button class="w-full rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white hover:bg-slate-800">Filter</button>
            </div>
        </form>
    </div>

    <div class="rounded-3xl bg-white shadow-lg">
        <div class="border-b border-gray-100 px-6 py-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-black text-gray-900">Learners</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $students->count() }} learner{{ $students->count() === 1 ? '' : 's' }} found</p>
            </div>
            @if(auth()->user()->isAdmin() && $selectedClassId && $selectedSessionId && $selectedTermId && $publishableCount > 0)
                <form method="POST" action="{{ route('admin.developmental-reports.bulk-publish') }}" onsubmit="return confirm('Publish all submitted developmental reports for this class?')" class="flex items-center gap-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                    <input type="hidden" name="session_id" value="{{ $selectedSessionId }}">
                    <input type="hidden" name="term_id" value="{{ $selectedTermId }}">
                    <button type="submit" class="rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700">
                        Publish {{ $publishableCount }} Submitted
                    </button>
                </form>
            @endif
        </div>
        <div class="overflow-x-auto">
            @if($students->isNotEmpty())
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase text-gray-500">Learner</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase text-gray-500">Registration</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase text-gray-500">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-black uppercase text-gray-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($students as $student)
                            @php($report = $reports->get($student->id))
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($student->photo)
                                            <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->name }}" class="h-11 w-11 rounded-2xl object-cover">
                                        @else
                                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">{{ strtoupper(substr($student->name, 0, 1)) }}</div>
                                        @endif
                                        <div>
                                            <p class="font-black text-gray-900">{{ $student->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $student->class?->display_name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-600">{{ $student->registration_number ?: 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $report?->status === 'published' ? 'bg-green-100 text-green-700' : ($report?->status === 'submitted' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $report ? ucfirst($report->status) : 'Not started' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if($report)
                                            <a href="{{ route('admin.developmental-reports.show', $report) }}" class="rounded-xl bg-sky-100 px-4 py-2 text-sm font-black text-sky-700 hover:bg-sky-200">Preview</a>
                                        @endif
                                        <a href="{{ route('admin.developmental-reports.edit', ['student' => $student]) }}" class="rounded-xl bg-teal-600 px-4 py-2 text-sm font-black text-white hover:bg-teal-700">Development</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-6 py-12 text-center text-gray-500">
                    No early-years learners are available for this selection.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
