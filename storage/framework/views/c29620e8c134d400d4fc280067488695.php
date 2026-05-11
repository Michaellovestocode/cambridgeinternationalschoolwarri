<p>Hello <?php echo e($message->recipient?->name ?? 'there'); ?>,</p>

<p>You have received a new message from <?php echo e($message->sender?->name ?? 'the school'); ?>.</p>

<?php if($message->subject): ?>
    <p><strong>Subject:</strong> <?php echo e($message->subject); ?></p>
<?php endif; ?>

<p><strong>Message:</strong></p>
<p><?php echo nl2br(e($message->body)); ?></p>

<p>Please log in to the school portal to reply or view the full message history.</p>
<?php /**PATH C:\laragon\www\modern-cbt-platform-for-highschool\resources\views\emails\direct-message-notification.blade.php ENDPATH**/ ?>