<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User storage time.
    |--------------------------------------------------------------------------
    |
    | The number of days a user with no remaining loans is stored in Bibrex
    | before being removed by the DeleteInactiveUsers command. The storage
    | time for imported users can be set to a lower value than the one for
    | local users, since there is no manual work involved in re-importing
    | the same user at a later point. Still, it makes sense to keep imported
    | for some time for more efficient lookups.
    |
    */

    'user_storage_time' => [
        'local' => env('BIBREX_LOCAL_USER_STORAGE_TIME', 365 * 3),
        'imported' => env('BIBREX_IMPORTED_USER_STORAGE_TIME', 365),
    ],

];
