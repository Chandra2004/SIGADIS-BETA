<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Eskalasi Emergency Alert
    |--------------------------------------------------------------------------
    |
    | Flows.md §7.3.2: kalau bidan tidak menekan "Terima/Tangani" dalam
    | batas waktu ini, alert dieskalasi ke kader secondary wilayah.
    | Nilai default 10 menit — nilai konservatif awal per dokumen, USULAN
    | untuk disesuaikan hasil pengujian (Rules.md §6).
    |
    */

    'escalation_timeout_minutes' => env('ALERT_ESCALATION_TIMEOUT_MINUTES', 10),

    /*
    | Flows.md §34.3.1: dipakai kalau bidan pendamping utama pasien sedang
    | berstatus nonaktif sementara (is_available=false) — tidak menunggu
    | penuh batas waktu normal di atas.
    */
    'escalation_timeout_when_midwife_unavailable_minutes' => env('ALERT_ESCALATION_TIMEOUT_MIDWIFE_UNAVAILABLE_MINUTES', 3),

];
