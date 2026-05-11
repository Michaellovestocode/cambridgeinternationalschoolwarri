<?php

return [
    'email_to' => env('ADMISSION_ENQUIRY_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
    'whatsapp_number' => env('ADMISSION_WHATSAPP_NUMBER', '2348032897744'),
    'whatsapp_message_webhook' => env('WHATSAPP_MESSAGE_WEBHOOK'),
];
