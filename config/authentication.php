<?php

return [
    'driver' => env('AUTH_DRIVER', 'database'),
    'masquerade_username' => env('MASQUERADE_USERNAME'),
    'max_login_failures' => env('MAX_LOGIN_FAILURES', 3),
    'timebox_duration' => env('TIMEBOX_DURATION', 4000000),
];
