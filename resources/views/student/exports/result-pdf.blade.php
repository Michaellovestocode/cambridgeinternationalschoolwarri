<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Exam Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #16a34a;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #16a34a;
            margin: 0;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-section td {
            padding: 5px;
        }
        .score-box {
            background: {{ $attempt->total_score >= $attempt->exam->pass_mark ? '#dcfce7' : '#fee2e2' }};
            border: 2px solid {{ $attempt->total_score >= $attempt->exam->pass_mark ? '#16a34a' : '#dc2626' }};
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .score-box .score {
            font-size: 36px;
            font-weight: bold;
            color: {{ $attempt->total_score >= $attempt->exam->pass_mark ? '#16a34a' : '#dc2626' }};
        }
        .question {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .question-header {
            background: #f3f4f6;
            padding: 10px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .answer-box {
            background: #f9fafb;
            padding: 10px;
            margin: 10px 0;
            border-left: 3px solid #3b82f6;
        }
        .correct-answer {
            background: #dcfce7;
            border-left-color: #16a34a;
        }
        .feedback {
            background: #dbeafe;
            padding: 10px;
            margin-top: 10px;
            border-left: 3px solid #3b82f6;
        }
        .marks {
            font-weight: bold;
            color: #16a34a;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎓 Nigerian CBT System</h1>
        <h2>{{ $attempt->exam->title }}</h2>
        <p>{{ $attempt->exam->subject }}</p>
    </div>

    <div class="info-section">
        <table>
            <tr>
                <td><strong>Student Name:</strong></td>
                <td>{{ $attempt->user->name }}</td>
                <td><strong>Registration:</strong></td>
                <td>{{ $attempt->user->registration_number }}</td>
            </tr>
            <tr>
                <td><strong>Date Submitted:</strong></td>
                <td>{{ $attempt->submitted_at->format('d M Y, h:i A') }}</td>
                <td><strong>Duration:</strong></td>
                <td>{{ $attempt->exam->duration_minutes }} minutes</td>
            </tr>
        </table>
    </div>

    <div class="score-box">
        <div class="score">{{ $attempt->total_score }}/{{ $attempt->exam->total_marks }}</div>
        <div style="font-size: 18px; margin-top: 10px;">
            @if($attempt->total_score >= $attempt->exam->pass_mark)
                ✓ PASSED (Pass mark: {{ $attempt->exam->pass_mark }})
            @else
                ✗ FAILED (Pass mark: {{ $attempt->exam->pass_mark }})
            @endif
        </div>
        <div style="margin-top: 10px;">
            Percentage: {{ round(($attempt->total_score / $attempt->exam->total_marks) * 100, 1) }}%
        </div>
    </div>

    <h3>Score Breakdown</h3>
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td><strong>Objective Score:</strong></td>
            <td>{{ $attempt->objective_score ?? 0 }}</td>
            <td><strong>Subjective Score:</strong></td>
            <td>{{ $attempt->subjective_score ?? 0 }}</td>
        </tr>
    </table>

    <h3>Detailed Review</h3>
    @foreach($attempt->answers as $index => $answer)
    @php
        $question = $answer->question;
    @endphp
    <div class="question">
        <div class="question-header">
            Question {{ $index + 1 }} - {{ ucwords(str_replace('_', ' ', $question->question_type)) }} ({{ $question->marks }} marks)
        </div>
        <p><strong>{!! $question->formattedQuestionText() !!}</strong></p>

        @if($answer->answer_text)
        <div class="answer-box">
            <strong>Your Answer:</strong><br>
            {{ $answer->answer_text }}
        </div>
        @else
        <div class="answer-box" style="border-left-color: #dc2626; background: #fee2e2;">
            <strong>Not Answered</strong>
        </div>
        @endif

        @if($question->question_type === 'multiple_choice' || $question->question_type === 'fill_blank')
        <div class="answer-box correct-answer">
            <strong>Correct Answer:</strong> {{ $question->correct_answer }}
        </div>
        @endif

        <p class="marks">Marks Obtained: {{ $answer->marks_obtained ?? 0 }}/{{ $question->marks }}</p>

        @if($answer->feedback)
        <div class="feedback">
            <strong>Teacher's Feedback:</strong><br>
            {{ $answer->feedback }}
        </div>
        @endif
    </div>
    @endforeach

    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }}<br>
        Nigerian CBT System - Exam Results
    </div>
</body>
</html>
