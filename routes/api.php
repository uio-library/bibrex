<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', 'PublicApiController@doc');

Route::get('/libraries', 'PublicApiController@libraries');
Route::get('/things', 'PublicApiController@things');
Route::get('/items', 'PublicApiController@items');
