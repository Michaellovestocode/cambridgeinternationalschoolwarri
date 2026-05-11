<?php

namespace App\Http\Controllers;

use App\Mail\AdmissionEnquiryAcknowledgement;
use App\Models\AdmissionEnquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminAdmissionEnquiryController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = AdmissionEnquiry::query();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($sub) use ($search) {
                $sub->where('parent_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('student_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $enquiries = $query->latest()->paginate(15)->withQueryString();

        $summary = [
            AdmissionEnquiry::STATUS_NEW => AdmissionEnquiry::where('status', AdmissionEnquiry::STATUS_NEW)->count(),
            AdmissionEnquiry::STATUS_UNDER_REVIEW => AdmissionEnquiry::where('status', AdmissionEnquiry::STATUS_UNDER_REVIEW)->count(),
            AdmissionEnquiry::STATUS_APPROVED => AdmissionEnquiry::where('status', AdmissionEnquiry::STATUS_APPROVED)->count(),
            AdmissionEnquiry::STATUS_REJECTED => AdmissionEnquiry::where('status', AdmissionEnquiry::STATUS_REJECTED)->count(),
            AdmissionEnquiry::STATUS_CONTACTED => AdmissionEnquiry::where('status', AdmissionEnquiry::STATUS_CONTACTED)->count(),
        ];

        return view('admin.enquiries.index', [
            'enquiries' => $enquiries,
            'statuses' => AdmissionEnquiry::statuses(),
            'summary' => $summary,
            'filters' => [
                'status' => $request->string('status')->value(''),
                'search' => $request->string('search')->value(''),
            ],
        ]);
    }

    public function show(AdmissionEnquiry $enquiry)
    {
        return view('admin.enquiries.show', [
            'enquiry' => $enquiry,
            'statuses' => AdmissionEnquiry::statuses(),
        ]);
    }

    public function update(Request $request, AdmissionEnquiry $enquiry)
    {
        $payload = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', AdmissionEnquiry::statuses())],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $previousStatus = $enquiry->status;
        $enquiry->update($payload);
        if ($enquiry->status === AdmissionEnquiry::STATUS_CONTACTED && $previousStatus !== AdmissionEnquiry::STATUS_CONTACTED) {
            Log::info('Admission enquiry marked contacted', ['id' => $enquiry->id, 'parent' => $enquiry->parent_name]);
            if ($enquiry->email) {
                try {
                    Mail::to($enquiry->email)->send(new AdmissionEnquiryAcknowledgement($enquiry));
                } catch (\Throwable $exception) {
                    Log::warning('Admission acknowledgement email delivery failed.', [
                        'enquiry_id' => $enquiry->id,
                        'target_email' => $enquiry->email,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.enquiries.show', $enquiry)
            ->with('success', 'Admission record updated.');
    }
}
