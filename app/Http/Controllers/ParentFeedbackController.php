<?php

namespace App\Http\Controllers;

use App\Mail\ParentFeedbackNotification;
use App\Mail\ParentFeedbackReceipt;
use App\Models\ParentFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ParentFeedbackController extends Controller
{
    public function create()
    {
        return view('feedback.create', ['categories' => ParentFeedback::categories()]);
    }

    public function store(Request $request)
    {
        $anonymous = $request->boolean('is_anonymous');
        $validated = $request->validate([
            'category' => ['required', 'string', 'in:' . implode(',', ParentFeedback::categories())],
            'is_anonymous' => ['nullable', 'boolean'],
            'parent_name' => [$anonymous ? 'nullable' : 'required', 'string', 'max:255'],
            'email' => [$anonymous ? 'nullable' : 'required_without:phone', 'nullable', 'email', 'max:255'],
            'phone' => [$anonymous ? 'nullable' : 'required_without:email', 'nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'min:15', 'max:5000'],
            'website' => ['nullable', 'prohibited'],
        ], [
            'email.required_without' => 'Provide an email address or phone number so the school can follow up.',
            'phone.required_without' => 'Provide an email address or phone number so the school can follow up.',
            'message.min' => 'Please include a little more detail (at least 15 characters).',
        ]);

        do {
            $referenceCode = 'FB-' . now()->format('Y') . '-' . Str::upper(Str::random(7));
        } while (ParentFeedback::where('reference_code', $referenceCode)->exists());

        $feedback = ParentFeedback::create([
            'reference_code' => $referenceCode,
            'category' => $validated['category'],
            'is_anonymous' => $anonymous,
            'parent_name' => $anonymous ? null : $validated['parent_name'],
            'email' => $anonymous ? null : ($validated['email'] ?? null),
            'phone' => $anonymous ? null : ($validated['phone'] ?? null),
            'message' => $validated['message'],
            'status' => ParentFeedback::STATUS_NEW,
        ]);

        try {
            Mail::to(config('parent_feedback.email_to'))->send(new ParentFeedbackNotification($feedback));
        } catch (\Throwable $exception) {
            Log::warning('Parent feedback notification email failed.', [
                'feedback_id' => $feedback->id,
                'reference_code' => $feedback->reference_code,
                'target_email' => config('parent_feedback.email_to'),
                'error' => $exception->getMessage(),
            ]);
        }

        if (!$feedback->is_anonymous && $feedback->email) {
            try {
                Mail::to($feedback->email)->send(new ParentFeedbackReceipt($feedback));
            } catch (\Throwable $exception) {
                Log::warning('Parent feedback receipt email failed.', [
                    'feedback_id' => $feedback->id,
                    'reference_code' => $feedback->reference_code,
                    'target_email' => $feedback->email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('feedback.create')
            ->with('feedback_reference', $feedback->reference_code);
    }
}
