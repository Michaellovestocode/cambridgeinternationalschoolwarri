<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Feedback received</title></head>
<body style="margin:0;background:#f1f5f9;color:#0f172a;font-family:Arial,sans-serif;line-height:1.6;padding:24px;">
    <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">
        <div style="background:#123b70;color:#fff;padding:24px 28px;">
            <p style="margin:0;color:#bfdbfe;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;">Cambridge International School Warri</p>
            <h1 style="margin:8px 0 0;font-size:24px;">We received your feedback</h1>
        </div>
        <div style="padding:24px 28px;">
            <p>Thank you for taking the time to share your {{ $feedback->category }} with us. The appropriate school staff will review it.</p>
            <p>Your reference number is:</p>
            <p style="display:inline-block;border-radius:10px;background:#ecfdf5;padding:12px 16px;color:#065f46;font-size:18px;font-weight:bold;">{{ $feedback->reference_code }}</p>
            <p style="font-size:13px;color:#64748b;">Please keep this reference for your records. This message confirms receipt; it is not a resolution notice.</p>
            <p style="margin-top:24px;">Cambridge International School Warri</p>
        </div>
    </div>
</body>
</html>
