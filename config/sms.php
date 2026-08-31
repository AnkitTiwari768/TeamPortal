<?php

return [
    'smartping' => [
        'base_url' => env('SMARTPING_BASE_URL', 'https://api.smartping.ai/fe/api/v1/send'),
        'username' => env('SMARTPING_USERNAME'),
        'password' => env('SMARTPING_PASSWORD'),
        'sender'   => env('SMARTPING_SENDER', 'NSICTI'),
        'timeout'  => 10,
    ],
];
