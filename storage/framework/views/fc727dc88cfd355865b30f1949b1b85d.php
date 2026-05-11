<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Admission Enquiry</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937;">
    <h2 style="margin-bottom: 16px;">New Admission <?php echo e($enquiry->inquiry_type === \App\Models\AdmissionEnquiry::TYPE_APPLICATION ? 'Application' : 'Enquiry'); ?></h2>

    <p>A new admission <?php echo e($enquiry->inquiry_type); ?> was submitted from the website.</p>

    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%; max-width: 700px;">
        <tr>
            <td><strong>Record Type</strong></td>
            <td><?php echo e(ucfirst($enquiry->inquiry_type)); ?></td>
        </tr>
        <tr>
            <td><strong>Parent / Guardian</strong></td>
            <td><?php echo e($enquiry->parent_name); ?></td>
        </tr>
        <tr>
            <td><strong>Phone / WhatsApp</strong></td>
            <td><?php echo e($enquiry->phone); ?></td>
        </tr>
        <tr>
            <td><strong>Alternate Phone</strong></td>
            <td><?php echo e($enquiry->alternate_phone ?: 'Not provided'); ?></td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td><?php echo e($enquiry->email ?: 'Not provided'); ?></td>
        </tr>
        <tr>
            <td><strong>Student</strong></td>
            <td><?php echo e($enquiry->student_name); ?></td>
        </tr>
        <tr>
            <td><strong>Preferred Name</strong></td>
            <td><?php echo e($enquiry->preferred_name ?: 'Not provided'); ?></td>
        </tr>
        <tr>
            <td><strong>Student Gender</strong></td>
            <td><?php echo e($enquiry->student_gender ? ucfirst($enquiry->student_gender) : 'Not provided'); ?></td>
        </tr>
        <tr>
            <td><strong>Date of Birth</strong></td>
            <td><?php echo e($enquiry->student_date_of_birth ? $enquiry->student_date_of_birth->format('F j, Y') : 'Not provided'); ?></td>
        </tr>
        <tr>
            <td><strong>Class Applying For</strong></td>
            <td><?php echo e($enquiry->class_level); ?></td>
        </tr>
        <tr>
            <td><strong>Academic Year</strong></td>
            <td><?php echo e($enquiry->academic_year ?: 'Not provided'); ?></td>
        </tr>
        <tr>
            <td><strong>Nationality / State of Origin</strong></td>
            <td><?php echo e($enquiry->nationality ?: 'Not provided'); ?><?php echo e($enquiry->state_of_origin ? ' / ' . $enquiry->state_of_origin : ''); ?></td>
        </tr>
        <tr>
            <td><strong>Religion / Languages</strong></td>
            <td>
                <?php echo e($enquiry->religious_affiliation ?: 'Not provided'); ?>

                <?php if($enquiry->native_language): ?>
                    | Native: <?php echo e($enquiry->native_language); ?>

                <?php endif; ?>
                <?php if($enquiry->other_languages): ?>
                    | Other: <?php echo e($enquiry->other_languages); ?>

                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>Previous School</strong></td>
            <td><?php echo e($enquiry->previous_school ?: 'Not provided'); ?></td>
        </tr>
        <tr>
            <td><strong>Parent Occupation</strong></td>
            <td><?php echo e($enquiry->parent_occupation ?: 'Not provided'); ?></td>
        </tr>
        <tr>
            <td><strong>Home Address</strong></td>
            <td><?php echo e($enquiry->home_address ?: 'Not provided'); ?></td>
        </tr>
        <tr>
            <td><strong>Current School</strong></td>
            <td>
                <?php echo e($enquiry->current_school_name ?: 'Not provided'); ?>

                <?php if($enquiry->current_school_class): ?>
                    (<?php echo e($enquiry->current_school_class); ?>)
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>Previous Schools</strong></td>
            <td><?php echo e($enquiry->previous_schools ?: 'Not provided'); ?></td>
        </tr>
        <tr>
            <td><strong>Father</strong></td>
            <td><?php echo e($enquiry->father_name ?: 'Not provided'); ?> | <?php echo e($enquiry->father_phone ?: 'No phone'); ?></td>
        </tr>
        <tr>
            <td><strong>Mother</strong></td>
            <td><?php echo e($enquiry->mother_name ?: 'Not provided'); ?> | <?php echo e($enquiry->mother_phone ?: 'No phone'); ?></td>
        </tr>
        <tr>
            <td><strong>Medical / Learning Notes</strong></td>
            <td>
                Limitation: <?php echo e($enquiry->learning_physical_limitation ?: 'None'); ?><br>
                Illness: <?php echo e($enquiry->peculiar_illness ?: 'None'); ?>

            </td>
        </tr>
        <tr>
            <td><strong>About the Child</strong></td>
            <td><?php echo e($enquiry->child_personality_notes ?: 'Not provided'); ?></td>
        </tr>
        <tr>
            <td><strong>Undertaking Accepted</strong></td>
            <td><?php echo e($enquiry->undertaking_accepted ? 'Yes' : 'No'); ?></td>
        </tr>
        <tr>
            <td><strong>How They Heard About Us</strong></td>
            <td><?php echo e($enquiry->how_heard_about_us ?: 'Not provided'); ?></td>
        </tr>
        <tr>
            <td><strong>Message / Questions</strong></td>
            <td><?php echo e($enquiry->message ?: 'None'); ?></td>
        </tr>
        <tr>
            <td><strong>Status</strong></td>
            <td><?php echo e(ucfirst(str_replace('_', ' ', $enquiry->status))); ?></td>
        </tr>
        <tr>
            <td><strong>Submitted At</strong></td>
            <td><?php echo e($enquiry->created_at->format('F j, Y g:i A')); ?></td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views/emails/admission-enquiry-submitted.blade.php ENDPATH**/ ?>