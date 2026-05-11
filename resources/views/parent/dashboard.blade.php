@extends('layouts.app')

@section('title', 'Parent Portal')

@section('content')
<div class="space-y-6">
    @php
        $unreadMessagesCount = auth()->user()->receivedMessages()->whereNull('read_at')->count();
    @endphp
    <div class="bg-white rounded-3xl shadow-lg p-6">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Parent Portal</h1>
                <p class="text-sm text-gray-500">Monitor {{ $notifications['children'] }} child(ren), upcoming exam dates, and completed exam activity in one place.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('parent.messages.index') }}" class="px-4 py-2 rounded-full text-sm font-semibold bg-rose-50 text-rose-700">
                    Messages {{ $unreadMessagesCount > 0 ? '(' . $unreadMessagesCount . ' new)' : '' }}
                </a>
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-blue-50 text-blue-600">Children {{ $notifications['children'] }}</span>
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-amber-50 text-amber-700">Upcoming Exams {{ $notifications['upcomingExams'] }}</span>
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-green-50 text-green-600">New Results {{ $notifications['gradedAttempts'] }}</span>
                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-indigo-50 text-indigo-600">Report Cards {{ $notifications['reportCards'] }}</span>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        @forelse($children as $child)
            @php
                $overview = $childExamOverview[$child->id] ?? null;
                $nextExam = $overview['next_exam'] ?? null;
            @endphp
            <div class="bg-white rounded-3xl shadow-lg p-5 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $child->name }}</h2>
                        <p class="text-sm text-gray-500">{{ $child->class?->display_name ?? 'Not assigned yet' }}</p>
                    </div>
                    <span class="text-xs font-semibold text-gray-500 uppercase">{{ $overview['completed_count'] ?? 0 }} done</span>
                </div>

                @if($nextExam)
                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">{{ $nextExam->status_label }}</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $nextExam->title }}</p>
                        <p class="text-sm text-gray-600">{{ $nextExam->subject ?: 'General exam' }}</p>
                        <p class="mt-2 text-xs text-gray-500">
                            {{ $nextExam->start_date->format('D, M j, Y g:i A') }} to {{ $nextExam->end_date->format('D, M j, Y g:i A') }}
                        </p>
                    </div>
                @else
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-sm font-semibold text-gray-700">No active exam schedule right now.</p>
                        <p class="text-xs text-gray-500 mt-1">The next exam will appear here automatically once it is scheduled for this child&apos;s class.</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-blue-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-blue-600 font-semibold">Upcoming</p>
                        <p class="text-2xl font-black text-blue-900 mt-1">{{ $overview['upcoming_count'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-2xl bg-green-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-green-600 font-semibold">Completed</p>
                        <p class="text-2xl font-black text-green-900 mt-1">{{ $overview['completed_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="lg:col-span-3 bg-white rounded-3xl shadow-lg p-8 text-center text-gray-500">
                No children are linked to this parent account yet.
            </div>
        @endforelse
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Exam Schedule</h3>
                    <p class="text-sm text-gray-500">Parents can see dates and status only. Questions remain private to the student.</p>
                </div>
            </div>

            <div class="space-y-5">
                @forelse($children as $child)
                    @php
                        $schedule = $childExamOverview[$child->id]['schedule'] ?? collect();
                    @endphp
                    <div class="border border-gray-100 rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $child->name }}</p>
                                <p class="text-xs text-gray-500">{{ $child->class?->display_name ?? 'Unassigned' }}</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @forelse($schedule as $examItem)
                                <div class="rounded-2xl border border-gray-100 px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-gray-900">{{ $examItem->title }}</p>
                                        <span class="text-xs font-semibold px-3 py-1 rounded-full
                                            @if($examItem->status === 'graded') bg-green-50 text-green-700
                                            @elseif($examItem->status === 'submitted') bg-blue-50 text-blue-700
                                            @elseif($examItem->status === 'in_progress') bg-amber-50 text-amber-700
                                            @elseif($examItem->status === 'open') bg-orange-50 text-orange-700
                                            @elseif($examItem->status === 'missed') bg-rose-50 text-rose-700
                                            @else bg-gray-100 text-gray-700
                                            @endif">
                                            {{ $examItem->status_label }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $examItem->start_date->format('D, M j, Y g:i A') }} to {{ $examItem->end_date->format('D, M j, Y g:i A') }}
                                    </p>
                                    @if($examItem->attempt?->submitted_at)
                                        <p class="text-xs text-gray-500 mt-2">
                                            Submitted on {{ $examItem->attempt->submitted_at->format('M j, Y g:i A') }}
                                        </p>
                                    @elseif($examItem->attempt?->started_at)
                                        <p class="text-xs text-gray-500 mt-2">
                                            Started on {{ $examItem->attempt->started_at->format('M j, Y g:i A') }}
                                        </p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No exams scheduled yet for this child.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No children linked yet.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            <h3 class="text-xl font-bold text-gray-900">Completed Exam Activity</h3>
            <div class="space-y-4">
                @forelse($children as $child)
                    @php
                        $recentActivity = $childExamOverview[$child->id]['recent_activity'] ?? collect();
                    @endphp
                    <div class="border border-gray-100 rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $child->name }}</p>
                                <p class="text-xs text-gray-500">Recent exam outcomes</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @forelse($recentActivity as $examItem)
                                <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-gray-900">{{ $examItem->title }}</p>
                                        <span class="text-xs font-semibold text-gray-600">{{ $examItem->status_label }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ $examItem->start_date->format('M j, Y g:i A') }}</p>
                                    @if($examItem->status === 'graded' && $examItem->show_results_to_students && !is_null($examItem->attempt?->total_score))
                                        <p class="text-sm text-green-700 mt-2">Score released: {{ $examItem->attempt->total_score }} / {{ $examItem->total_marks }}</p>
                                    @elseif($examItem->status === 'graded')
                                        <p class="text-sm text-blue-700 mt-2">Child has completed this exam. The result has not been released yet.</p>
                                    @elseif($examItem->status === 'submitted')
                                        <p class="text-sm text-blue-700 mt-2">Child has completed this exam and the result is waiting for grading.</p>
                                    @elseif($examItem->status === 'missed')
                                        <p class="text-sm text-rose-700 mt-2">No attempt has been recorded for this exam.</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No completed exam activity yet.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No children linked yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            <h3 class="text-xl font-bold text-gray-900">Report Cards</h3>
            <div class="space-y-3">
                @forelse($reportCards as $reportCard)
                    <div class="border border-gray-100 rounded-2xl p-4 flex justify-between items-center gap-3">
                        <div>
                            <p class="text-sm text-gray-500">{{ $reportCard->session?->name ?? 'Session' }} - {{ $reportCard->term?->name ?? 'Term' }}</p>
                            <p class="font-semibold text-gray-900">{{ $reportCard->student->name ?? 'Student' }}</p>
                        </div>
                        <a href="{{ route('parent.report-cards.preview', $reportCard) }}" class="text-blue-600 font-semibold text-xs hover:underline">View</a>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No report cards are available yet. Published report cards appear here after fee clearance is approved for each child.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-6 space-y-4">
            <h3 class="text-xl font-bold text-gray-900">Communications</h3>
            <p class="text-sm text-gray-500">School messages, announcements, and admission updates.</p>
            <div class="space-y-3">
                @forelse($recentMessages as $message)
                    <div class="border border-blue-100 bg-blue-50 rounded-2xl p-4">
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $message->subject ?: 'School message' }}</p>
                                <p class="text-xs text-blue-700">{{ $message->sender?->name ?? 'School Admin' }} â€¢ {{ $message->created_at->format('M j, Y') }}</p>
                            </div>
                            @if($message->isUnread())
                                <span class="text-xs px-3 py-1 rounded-full bg-white text-blue-700 font-semibold">New</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-700 mt-2">{{ \Illuminate\Support\Str::limit($message->body, 180) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No school messages yet.</p>
                @endforelse

                <a href="{{ route('parent.messages.index') }}" class="inline-flex text-sm font-semibold text-blue-600 hover:underline">
                    Open all messages
                </a>

                <div class="border-t border-gray-100 pt-3"></div>

                @forelse($enquiries as $enquiry)
                    <div class="border border-gray-100 rounded-2xl p-4 flex justify-between items-start gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $enquiry->parent_name }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $enquiry->status)) }} • {{ $enquiry->created_at->format('M j, Y') }}</p>
                            <p class="text-sm text-gray-700 mt-2">{{ \Illuminate\Support\Str::limit($enquiry->message ?? 'No message provided.', 160) }}</p>
                        </div>
                        <span class="text-xs px-3 py-1 rounded-full bg-gray-100 text-gray-500">{{ $enquiry->phone }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No enquiries logged yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
