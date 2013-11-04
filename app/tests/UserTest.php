<?php

use Way\Tests\Factory;

class UserTest extends TestCase {
    use Way\Tests\ModelHelpers;

    public function tearDown()
    {
        Mockery::close();
    }

    public function setUp()
    {
        parent::setUp();

    }

    public function testUsingGuestNumber()
    {
		// Define some guest LTID
		$guest_ltid = 'eks1234567';
		$some_other_ltid = 'umn1000001';
		//Config::set('app.guest_ltid', $guest_ltid); // could we mock this? I've not found an elegant way to do it yet

        $this->library->guest_ltid = $guest_ltid;

        // Using the guest LTID should not be allowed
        $loan = Factory::user(['ltid' => $guest_ltid]);
        $this->assertNotValid($loan);

        // Using some other LTID should be allowed
        $loan = Factory::user(['ltid' => $some_other_ltid]);
        $this->assertValid($loan);

    }

}