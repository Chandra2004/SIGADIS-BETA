<?php

use App\Http\Middleware\ArtisanWebAuth;

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    */

    'enabled' => filter_var(env('WEBARTISAN_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Access Password (BCRYPT HASH)
    |--------------------------------------------------------------------------
    */

    'password' => env('WEBARTISAN_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Enabled Environments
    |--------------------------------------------------------------------------
    */

    'enabled_environments' => [
        '*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    */

    'route_prefix' => env('WEBARTISAN_PREFIX', 'webartisan'),

    /*
    |--------------------------------------------------------------------------
    | Route Domain
    |--------------------------------------------------------------------------
    */

    'domain' => env('WEBARTISAN_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    */

    'middleware' => ['web', ArtisanWebAuth::class],

    /*
    |--------------------------------------------------------------------------
    | Authorization Gate
    |--------------------------------------------------------------------------
    */

    'gate' => null,

    /*
    |--------------------------------------------------------------------------
    | Allowed Commands
    |--------------------------------------------------------------------------
    |
    | Dibatasi ke perintah deploy yang aman untuk shared hosting tanpa SSH.
    | Data sistem ini berisi data kesehatan (UU PDP) — jangan dikosongkan,
    | daftar kosong berarti SEMUA perintah artisan diizinkan termasuk
    | tinker (eksekusi PHP bebas) dan db:wipe/migrate:fresh (hapus data).
    |
    */

    'allowed_commands' => [],

    /*
    |--------------------------------------------------------------------------
    | Blocked Commands
    |--------------------------------------------------------------------------
    |
    | Lapisan kedua (defense in depth) kalau allowed_commands di atas
    | sengaja/tidak sengaja dikosongkan.
    |
    */

    'blocked_commands' => [],

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    */

    'theme' => env('WEBARTISAN_THEME', 'dark'),

];
