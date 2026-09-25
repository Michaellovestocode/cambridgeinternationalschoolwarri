<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>New Parent Feedback</title></head>
<body style="margin:0;background:#f1f5f9;color:#0f172a;font-family:Arial,sans-serif;line-height:1.6;padding:24px;">
    <div style="max-width:680px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">
        <div style="background:#123b70;color:#fff;padding:24px 28px;">
            <p style="margin:0;color:#bfdbfe;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;">Cambridge International School Warri</p>
            <h1 style="margin:8px 0 0;font-size:24px;">New parent {{ ucfirst($feedback->category) }}</h1>
        </div>
        <div style="padding:24px 28px;">
            <p style="margin:0 0 18px;color:#475569;">A parent feedback submission has been saved in the admin dashboard.</p>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <tr><td style="padding:9px;border-bottom:1px solid #e2e8f0;font-weight:bold;width:160px;">Reference</td><td style="padding:9px;border-bottom:1px solid #e2e8f0;">{{ $feedback->reference_code }}</td></tr>
                <tr><td style="padding:9px;border-bottom:1px solid #e2e8f0;font-weight:bold;">Category</td><td style="padding:9px;border-bottom:1px solid #e2e8f0;">{{ ucfirst($feedback->category) }}</td></tr>
                <tr><td style="padding:9px;border-bottom:1px solid #e2e8f0;font-weight:bold;">Parent</td><td style="padding:9px;border-bottom:1px solid #e2e8f0;">{{ $feedback->is_anonymous ? 'Anonymous' : $feedback->parent_name }}</td></tr>
                <tr><td style="padding:9px;border-bottom:1px solid #e2e8f0;font-weight:bold;">Email</td><td style="padding:9px;border-bottom:1px solid #e2e8f0;">{{ $feedback->email ?: 'Not provided' }}</td></tr>
                <tr><td style="padding:9px;border-bottom:1px solid #e2e8f0;font-weight:bold;">Phone</td><td style="padding:9px;border-bottom:1px solid #e2e8f0;">{{ $feedback->phone ?: 'Not provided' }}</td></tr>
            </table>
            <div style="margin-top:20px;padding:16px;border-radius:12px;background:#f8fafc;white-space:pre-line;">{{ $feedback->message }}</div>
            <p style="margin:22px 0 0;font-size:12px;color:#64748b;">Open the Parent Feedback page from the admin dashboard to review and track this submission.</p>
        </div>
    </div>
</body>
</html>
