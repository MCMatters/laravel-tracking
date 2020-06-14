<?php

declare(strict_types=1);

return [
    // Settings for skipping.
    'skip' => [
        // Determines whether tracking is required for anonymous users
        'anonymous' => false,

        // List of user names that should be skipped.
        'names' => [],

        // List of user emails that should be skipped.
        'emails' => [],

        // List of uris that should be skipped.
        'uris' => [],
    ],

    // User field settings.
    'user_fields' => [
        // Username field.
        'name' => 'name',

        // User email field.
        'email' => 'email',
    ],

    // Sanitize fields from input.
    'sanitize_input' => [
        'password',
        'password_confirmation',
    ],

    'table' => 'tracking',
];
