<?php

$admissionEmails = env('ADMISSION_ENQUIRY_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@example.com'));

return [
    'email_to' => array_values(array_filter(array_map('trim', explode(',', $admissionEmails)))),
    'whatsapp_number' => env('ADMISSION_WHATSAPP_NUMBER', '2348032897744'),
    'whatsapp_message_webhook' => env('WHATSAPP_MESSAGE_WEBHOOK'),
];
