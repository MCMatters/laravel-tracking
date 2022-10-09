<?php

declare(strict_types=1);

return [
    // Settings for skipping.
    'skip' => [
        // Determines whether tracking is required for anonymous users.
        'anonymous' => false,

        // List of usernames that should be skipped.
        'names' => [],

        // List of user emails that should be skipped.
        'emails' => [],

        // List of uris that should be skipped.
        'uris' => [],

        // Skip recording response.
        'response' => false,
    ],

    // User field settings.
    'user_fields' => [
        // Username field.
        'name' => 'name',

        // User email field.
        'email' => 'email',
    ],

    // Sanitize fields.
    'sanitize' => [
        // Sanitize request input.
        'input' => [
            'password',
            'password_confirmation',
            '_token',
        ],
        // Sanitize request headers.
        'headers' => [
            'cookie',
            'user-agent',
        ],
    ],

    'table' => 'tracking',
];
