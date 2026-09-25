<?php

namespace App\Http\Controllers;

use App\Models\ParentFeedback;
use Illuminate\Http\Request;

class AdminParentFeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $status = $request->string('status')->value();
        $category = $request->string('category')->value();
        $search = trim((string) $request->input('search', ''));

        $feedback = ParentFeedback::query()
            ->with('reviewedBy')
            ->when(in_array($status, ParentFeedback::statuses(), true), fn ($query) => $query->where('status', $status))
            ->when(in_array($category, ParentFeedback::categories(), true), fn ($query) => $query->where('category', $category))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($filter) use ($search) {
                    $filter->where('reference_code', 'like', "%{$search}%")
                        ->orWhere('parent_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            ParentFeedback::STATUS_NEW => ParentFeedback::where('status', ParentFeedback::STATUS_NEW)->count(),
            ParentFeedback::STATUS_UNDER_REVIEW => ParentFeedback::where('status', ParentFeedback::STATUS_UNDER_REVIEW)->count(),
            ParentFeedback::STATUS_RESOLVED => ParentFeedback::where('status', ParentFeedback::STATUS_RESOLVED)->count(),
        ];

        return view('admin.parent-feedback.index', [
            'feedback' => $feedback,
            'summary' => $summary,
            'statuses' => ParentFeedback::statuses(),
            'categories' => ParentFeedback::categories(),
            'filters' => compact('status', 'category', 'search'),
        ]);
    }

    public function show(ParentFeedback $parentFeedback)
    {
        return view('admin.parent-feedback.show', [
            'feedback' => $parentFeedback->load('reviewedBy'),
            'statuses' => ParentFeedback::statuses(),
        ]);
    }

    public function update(Request $request, ParentFeedback $parentFeedback)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', ParentFeedback::statuses())],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $parentFeedback->update([
            ...$validated,
            'reviewed_by' => $request->user()->id,
            'resolved_at' => $validated['status'] === ParentFeedback::STATUS_RESOLVED ? ($parentFeedback->resolved_at ?? now()) : null,
        ]);

        return redirect()
            ->route('admin.parent-feedback.show', $parentFeedback)
            ->with('success', 'Feedback record updated.');
    }
}
