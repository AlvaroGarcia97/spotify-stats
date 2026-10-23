<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ===== DATOS DE NUESTRA APP DE SPOTIFY =====
    // env('...') significa: "busca este valor en el archivo .env".
    // De esta forma los datos secretos NO quedan escritos dentro del código
    // y no se suben a GitHub por error.
    'spotify' => [
        'client_id' => env('SPOTIFY_CLIENT_ID'),         // Nos identifica ante Spotify.
        'client_secret' => env('SPOTIFY_CLIENT_SECRET'), // Es la contraseña secreta. ¡Nunca compartir!
        'redirect_uri' => env('SPOTIFY_REDIRECT_URI'),   // A dónde vuelve el usuario tras loguearse.
    ],

];
