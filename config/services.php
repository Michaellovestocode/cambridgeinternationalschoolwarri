<?php

return [
    // Twilio configuration (optional)
    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    // Robase configuration (optional)
    'robase' => [
        'api_key' => env('ROBASE_API_KEY'),
        'base_url' => env('ROBASE_BASE_URL', 'https://api.robase.dev'),
    ],

    'staff_attendance' => [
        'key' => env('STAFF_ATTENDANCE_KEY'),
        'device_id' => env('F_G495_DEVICE_ID'),
    ],

];
