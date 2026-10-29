<?php

return [
    // Robase configuration (optional)
    'robase' => [
        'api_key' => env('ROBASE_API_KEY'),
        'base_url' => env('ROBASE_BASE_URL', 'https://api.robase.dev'),
    ],

    'staff_attendance' => [
        'key' => env('STAFF_ATTENDANCE_KEY'),
        'device_id' => env('F_G495_DEVICE_ID'),
        'start_date' => env('ATTENDANCE_START_DATE', '2026-09-14'),
    ],

    'student_attendance' => [
        'device_id' => env('STUDENT_F_G495_DEVICE_ID'),
        'device_ids' => array_values(array_filter(array_map('trim', explode(',', (string) env('STUDENT_F_G495_DEVICE_IDS', env('STUDENT_F_G495_DEVICE_ID', '')))))),
        'host' => env('STUDENT_F_G495_HOST', 'student-clock.cambridgeinternationalschoolwarri.com'),
        'tcp_port' => (int) env('CLOCK_TCP_PORT', 5055),
        'tcp_allowed_serials' => env('CLOCK_ALLOWED_SERIALS', ''),
    ],

];
