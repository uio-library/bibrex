<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function() {
    return redirect()->action('LoansController@getIndex');
});
Route::get('/loans/index', function() {
    return redirect()->action('LoansController@getIndex');
});

Route::get('/about', function() {
    return Response::view('hello');
});

Route::resource('logs', 'LogsController')->only(['index']);  // 'destroy';

Route::get('/libraries/login', 'LibrariesController@getLogin')->name('login');
Route::post('/libraries/login', 'LibrariesController@postLogin');
Route::get('/logout', 'LibrariesController@getLogout');

Route::get('/available/{library}.json', 'ThingsController@getAvailableJson');
Route::get('/available/{library}', 'ThingsController@getAvailable');

Route::middleware(['auth'])->group(function()
{
    // --[[ LIBRARY ]]--
    Route::get('/libraries', 'LibrariesController@getIndex');
    Route::get('/libraries/create', 'LibrariesController@getCreate');
    Route::post('/libraries', 'LibrariesController@postStore');
    Route::get('/libraries/{library}', 'LibrariesController@getShow');
    Route::get('/libraries/password', 'LibrariesController@getPassword');
    Route::post('/libraries/password', 'LibrariesController@postPassword');

    Route::get('/my/account', 'LibrariesController@getMyAccount');
    Route::post('/my/account', 'LibrariesController@postStoreMyAccount');
    Route::get('/my/ips', 'LibrariesController@getMyIps');
    Route::post('/my/ips/store', 'LibrariesController@storeIp');
    Route::get('/my/ips/remove/{id}', 'LibrariesController@removeIp');

    // --[[ USER ]]--
    Route::get('/users', 'UsersController@getIndex');
    Route::get('/users/search-alma', 'UsersController@searchAlma');
    Route::get('/users.json', 'UsersController@getIndexAsJson');
    Route::get('/users/{user}', 'UsersController@getShow');
    Route::get('/users/{user}/edit', 'UsersController@getEdit');
    Route::get('/users/{user}/sync', 'UsersController@getNcipLookup');
    Route::put('/users/{user}', 'UsersController@putUpdate');
    Route::get('/users/merge/{user1}/{user2}', 'UsersController@getMerge');
    Route::post('/users/merge/{user1}/{user2}', 'UsersController@postMerge');

    // --[[ LOAN ]]--
    Route::get('/loans', 'LoansController@getIndex');
    Route::get('/loans/{loan}', 'LoansController@getShow');
    Route::post('/loans', 'LoansController@postStore');
    Route::get('/loans/lost/{loan}', 'LoansController@getLost');
    Route::get('/loans/destroy/{loan}', 'LoansController@getDestroy');
    Route::get('/loans/restore/{loan}', 'LoansController@getRestore');


    // --[[ ITEM ]]--
    Route::get('/items', 'ItemsController@index');
    Route::get('/items/search', 'ItemsController@search');
    Route::get('/items/{item}', 'ItemsController@show');
    Route::get('/items/edit/{item}', 'ItemsController@editForm');
    Route::post('/items/{item}', 'ItemsController@upsert');
    Route::get('/items/delete/{item}', 'ItemsController@deleteForm');
    Route::post('/items/delete/{item}', 'ItemsController@delete');
    Route::get('/items/restore/{item}', 'ItemsController@restore');

    // --[[ THINGS ]]--
    Route::get('/things', 'ThingsController@getIndex');
    Route::get('/things/{thing}', 'ThingsController@getShow');
    Route::get('/things/edit/{thing}', 'ThingsController@getEdit');
    Route::post('/things/toggle/{thing}', 'ThingsController@toggle');
    Route::post('/things/toggle-require-item/{thing}', 'ThingsController@toggleRequireItem');
    Route::post('/things/{thing}', 'ThingsController@postUpdate');
    Route::get('/things/destroy/{thing}', 'ThingsController@getDestroy');
    Route::get('/things/restore/{thing}', 'ThingsController@getRestore');
    // Route::delete('/things/{thing}', 'ThingsController@delete');

    // --[[ REMINDER ]]--
    Route::get('/reminders', 'RemindersController@getIndex');
    Route::get('/reminders/create', 'RemindersController@getCreate');
    Route::post('/reminders', 'RemindersController@postStore');
    Route::get('/reminders/{reminder}', 'RemindersController@getShow');

    // --[[ LOG ]]--
    Route::get('/logs', 'LogsController@getIndex');
    Route::post('/logs/destroy', 'LogsController@postDestroy');

});
