<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Webhooks routes
|--------------------------------------------------------------------------
*/

Route::get('alma', 'WebhooksController@challenge');

Route::post('alma', 'WebhooksController@handle');
