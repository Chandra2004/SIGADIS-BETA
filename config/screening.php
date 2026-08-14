<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Screening Reminder Settings
    |--------------------------------------------------------------------------
    |
    | Flows.md §6.3: notifikasi pengingat skrining berkala. Interval final
    | "ditentukan tim medis" (belum ditetapkan di spek) — nilai di bawah
    | adalah default konservatif, ubah lewat .env tanpa perlu deploy ulang.
    |
    */

    'reminder_interval_days' => env('SCREENING_REMINDER_INTERVAL_DAYS', 14),

];
