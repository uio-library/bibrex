<?php

return [
    'host' => env('TEST_HOST'),
    'caps' => [
        'platform' => env('TEST_PLATFORM'),
        'browserName' => env('TEST_BROWSER'),
        'browserVersion' => env('TEST_BROWSER_VERSION'),
    ],
];
