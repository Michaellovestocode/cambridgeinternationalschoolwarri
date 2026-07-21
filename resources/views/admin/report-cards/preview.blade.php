@extends('layouts.app')

@section('title', 'Report Card Preview')

@section('content')
@php
    $seniorStaffTitle = $reportCard->class?->reportAuthorityTitle() ?? 'Head Teacher';
    $seniorAuthority = \App\Models\User::where('report_authority_role', $reportCard->class?->reportAuthorityRole() ?? 'head_teacher')
        ->whereIn('role', ['admin', 'teacher'])
        ->orderBy('name')
        ->first();
@endphp
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Report Card Details</h1>
            <p class="text-gray-600 mt-1">
                Fill attendance, remarks, and signatures. Next term date is set by admin in the Term settings. Attendance percentage is calculated automatically.
            </p>
            <p class="mt-2">
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $reportCard->isPublished() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ $reportCard->isPublished() ? 'Published, fee clearance required' : 'Hidden from parents and students' }}
                </span>
                <span class="ml-2 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                    {{ $reportCard->workflowLabel() }}
                </span>
                @if($reportCard->review_required)
                    <span class="ml-2 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                        Needs Review After Score Update
                    </span>
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.report-cards') }}"
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-medium">
                Back
            </a>
            <a href="{{ route('admin.report-cards.visual-preview', $reportCard->id) }}" target="_blank"
               class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg font-medium">
                Visual Preview
            </a>
            <a href="{{ route('admin.report-cards.visual-preview', $reportCard->id) }}" target="_blank"
               class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium">
                Print
            </a>
            @if($canSubmitForReview)
            <form method="POST" action="{{ route('admin.report-cards.submit-review', $reportCard->id) }}">
                @csrf
                @method('PUT')
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium">
                    Submit for Review
                </button>
            </form>
            @endif
            @if(auth()->user()->canReviewReportCards() && $reportCard->isSubmittedForReview())
            <form method="POST" action="{{ route('admin.report-cards.approve-review', $reportCard->id) }}" class="flex gap-2">
                @csrf
                @method('PUT')
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium">
                    Approve Academic Review
                </button>
                <button type="submit" name="bypass_missing_scores" value="true" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium">
                    Approve Anyway (Bypass Missing Scores)
                </button>
            </form>
            @endif
            @if(auth()->user()->isAdmin())
            <form method="POST" action="{{ route('admin.report-cards.publication', $reportCard->id) }}" class="flex flex-wrap gap-2">
                @csrf
                @method('PUT')
                <input type="hidden" name="published" value="{{ $reportCard->isPublished() ? 0 : 1 }}">
                <button type="submit"
                        class="{{ $reportCard->isPublished() ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg font-medium">
                    {{ $reportCard->isPublished() ? 'Hide From Parents & Students' : 'Publish Report Card' }}
                </button>
            </form>
            @endif
            <a href="{{ route('admin.report-cards.download', $reportCard->id) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                Download PDF
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($reportCard->isRejectedByReviewer() && $reportCard->academic_rejection_reason)
        <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded-lg">
            <p class="font-semibold">Reviewer rejection note</p>
            <p class="mt-1 text-sm">{{ $reportCard->academic_rejection_reason }}</p>
        </div>
    @endif

    @if(auth()->user()->canReviewReportCards() && $reportCard->isSubmittedForReview())
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Reject Report Card</h2>
            <form method="POST" action="{{ route('admin.report-cards.reject-review', $reportCard->id) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <textarea name="academic_rejection_reason" rows="3" required class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Explain what the form teacher should correct.">{{ old('academic_rejection_reason') }}</textarea>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-3 rounded-lg font-medium">
                    Reject and Notify Form Teacher
                </button>
            </form>
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

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white rounded-xl shadow p-6 space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Student Summary</h2>
            <div class="space-y-2 text-sm">
                <p><span class="font-semibold text-gray-700">Student:</span> {{ $reportCard->student->name }}</p>
                <p><span class="font-semibold text-gray-700">Class:</span> {{ $reportCard->class->display_name }}</p>
                <p><span class="font-semibold text-gray-700">Session:</span> {{ $reportCard->session->name }}</p>
                <p><span class="font-semibold text-gray-700">Term:</span> {{ $reportCard->term->name }}</p>
                <p><span class="font-semibold text-gray-700">Next Term Begins:</span> 
                    <span class="text-blue-700 font-medium">{{ $reportCard->term->next_term_begins ? $reportCard->term->next_term_begins->format('l, d M Y') : 'Not set' }}</span>
                    <span class="text-xs text-gray-500 block">Set by admin in Term settings</span>
                </p>
                @php
                    $summarySubjectCount = isset($scores) ? $scores->count() : 0;
                    $summaryTotalScore = isset($scores) ? (float) $scores->sum('total') : (float) ($reportCard->total_score ?? 0);
                    $summaryAverage = $summarySubjectCount > 0 ? round($summaryTotalScore / $summarySubjectCount, 2) : 0;
                    $summaryGrade = $summarySubjectCount > 0 ? \App\Models\Subject::getGrade($summaryAverage) : ($reportCard->overall_grade ?? 'F9');
                @endphp
                <p><span class="font-semibold text-gray-700">Overall Grade:</span> {{ $summaryGrade }}</p>
                <p><span class="font-semibold text-gray-700">Average Score:</span> {{ number_format($summaryAverage, 1) }}%</p>
                {{-- Position is intentionally omitted from the report card performance summary --}}
                <p><span class="font-semibold text-gray-700">Last Score Update:</span> {{ $reportCard->scores_updated_at?->format('d M Y, H:i') ?? 'Not recorded' }}</p>
                <p><span class="font-semibold text-gray-700">Workflow:</span> {{ $reportCard->workflowLabel() }}</p>
                <p><span class="font-semibold text-gray-700">Submitted:</span> {{ $reportCard->submitted_for_review_at?->format('d M Y, H:i') ?? '-' }}</p>
                <p><span class="font-semibold text-gray-700">Academic Reviewer:</span> {{ $reportCard->academicReviewer?->name ?? '-' }}</p>
                <p><span class="font-semibold text-gray-700">Academic Reviewed:</span> {{ $reportCard->academic_reviewed_at?->format('d M Y, H:i') ?? '-' }}</p>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Subject Scores</h3>
                @if($canEditScores)
                <form method="POST" action="{{ route('admin.report-cards.scores', $reportCard->id) }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3 max-h-[34rem] overflow-y-auto pr-1">
                        @foreach ($scores as $score)
                            <div class="border border-gray-200 rounded-lg p-3 space-y-3">
                                <input type="hidden" name="scores[{{ $loop->index }}][id]" value="{{ $score->id }}">
                                <p class="font-medium text-gray-900">{{ $score->subject->name }}</p>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">CA1</label>
                                        <input type="number" step="0.01" min="0" max="30" name="scores[{{ $loop->index }}][ca1]" value="{{ old('scores.' . $loop->index . '.ca1', $score->ca1) }}" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">CA2</label>
                                        <input type="number" step="0.01" min="0" max="10" name="scores[{{ $loop->index }}][ca2]" value="{{ old('scores.' . $loop->index . '.ca2', $score->ca2) }}" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Exam</label>
                                        <input type="number" step="0.01" min="0" max="60" name="scores[{{ $loop->index }}][exam]" value="{{ old('scores.' . $loop->index . '.exam', $score->exam) }}" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm">
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600">
                                    Current Total: {{ number_format($score->total, 1) }} | Grade: {{ $score->grade }} | Remark: {{ $score->remark }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg font-medium">
                        Save Edited Scores
                    </button>
                </form>
                @else
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @foreach ($scores as $score)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <p class="font-medium text-gray-900">{{ $score->subject->name }}</p>
                            <p class="text-sm text-gray-600">
                                Total: {{ number_format($score->total, 1) }} | Grade: {{ $score->grade }} | Remark: {{ $score->remark }}
                            </p>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl shadow p-6">
            <form action="{{ route('admin.report-cards.update', $reportCard->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Appearance</h2>
                    <div>
                        <label for="theme_color" class="block text-sm font-medium text-gray-700 mb-2">Theme Color</label>
                        <select id="theme_color" name="theme_color" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                            @foreach ($colors as $color)
                                <option value="{{ $color }}" {{ old('theme_color', $reportCard->theme_color ?? 'blue') === $color ? 'selected' : '' }}>
                                    {{ ucfirst($color) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Attendance</h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label for="days_school_opened" class="block text-sm font-medium text-gray-700 mb-2">Days School Opened</label>
                            <input id="days_school_opened" name="days_school_opened" type="number" min="0"
                                   value="{{ old('days_school_opened', $reportCard->days_school_opened) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                        <div>
                            <label for="days_present" class="block text-sm font-medium text-gray-700 mb-2">Days Present</label>
                            <input id="days_present" name="days_present" type="number" min="0"
                                   value="{{ old('days_present', $reportCard->days_present) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                        <div>
                            <label for="days_absent" class="block text-sm font-medium text-gray-700 mb-2">Days Absent</label>
                            <input id="days_absent" name="days_absent" type="number" min="0" readonly
                                   value="{{ old('days_absent', $reportCard->days_absent) }}"
                                   class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                            <p class="text-xs text-gray-500 mt-1">Automatically calculated as Days School Opened minus Days Present.</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        Attendance percentage will be calculated automatically from days present and days school opened.
                    </p>
                </div>

                @php
                    $affectiveTraits = [
                        'punctuality' => 'Punctuality',
                        'neatness' => 'Neatness',
                        'politeness' => 'Politeness',
                        'attentiveness' => 'Attentiveness',
                        'self_control' => 'Self Control',
                        'sense_of_responsibility' => 'Sense of Responsibility',
                    ];
                    $psychomotorTraits = [
                        'handwriting' => 'Handwriting',
                        'drawing_painting' => 'Drawing/Painting',
                        'craft_work' => 'Craft Work',
                        'speech_fluency' => 'Speech Fluency',
                        'sports_games' => 'Sports & Games',
                        'music' => 'Music',
                    ];
                    $ratingOptions = [
                        5 => '5 - Excellent',
                        4 => '4 - Good',
                        3 => '3 - Average',
                        2 => '2 - Fair',
                        1 => '1 - Needs Improvement',
                    ];
                @endphp

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Behaviour & Skills Ratings</h2>
                    <p class="text-sm text-gray-500 mb-4">
                        These ratings appear in the Affective Domain and Psychomotor Skills tables on the visual report card.
                    </p>
                    <div class="grid lg:grid-cols-2 gap-6">
                        <div class="border border-gray-200 rounded-xl p-4">
                            <h3 class="font-semibold text-gray-900 mb-4">Affective Domain</h3>
                            <div class="space-y-3">
                                @foreach ($affectiveTraits as $key => $label)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1" for="affective_domain_{{ $key }}">{{ $label }}</label>
                                        <select id="affective_domain_{{ $key }}" name="affective_domain[{{ $key }}]" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                            <option value="">Not Rated</option>
                                            @foreach ($ratingOptions as $value => $text)
                                                <option value="{{ $value }}" {{ (string) old("affective_domain.$key", data_get($reportCard->affective_domain, $key)) === (string) $value ? 'selected' : '' }}>
                                                    {{ $text }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-xl p-4">
                            <h3 class="font-semibold text-gray-900 mb-4">Psychomotor Skills</h3>
                            <div class="space-y-3">
                                @foreach ($psychomotorTraits as $key => $label)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1" for="psychomotor_skills_{{ $key }}">{{ $label }}</label>
                                        <select id="psychomotor_skills_{{ $key }}" name="psychomotor_skills[{{ $key }}]" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                            <option value="">Not Rated</option>
                                            @foreach ($ratingOptions as $value => $text)
                                                <option value="{{ $value }}" {{ (string) old("psychomotor_skills.$key", data_get($reportCard->psychomotor_skills, $key)) === (string) $value ? 'selected' : '' }}>
                                                    {{ $text }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-900">Form Teacher Section</h2>
                        <div>
                            <label for="class_teacher_comment" class="block text-sm font-medium text-gray-700 mb-2">Form Teacher's Remark</label>
                            <textarea id="class_teacher_comment" name="class_teacher_comment" rows="4"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3">{{ old('class_teacher_comment', $reportCard->class_teacher_comment) }}</textarea>
                        </div>
                        <div>
                            <label for="class_teacher_name" class="block text-sm font-medium text-gray-700 mb-2">Form Teacher's Name</label>
                            <input id="class_teacher_name" name="class_teacher_name" type="text"
                                   value="{{ old('class_teacher_name', $reportCard->class_teacher_name) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                        <div>
                            <label for="class_teacher_signature" class="block text-sm font-medium text-gray-700 mb-2">Form Teacher's Signature</label>
                            <input id="class_teacher_signature" name="class_teacher_signature" type="text"
                                   value="{{ old('class_teacher_signature', $reportCard->class_teacher_signature) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-900">{{ $seniorStaffTitle }} Section</h2>
                        <div>
                            <label for="head_teacher_comment" class="block text-sm font-medium text-gray-700 mb-2">{{ $seniorStaffTitle }}'s Remark</label>
                            <textarea id="head_teacher_comment" name="head_teacher_comment" rows="4"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3">{{ old('head_teacher_comment', $reportCard->head_teacher_comment) }}</textarea>
                        </div>
                        <div>
                            <label for="head_teacher_name" class="block text-sm font-medium text-gray-700 mb-2">{{ $seniorStaffTitle }}'s Name</label>
                            <input id="head_teacher_name" name="head_teacher_name" type="text"
                                   value="{{ old('head_teacher_name', $reportCard->head_teacher_name ?: $seniorAuthority?->name) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        </div>
                        <div>
                            <label for="head_teacher_signature" class="block text-sm font-medium text-gray-700 mb-2">{{ $seniorStaffTitle }}'s Signature</label>
                            <input id="head_teacher_signature" name="head_teacher_signature" type="text"
                                   value="{{ old('head_teacher_signature', $reportCard->head_teacher_signature) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                            @if($seniorAuthority?->signature)
                                <p class="text-xs text-gray-500 mt-1">{{ $seniorStaffTitle }} signature image will be used automatically from {{ $seniorAuthority->name }}'s staff profile.</p>
                            @endif
                        </div>
                        @if(auth()->user()->isAdmin())
                        <div>
                            <label for="principal_signature_image" class="block text-sm font-medium text-gray-700 mb-2">Principal Signature Image</label>
                            @if($schoolSettings->principal_signature)
                                <div class="mb-2 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <img src="{{ asset('storage/' . $schoolSettings->principal_signature) }}" alt="Principal Signature" class="h-16 w-48 object-contain">
                                </div>
                            @endif
                            <input id="principal_signature_image" name="principal_signature_image" type="file" accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3">
                            <p class="text-xs text-gray-500 mt-1">This is saved school-wide and used on report cards.</p>
                            @error('principal_signature_image')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        @endif
                    </div>
                </div>

                 <div class="flex justify-end">
                     <button type="submit"
                             class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium">
                         Save Report Card Details
                     </button>
                 </div>
             </form>
         </div>
     </div>
 </div>
@endsection
