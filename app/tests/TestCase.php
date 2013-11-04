<?php

use Mockery as m;

class TestCase extends Illuminate\Foundation\Testing\TestCase {

  protected $useDatabase = true;

	/**
	 * Creates the application.
	 *
	 * @return Symfony\Component\HttpKernel\HttpKernelInterface
	 */
	public function createApplication()
	{
		$unitTesting = true;

		$testEnvironment = 'testing';

		return require __DIR__.'/../../bootstrap/start.php';
	}

    public function setUpDb()
    {
        Artisan::call('migrate');
        Artisan::call('db:seed');
        //$this->seed();

        $this->library = Library::find(1);
        if (is_null($this->library)) {
            dd('Library not seeded!');
        }
        $this->be($this->library);

    }

    public function setUp()
    {
        parent::setUp();
        if($this->useDatabase)
        {
            $this->setUpDb();
        }
    }

    public function tearDown()
    {
        m::close();
        Artisan::call('migrate:reset');
    }

}
