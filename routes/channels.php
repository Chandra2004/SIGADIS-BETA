<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

/**
 * Kanal privat per bidan/kader (Architecture.md §5.2): dashboard mereka
 * subscribe ke kanal ini untuk update real-time saat ada Emergency Alert
 * baru, tanpa reload. Otorisasi lewat guard 'staff', bukan guard default.
 */
Broadcast::channel('staff.{workerId}', function ($user, $workerId) {
    return (int) Auth::guard('staff')->id() === (int) $workerId;
});
