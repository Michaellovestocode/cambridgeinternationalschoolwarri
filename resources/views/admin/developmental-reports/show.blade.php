<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developmental Report - {{ $developmentalReport->student->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e5e7eb; color: #0f172a; font-family: Arial, Helvetica, sans-serif; overflow-x: hidden; }
        .toolbar { max-width: 940px; margin: 18px auto; display: flex; gap: 10px; justify-content: flex-end; }
        .toolbar a, .toolbar button { border: 0; border-radius: 8px; padding: 10px 14px; font-weight: 700; text-decoration: none; cursor: pointer; font-size: 13px; }
        .toolbar a { background: #0f172a; color: #fff; }
        .toolbar button { background: #059669; color: #fff; }
        .page { width: 210mm; min-height: 297mm; margin: 0 auto 24px; background: #fff; padding: 9mm; border: 3px solid #0f2d6b; }
        .header-table { width: 100%; border-collapse: collapse; }
        .logo-cell, .photo-cell { width: 86px; vertical-align: top; }
        .logo, .photo { width: 78px; height: 78px; object-fit: cover; border: 1px solid #d1d5db; }
        .logo { border-radius: 10px; }
        .photo { border-radius: 6px; }
        .school { text-align: center; vertical-align: top; }
        .school h1 { margin: 0; color: #12307c; font-size: 28px; line-height: 1; letter-spacing: .3px; }
        .school h2 { margin: 5px 0 0; color: #12307c; font-size: 16px; }
        .school p { margin: 5px 0 0; color: #12307c; font-size: 11px; font-weight: 700; }
        .title { margin-top: 9px; color: #9f1239; text-align: center; font-size: 17px; font-weight: 900; text-transform: uppercase; }
        .meta { margin-top: 14px; border: 1.5px solid #0f2d6b; border-collapse: collapse; width: 100%; font-size: 12px; }
        .meta td { padding: 7px 9px; border-bottom: 1px solid #cbd5e1; }
        .meta strong { color: #0f2d6b; }
        .note { margin: 12px 0; text-align: center; font-size: 11px; font-weight: 800; }
        .band { margin: 0 auto 10px; width: 72%; border-radius: 5px; background: #22c55e; color: #052e16; padding: 8px 12px; text-align: center; font-size: 12px; font-weight: 900; text-transform: uppercase; }
        .scale { margin: 0 auto 12px; width: 360px; border-collapse: collapse; font-size: 11px; }
        .scale td { border: 1px solid #334155; padding: 5px 8px; }
        .scale td:first-child { width: 48px; text-align: center; font-weight: 900; }
        .section-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .skill-table { width: 100%; border-collapse: collapse; font-size: 10.5px; page-break-inside: avoid; }
        .skill-table th { background: #f1f5f9; color: #0f172a; border: 1px solid #64748b; padding: 6px 5px; text-transform: uppercase; }
        .skill-table td { border: 1px solid #94a3b8; padding: 5px; }
        .skill-table .skill { width: 62%; }
        .rating-cell { width: 7.6%; text-align: center; color: #0f4ab5; font-size: 15px; font-weight: 900; }
        .remarks { margin-top: 10px; width: 100%; border-collapse: collapse; font-size: 11px; }
        .remarks td { border: 1px solid #64748b; padding: 7px; vertical-align: top; }
        .remarks .label { width: 26%; font-weight: 900; text-transform: uppercase; }
        .signature { width: 150px; min-height: 42px; text-align: center; }
        .signature img { max-width: 135px; max-height: 42px; object-fit: contain; }
        .muted { color: #64748b; }
        @media screen and (max-width: 760px) {
            body { background: #fff; }
            .toolbar { max-width: none; margin: 0; padding: 10px; flex-wrap: wrap; justify-content: flex-start; background: #f8fafc; border-bottom: 1px solid #cbd5e1; }
            .toolbar a, .toolbar button { padding: 9px 11px; font-size: 12px; }
            .page { width: calc(100vw - 16px); min-height: auto; margin: 8px auto 16px; padding: 10px; border-width: 2px; }
            .logo-cell, .photo-cell { width: 54px; }
            .logo, .photo { width: 48px; height: 48px; }
            .school h1 { font-size: 15px; line-height: 1.12; }
            .school h2 { font-size: 9px; margin-top: 3px; }
            .school p { font-size: 8px; margin-top: 3px; }
            .title { margin-top: 5px; font-size: 10px; }
            .meta { margin-top: 8px; font-size: 9px; table-layout: fixed; }
            .meta td { padding: 5px 4px; overflow-wrap: anywhere; }
            .band { width: 100%; font-size: 9px; padding: 6px; }
            .scale { width: 100%; font-size: 9px; }
            .section-grid { grid-template-columns: 1fr; gap: 6px; }
            .skill-table { font-size: 9px; }
            .skill-table th, .skill-table td { padding: 4px 3px; }
            .skill-table .skill { width: 52%; }
            .rating-cell { font-size: 12px; }
            .remarks { font-size: 9px; table-layout: fixed; }
            .remarks td { padding: 5px; overflow-wrap: anywhere; }
            .remarks .label { width: 31%; }
            .signature { width: 64px; min-height: 32px; }
            .signature img { max-width: 58px; max-height: 30px; }
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .page { margin: 0; border: 3px solid #0f2d6b; width: 100%; min-height: auto; }
        }
    </style>
</head>
<body>
    @if(($renderMode ?? 'browser') === 'browser')
        <div class="toolbar">
            <a href="{{ route('admin.developmental-reports.index', ['class_id' => $developmentalReport->class_id, 'session_id' => $developmentalReport->session_id, 'term_id' => $developmentalReport->term_id]) }}">Back</a>
            <a href="{{ route('admin.developmental-reports.edit', ['student' => $developmentalReport->student_id, 'session_id' => $developmentalReport->session_id, 'term_id' => $developmentalReport->term_id]) }}">Edit</a>
            <a href="{{ route('admin.developmental-reports.download', $developmentalReport) }}">Download PDF</a>
            <button type="button" onclick="window.print()">Print</button>
        </div>

        @if(auth()->check() && auth()->user()->isAdmin() && ! $developmentalReport->isPublished())
            <form method="POST" action="{{ route('admin.developmental-reports.publish', $developmentalReport) }}" class="toolbar" style="margin-top:-8px;">
                @csrf
                @method('PUT')
                <input name="authority_remark" value="{{ $developmentalReport->authority_remark }}" placeholder="{{ $developmentalReport->authorityTitle() }} remark" style="flex:1; border:1px solid #cbd5e1; border-radius:8px; padding:10px 12px;">
                <button type="submit">Publish</button>
            </form>
        @endif
    @endif

    @php
        $student = $developmentalReport->student;
        $isBrowser = ($renderMode ?? 'browser') === 'browser';

        if ($isBrowser) {
            $logo = $schoolSettings->school_logo ? asset('storage/' . $schoolSettings->school_logo) : asset('images/schoollogo.jpg');
            $photo = $student->photo ? asset('storage/' . $student->photo) : null;
            $formSignature = $developmentalReport->form_teacher_signature ? asset('storage/' . $developmentalReport->form_teacher_signature) : null;
            $authoritySignature = $developmentalReport->authority_signature ? asset('storage/' . $developmentalReport->authority_signature) : null;
        } else {
            $logoPath = $schoolSettings->school_logo ? public_path('storage/' . $schoolSettings->school_logo) : null;
            $photoPath = $student->photo ? public_path('storage/' . $student->photo) : null;
            $formSignaturePath = $developmentalReport->form_teacher_signature ? public_path('storage/' . $developmentalReport->form_teacher_signature) : null;
            $authoritySignaturePath = $developmentalReport->authority_signature ? public_path('storage/' . $developmentalReport->authority_signature) : null;

            $logo = ($logoPath && file_exists($logoPath)) ? $logoPath : public_path('images/schoollogo.jpg');
            $photo = ($photoPath && file_exists($photoPath)) ? $photoPath : null;
            $formSignature = ($formSignaturePath && file_exists($formSignaturePath)) ? $formSignaturePath : null;
            $authoritySignature = ($authoritySignaturePath && file_exists($authoritySignaturePath)) ? $authoritySignaturePath : null;
        }
    @endphp

    <main class="page">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if($logo)
                        <img src="{{ $logo }}" class="logo" alt="School Logo">
                    @endif
                </td>
                <td class="school">
                    <h1>{{ strtoupper($schoolSettings->school_name ?: 'Cambridge International School') }}</h1>
                    <h2>CRECHE, NURSERY, PRIMARY & SECONDARY</h2>
                    <p>{{ strtoupper($schoolSettings->school_address ?: 'Delta State, Nigeria') }}</p>
                    <div class="title">Pupil's Developmental Progress Report</div>
                </td>
                <td class="photo-cell">
                    @if($photo)
                        <img src="{{ $photo }}" class="photo" alt="{{ $student->name }}">
                    @endif
                </td>
            </tr>
        </table>

        <table class="meta">
            <tr>
                <td><strong>Pupil's Name:</strong> {{ strtoupper($student->name) }}</td>
                <td><strong>Session:</strong> {{ $developmentalReport->session->name }}</td>
                <td><strong>Term:</strong> {{ strtoupper($developmentalReport->term->name) }}</td>
            </tr>
            <tr>
                <td><strong>Admission Number:</strong> {{ $student->registration_number ?: 'N/A' }}</td>
                <td><strong>Gender:</strong> {{ $student->sex ? ucfirst($student->sex) : 'N/A' }}</td>
                <td><strong>Age:</strong> {{ $student->age ? $student->age . 'yrs' : 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Class:</strong> {{ $developmentalReport->class->display_name }}</td>
                <td><strong>No. of Times School Opened:</strong> {{ $developmentalReport->days_school_opened ?? 'N/A' }}</td>
                <td><strong>No. of Times Absent:</strong> {{ $developmentalReport->days_absent ?? 'N/A' }}</td>
            </tr>
        </table>

        <div class="band">Reception and Pre-Kindergarten's Developmental Progress Report</div>

        <table class="scale">
            @foreach($ratingLabels as $rating => $label)
                <tr>
                    <td>{{ $rating }}</td>
                    <td>{{ $label }}</td>
                </tr>
            @endforeach
        </table>

        <div class="section-grid">
            @foreach($skillsBySection as $section => $skills)
                <table class="skill-table">
                    <thead>
                        <tr>
                            <th class="skill">{{ $section }}</th>
                            @foreach(array_keys($ratingLabels) as $rating)
                                <th>{{ $rating }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($skills as $skill)
                            <tr>
                                <td class="skill">{{ $skill->name }}</td>
                                @foreach(array_keys($ratingLabels) as $rating)
                                    <td class="rating-cell">{!! $ratings->get($skill->id) === $rating ? '&#10003;' : '' !!}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>

        <table class="remarks">
            <tr>
                <td class="label">Class Teacher's Remark</td>
                <td>{{ $developmentalReport->class_teacher_remark ?: '............................................................' }}</td>
                <td class="signature" rowspan="2">
                    @if($formSignature)
                        <img src="{{ $formSignature }}" alt="Form Teacher Signature">
                    @else
                        <span class="muted">Signature</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Class Teacher's Name</td>
                <td><strong>{{ $developmentalReport->form_teacher_name ?: $developmentalReport->class?->activeFormTeacher?->teacher?->name ?: '................................' }}</strong></td>
            </tr>
            <tr>
                <td class="label">{{ $developmentalReport->authorityTitle() }}'s Remark</td>
                <td>{{ $developmentalReport->authority_remark ?: '............................................................' }}</td>
                <td class="signature" rowspan="2">
                    @if($authoritySignature)
                        <img src="{{ $authoritySignature }}" alt="{{ $developmentalReport->authorityTitle() }} Signature">
                    @else
                        <span class="muted">Signature</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">{{ $developmentalReport->authorityTitle() }}'s Name</td>
                <td><strong>{{ $developmentalReport->authority_name ?: '................................' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Examination Date</td>
                <td colspan="2">{{ optional($developmentalReport->published_at ?: now())->format('F jS, Y') }}</td>
            </tr>
        </table>
    </main>
</body>
</html>
