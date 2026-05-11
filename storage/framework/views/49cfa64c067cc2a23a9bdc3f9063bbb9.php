<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enquiry received</title>
</head>
<body style="font-family: system-ui, sans-serif; color:#1f2937; line-height:1.5">
    <h2 style="margin-bottom:8px;">Hello <?php echo e($enquiry->parent_name); ?>,</h2>
    <p>Thank you for your <?php echo e($enquiry->inquiry_type); ?> regarding <?php echo e($enquiry->student_name); ?>'s admission to Cambridge International School.</p>
    <p>Our admissions team has updated your record and will follow up with you shortly.</p>
    <?php if($enquiry->message): ?>
        <p><strong>Your note:</strong> <?php echo e($enquiry->message); ?></p>
    <?php endif; ?>
    <p>Feel free to reply to this email if you have any more questions.</p>
    <p>Best regards,<br>Cambridge International School Admissions</p>
</body>
</html>
<?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\emails\admission-enquiry-acknowledgement.blade.php ENDPATH**/ ?>