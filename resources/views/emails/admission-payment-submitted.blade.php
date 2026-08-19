<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Admission Payment Submitted</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937;">
    <h2 style="margin-bottom: 16px;">New Admission Form Payment Submitted</h2>

    <p>A new payment for the admission form fee has been submitted from the website.</p>

    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%; max-width: 700px;">
        <tr>
            <td><strong>Parent / Guardian</strong></td>
            <td>{{ $payment->parent_name }}</td>
        </tr>
        <tr>
            <td><strong>Phone / WhatsApp</strong></td>
            <td>{{ $payment->phone }}</td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td>{{ $payment->email ?: 'Not provided' }}</td>
        </tr>
        <tr>
            <td><strong>Student Name</strong></td>
            <td>{{ $payment->student_name }}</td>
        </tr>
        <tr>
            <td><strong>Class Level</strong></td>
            <td>{{ $payment->class_level }}</td>
        </tr>
        <tr>
            <td><strong>Depositor Name</strong></td>
            <td>{{ $payment->depositor_name }}</td>
        </tr>
        <tr>
            <td><strong>Amount Paid</strong></td>
            <td>₦{{ number_format($payment->amount_paid) }}</td>
        </tr>
        <tr>
            <td><strong>Bank Name</strong></td>
            <td>{{ $payment->bank_name ?: 'Not provided' }}</td>
        </tr>
        <tr>
            <td><strong>Payment Date</strong></td>
            <td>{{ $payment->payment_date ? $payment->payment_date->format('F j, Y') : 'Not provided' }}</td>
        </tr>
        <tr>
            <td><strong>Payment Reference</strong></td>
            <td>{{ $payment->payment_reference ?: 'Not provided' }}</td>
        </tr>
        <tr>
            <td><strong>Payment Notes</strong></td>
            <td>{{ $payment->payment_notes ?: 'None' }}</td>
        </tr>
        <tr>
            <td><strong>Status</strong></td>
            <td>{{ ucfirst(str_replace('_', ' ', $payment->status)) }}</td>
        </tr>
        <tr>
            <td><strong>Submitted At</strong></td>
            <td>{{ $payment->created_at->format('F j, Y g:i A') }}</td>
        </tr>
        <tr>
            <td><strong>Submission IP</strong></td>
            <td>{{ $payment->ip_address }}</td>
        </tr>
    </table>

    <p style="margin-top: 24px; color: #6b7280; font-size: 12px;">
        <strong>Next Step:</strong> Please verify this payment against your bank records and approve or reject it in the admin panel to issue an application code.
    </p>
</body>
</html>
