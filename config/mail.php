<?php

return [

    // -------------------------------------------------------------------------
    // Global "From" Address
    // -------------------------------------------------------------------------

    'from' => ['address' => env('MAIL_FROM_ADDRESS'), 'name' => env('MAIL_FROM_NAME')],

    // -------------------------------------------------------------------------
    // Mail Driver
    // -------------------------------------------------------------------------

    'driver' => env('MAIL_DRIVER', 'mail'),

    // -------------------------------------------------------------------------
    // SMTP Server
    // -------------------------------------------------------------------------

    'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
    'port' => env('MAIL_PORT', 587),
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),

    // -------------------------------------------------------------------------
    // Sendmail System Path
    // -------------------------------------------------------------------------

    'sendmail' => '/usr/sbin/sendmail -bs',

    // -------------------------------------------------------------------------
    // E-Mail Encryption Protocol
    // -------------------------------------------------------------------------

    'encryption' => env('MAIL_ENCRYPTION', 'tls'),

    // -------------------------------------------------------------------------
    // Mail "Pretend"
    // -------------------------------------------------------------------------

    'pretend' => false,

];
