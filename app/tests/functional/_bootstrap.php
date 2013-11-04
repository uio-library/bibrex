<?php
// Here you can initialize variables that will for your tests

Config::set('database.default', 'mysql');

Route::enableFilters();
Auth::logout(); // Shouldn't the Codeception Laravel module ideally do this?

if ($scenario->running()) {

	// Mock NCIP

	Log::info('Mocking NcipClient');

	$ncipMock = Mockery::mock();
	App::instance('NcipClient', $ncipMock);

	$response1 = new Danmichaelo\Ncip\CheckOutResponse(null);
	$ncipMock->shouldReceive('checkOutItem')
		->andReturn($response1);

	$response2 = new Danmichaelo\Ncip\CheckInResponse(null);
	$response2->success = true;
	$ncipMock->shouldReceive('checkInItem')
		->andReturn($response2);

}

if ($scenario->preload()) {
    #Log::info('Analyzing');
}

if ($scenario->running()) {
    #Log::info('Running');
    #Artisan::call('migrate:refresh');
	#App::make('DatabaseSeeder')->run();
}
