<?php

namespace App\Http\Controllers;

use App\Models\AdmissionFormPayment;
use Illuminate\Http\Request;

class AdminAdmissionFormPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = AdmissionFormPayment::query()->with('application');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($sub) use ($search) {
                $sub->where('parent_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('student_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('application_code', 'like', "%{$search}%")
                    ->orWhere('payment_reference', 'like', "%{$search}%");
            });
        }

        return view('admin.admission-form-payments.index', [
            'payments' => $query->latest()->paginate(15)->withQueryString(),
            'statuses' => AdmissionFormPayment::statuses(),
            'filters' => [
                'status' => $request->string('status')->value(''),
                'search' => $request->string('search')->value(''),
            ],
            'summary' => [
                AdmissionFormPayment::STATUS_PENDING => AdmissionFormPayment::where('status', AdmissionFormPayment::STATUS_PENDING)->count(),
                AdmissionFormPayment::STATUS_APPROVED => AdmissionFormPayment::where('status', AdmissionFormPayment::STATUS_APPROVED)->count(),
                AdmissionFormPayment::STATUS_REJECTED => AdmissionFormPayment::where('status', AdmissionFormPayment::STATUS_REJECTED)->count(),
            ],
        ]);
    }

    public function show(AdmissionFormPayment $payment)
    {
        return view('admin.admission-form-payments.show', [
            'payment' => $payment->load('application'),
            'statuses' => AdmissionFormPayment::statuses(),
        ]);
    }

    public function update(Request $request, AdmissionFormPayment $payment)
    {
        $payload = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', AdmissionFormPayment::statuses())],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($payload['status'] === AdmissionFormPayment::STATUS_APPROVED) {
            if (blank($payment->application_code)) {
                $payload['application_code'] = AdmissionFormPayment::generateApplicationCode();
            }

            $payload['approved_at'] = $payment->approved_at ?: now();
        }

        if ($payload['status'] !== AdmissionFormPayment::STATUS_APPROVED) {
            $payload['approved_at'] = null;
        }

        $payment->update($payload);

        return redirect()
            ->route('admin.admission-form-payments.show', $payment)
            ->with('success', 'Payment request updated.');
    }
}
