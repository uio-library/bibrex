<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Webhooks routes
|--------------------------------------------------------------------------
*/

Route::get('alma', function (Request $request) {
    return response()->json([
        'challenge' => $request->query('challenge'),
    ]);
});

Route::post('alma', 'WebhooksController@handle');
