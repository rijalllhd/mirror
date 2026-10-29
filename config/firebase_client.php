<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Client Config (dipakai di Blade via @json / JS)
    |--------------------------------------------------------------------------
    */
    'api_key'             => env('FIREBASE_API_KEY'),
    'auth_domain'         => env('FIREBASE_AUTH_DOMAIN'),
    'database_url'        => env('FIREBASE_DATABASE_URL'),
    'project_id'          => env('FIREBASE_PROJECT_ID'),
    'storage_bucket'      => env('FIREBASE_STORAGE_BUCKET'),
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
    'app_id'              => env('FIREBASE_APP_ID'),
    'credentials'         => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),

    /*
    |--------------------------------------------------------------------------
    | Firebase path — sesuai struktur Firebase Realtime Database
    |--------------------------------------------------------------------------
    */
    'sensor_path' => 'SKARP',
];
