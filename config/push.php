<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Push Notification Gateway
    |--------------------------------------------------------------------------
    |
    | Firebase Cloud Messaging (Architecture.md §5.4) untuk Emergency Alert
    | saat app bidan/kader di background. Default "log" dipakai untuk
    | lokal/testing sebelum file service account Firebase tersedia
    | (lihat config/firebase.php, env FIREBASE_CREDENTIALS).
    |
    */

    'gateway' => env('PUSH_GATEWAY', 'log'), // 'log' | 'fcm'

];
