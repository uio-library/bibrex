<?php namespace App\Services\Providers;

use Illuminate\Support\ServiceProvider,
	App\Services\Validators\CustomValidator;

class ValidatorServiceProvider extends ServiceProvider {

	public function boot()
	{
		\Validator::resolver(function($translator, $data, $rules, $messages)
		{
			return new CustomValidator($translator, $data, $rules, $messages);
		});
	}

	public function register()
	{

	}

	public function provides()
	{
		return array();
	}
}