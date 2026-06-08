<?php

$admissionEmails = env('ADMISSION_ENQUIRY_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@example.com'));

return [
    'email_to' => array_values(array_filter(array_map('trim', explode(',', $admissionEmails)))),
    'whatsapp_number' => env('ADMISSION_WHATSAPP_NUMBER', '2348032897744'),
    'whatsapp_message_webhook' => env('WHATSAPP_MESSAGE_WEBHOOK'),
    'form_fee' => (int) env('ADMISSION_FORM_FEE', 20000),
    'form_payment_bank_name' => env('ADMISSION_FORM_BANK_NAME', 'First Bank'),
    'form_payment_account_name' => env('ADMISSION_FORM_ACCOUNT_NAME', 'Cambridge Creche to College Limited'),
    'form_payment_account_number' => env('ADMISSION_FORM_ACCOUNT_NUMBER', '2042552725'),
    'form_payment_accounts' => [
        [
            'bank_name' => 'First Bank',
            'account_name' => 'Cambridge Creche to College Limited',
            'account_number' => '2042552725',
        ],
        [
            'bank_name' => 'UBA',
            'account_name' => 'Cambridge International School',
            'account_number' => '1022220885',
        ],
    ],
];
