<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

App::missing(function($exception)
{
    return Response::view('errors.missing', array(), 404);
});

Route::get('/', function()
{
	return Redirect::action('LoansController@getIndex');
});

Route::get('/about', function()
{
	return Response::view('hello');
});

Route::controller('logs', 'LogsController');

Route::get('/libraries/login', 'LibrariesController@getLogin');
Route::post('/libraries/login', 'LibrariesController@postLogin');
Route::get('/logout', 'LibrariesController@getLogout');

Route::get('/available/{library}.json', 'ThingsController@getAvailableJson');
Route::get('/available/{library}', 'ThingsController@getAvailable');

Route::group(array('before' => 'auth'), function()
{

	Route::controller('users', 'UsersController');

	Route::controller('loans', 'LoansController');

	Route::controller('documents', 'DocumentsController');

	Route::controller('things', 'ThingsController');

	Route::controller('reminders', 'RemindersController');

	Route::controller('logs', 'LogsController');

	Route::controller('libraries', 'LibrariesController');

	Route::get('/my/account', 'LibrariesController@myAccount');
	Route::get('/my/ips', 'LibrariesController@myIps');
	Route::post('/my/ips/store', 'LibrariesController@storeIp');
	Route::get('/my/ips/remove/{id}', 'LibrariesController@removeIp');


});

