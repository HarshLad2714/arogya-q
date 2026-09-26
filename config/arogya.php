<?php

return [

    /*
    | Queue engine contract
    | GET  /queue/status?doctor_id=&date=  -> {current_token,total_booked,avg_wait_time,avg_consultation_minutes,room,doctor_name}
    | POST /queue/next   {doctor_id,date}  -> same shape, current_token incremented
    | POST /queue/sync   {doctor_id,clinic_id,date,total_booked,avg_consultation_minutes,room,doctor_name}
    | POST /queue/reset  {date}
    */

    'queue_engine' => [
        'url' => env('QUEUE_ENGINE_URL', 'http://127.0.0.1:8081'),
        'secret' => env('QUEUE_ENGINE_SECRET', 'arogya-queue-secret'),
        'ws_port' => (int) env('QUEUE_ENGINE_WS_PORT', 8082),
        'force_http' => (bool) env('QUEUE_ENGINE_FORCE_HTTP', false),
    ],

    'otp' => [
        'demo' => (bool) env('OTP_DEMO', env('APP_ENV') === 'local'),
        'ttl_minutes' => 5,
    ],

    'alerts' => [
        'thresholds' => [5, 1, 0],
    ],

    'revenue_share' => (int) env('PLATFORM_REVENUE_SHARE', 10),

    'specialties' => [
        'General Medicine',
        'Pediatrics',
        'Dermatology',
        'Dental',
        'Orthopedics',
        'Gynecology',
        'ENT',
        'Ophthalmology',
        'Cardiology',
        'Ayurveda',
    ],

    'demo_accounts' => [
        ['role' => 'Super Admin', 'mobile' => '9999999999', 'password' => 'Admin@123'],
        ['role' => 'Clinic Admin', 'mobile' => '9888888888', 'password' => 'Clinic@123'],
        ['role' => 'Doctor', 'mobile' => '9777777771', 'password' => 'Doctor@123'],
        ['role' => 'Reception', 'mobile' => '9666666666', 'password' => 'Desk@123'],
        ['role' => 'Patient', 'mobile' => '9555555555', 'password' => 'Patient@123'],
    ],

];
