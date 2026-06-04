<?php

$admissionEmails = env('ADMISSION_ENQUIRY_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@example.com'));

return [
    'email_to' => array_values(array_filter(array_map('trim', explode(',', $admissionEmails)))),
    'whatsapp_number' => env('ADMISSION_WHATSAPP_NUMBER', '2348032897744'),
    'whatsapp_message_webhook' => env('WHATSAPP_MESSAGE_WEBHOOK'),
    'form_fee' => (int) env('ADMISSION_FORM_FEE', 20000),
    'form_payment_bank_name' => env('ADMISSION_FORM_BANK_NAME', 'Demo Trust Bank'),
    'form_payment_account_name' => env('ADMISSION_FORM_ACCOUNT_NAME', 'Cambridge International School Demo Account'),
    'form_payment_account_number' => env('ADMISSION_FORM_ACCOUNT_NUMBER', '0001234567'),
];
