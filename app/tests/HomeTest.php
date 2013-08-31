<?php

class HomeTest extends TestCase {

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testRedirectsToLoansIndexView()
	{
		$crawler = $this->client->request('GET', '/');
		$this->assertRedirectedToAction('LoansController@getIndex');
	}

}
