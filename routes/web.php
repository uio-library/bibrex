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

Route::redirect('/', '/loans');
Route::redirect('/loans/index', '/loans');

Route::view('/about', 'about');

Route::resource('logs', 'LogsController')->only(['index']);  // 'destroy';

Route::get('/libraries/login', 'LibrariesController@getLogin')->name('login');
Route::post('/libraries/login', 'LibrariesController@postLogin');
Route::get('/logout', 'LibrariesController@getLogout');

Route::middleware(['auth'])->group(function () {
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
    Route::get('/users.json', 'UsersController@json');
    Route::get('/users/{user}', 'UsersController@getShow');
    Route::get('/users/{user}/edit', 'UsersController@getEdit');
    Route::get('/users/{user}/sync', 'UsersController@sync');
    Route::get('/users/{user}/connect', 'UsersController@connectForm');
    Route::post('/users/{user}/connect', 'UsersController@connect');
    Route::post('/users/{user}', 'UsersController@upsert');
    Route::get('/users/merge/{user1}/{user2}', 'UsersController@getMerge');
    Route::post('/users/merge/{user1}/{user2}', 'UsersController@postMerge');
    Route::get('/users/delete/{user}', 'UsersController@deleteForm');
    Route::post('/users/delete/{user}', 'UsersController@delete');

    // --[[ LOAN ]]--
    Route::get('/loans', 'LoansController@getIndex');
    Route::get('/loans.json', 'LoansController@json');
    Route::post('/loans/checkout', 'LoansController@checkout');
    Route::post('/loans/checkin', 'LoansController@checkin');
    Route::get('/loans/{loan}', 'LoansController@getShow');
    Route::post('/loans/{loan}/restore', 'LoansController@restore');
    Route::post('/loans/{loan}/lost', 'LoansController@lost');
    Route::get('/loans/{loan}/edit', 'LoansController@edit');
    Route::post('/loans/{loan}', 'LoansController@update');

    // --[[ ITEM ]]--
    Route::get('/items', 'ItemsController@index');
    Route::get('/items/search', 'ItemsController@search');
    Route::get('/items/{item}', 'ItemsController@show');
    Route::get('/items/edit/{item}', 'ItemsController@editForm');
    Route::post('/items/{item}', 'ItemsController@upsert');
    // Route::get('/items/delete/{item}', 'ItemsController@deleteForm');
    Route::get('/items/delete/{item}', 'ItemsController@delete');
    Route::get('/items/restore/{item}', 'ItemsController@restore');

    // --[[ THINGS ]]--
    Route::get('/things', 'ThingsController@index');
    Route::get('/things.json', 'ThingsController@json');
    Route::get('/things/{thing}', 'ThingsController@show');
    Route::post('/things/{thing}', 'ThingsController@upsert');
    Route::post('/things/{thing}/settings', 'ThingsController@updateSettings');
    Route::post('/things/{thing}/delete', 'ThingsController@delete');
    Route::post('/things/{thing}/restore', 'ThingsController@restore');

    // --[[ REMINDER ]]--
    Route::get('/notifications', 'NotificationsController@index');
    Route::get('/notifications/{notification}', 'NotificationsController@show');

    Route::get('/loans/{loan}/create-reminder', 'NotificationsController@create');
    Route::post('/loans/{loan}/send-reminder', 'NotificationsController@send');


    // --[[ LOG ]]--
    Route::get('/logs', 'LogsController@getIndex');
    Route::post('/logs/destroy', 'LogsController@postDestroy');
});
