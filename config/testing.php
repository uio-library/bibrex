<?php

return [
    'host' => env('SELENIUM_HOST'),
    'caps' => [
        'platform' => env('SELENIUM_PLATFORM'),
        'browserName' => env('SELENIUM_BROWSER_NAME'),
        'browserVersion' => env('SELENIUM_BROWSER_VERSION'),
        'browserstack.local' => 'true',
        'browserstack.localIdentifier' => env('BROWSERSTACK_LOCAL_IDENTIFIER'),
        'browserstack.console' => 'info',
        'project' => 'bibrex',
    ],
    'browserstack' => [
        'user' => env('BROWSERSTACK_USER'),
        'key' => env('BROWSERSTACK_KEY'),
    ]
];
