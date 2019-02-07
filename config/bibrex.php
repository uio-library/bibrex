<?php

return [

    'thumbnail_dimensions' => [
        'width' => env('MIX_THUMBNAIL_WIDTH', 300),
        'height' => env('MIX_THUMBNAIL_HEIGHT', 300),
    ],

    'storage_time' => [

        /*
        |--------------------------------------------------------------------------
        | User storage time
        |--------------------------------------------------------------------------
        |
        | The number of days a user with no remaining loans is stored in Bibrex
        | before being removed by the PurgeUsers command. The storage
        | time for imported users can be set to a lower value than the one for
        | local users, since there is no manual work involved in re-importing
        | the same user at a later point. Still, it makes sense to keep imported
        | for some time for more efficient lookups.
        |
        */
        'local_users' => env('BIBREX_LOCAL_USER_STORAGE_TIME', 365 * 3),
        'imported_users' => env('BIBREX_IMPORTED_USER_STORAGE_TIME', 120),

        /*
        |--------------------------------------------------------------------------
        | Notification storage time
        |--------------------------------------------------------------------------
        |
        | The number of days to store notifications before they are deleted
        | permanently by the PurgeNotifications job.
        |
        */

        'notifications' => env('BIBREX_NOTIFICATION_STORAGE_TIME', 120),
    ],

];
