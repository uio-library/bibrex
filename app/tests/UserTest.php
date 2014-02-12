<?php

use Way\Tests\Factory;

class UserTest extends TestCase {
    #use Way\Tests\ModelHelpers;
    /**
     * Methods copied from Way\Tests\ModelHelpers, since traits are not supported in PHP 5.3
     */
    public function assertValid($model)
    {
        $this->assertRespondsTo('validate', $model, "The 'validate' method does not exist on this model.");
        $this->assertTrue($model->validate(), 'Model did not pass validation.');
    }

    public function assertNotValid($model)
    {
        $this->assertRespondsTo('validate', $model, "The 'validate' method does not exist on this model.");
        $this->assertFalse($model->validate(), 'Did not expect model to pass validation.');
    }

    public function assertRespondsTo($method, $class, $message = null)
    {
        $message = $message ?: "Expected the '$class' class to have method, '$method'.";

        $this->assertTrue(
            method_exists($class, $method),
            $message
        );
    }


    /**
     * Everything else
     */
    public function tearDown()
    {
        Mockery::close();
    }

    public function setUp()
    {
        parent::setUp();

        // Mock NCIP
        $this->ncip = Mockery::mock();
        App::instance('ncip.client', $this->ncip);

    }

    /*
     * Validation should fail if a user is created with the guest number LTID
     */
    public function testUsingGuestNumber()
    {
		// Define some guest LTID
		$guest_ltid = 'eks1234567';
		$some_other_ltid = 'umn1000001';
		//Config::set('app.guest_ltid', $guest_ltid); // could we mock this? I've not found an elegant way to do it yet

        $this->library->guest_ltid = $guest_ltid;

        // Using the guest LTID should not be allowed
        $loan = Factory::make('User', array('ltid' => $guest_ltid));
        $this->assertNotValid($loan);

        // Using some other LTID should be allowed
        $loan = Factory::make('User', array('ltid' => $some_other_ltid));
        $this->assertValid($loan);
    }

    /*
     * Merge where all attributes could be taken from user 2
     */
    public function testMergingCase1()
    {
        $user1 = new User();
        $user1->firstname = 'Donald';
        $user1->lastname = 'Duck';

        $ltid = 'eks0000001';
        $user2 = new User();
        $user2->ltid = $ltid;
        $user2->firstname = 'Donald';
        $user2->lastname = 'Duck';
        $user2->email = 'd.duck@andeby.no';
        $user2->phone = '1234';

        $response = Mockery::mock();
        $response->exists = false;  // to keep the response simple

        $this->ncip->shouldReceive('lookupUser')->once()
            ->with($ltid)
            ->andReturn($response);

        $result = $user1->merge($user2);

        $this->assertNull($result);

        $this->assertTrue($user1->exists);
        $this->assertEquals($ltid, $user1->ltid);
        $this->assertEquals('Donald', $user1->firstname);
        $this->assertEquals('Duck', $user1->lastname);
        $this->assertEquals('d.duck@andeby.no', $user1->email);
        $this->assertEquals('1234', $user1->phone);

        $this->assertFalse($user2->exists);
    }

    /*
     * Merge where some attributes will be taken from user 1, others from user 2
     */
    public function testMergingCase2()
    {
        $user1 = new User();
        $user1->firstname = 'Donald O.';
        $user1->lastname = 'Duck';
        $user1->email = 'd.duck@andeby.com';

        $ltid = 'eks0000002';
        $user2 = new User();
        $user2->ltid = $ltid;
        $user2->firstname = 'Donald';
        $user2->lastname = 'Duck';
        $user2->email = 'd.duck@andeby.no';
        $user2->phone = '1234';

        $response = Mockery::mock();
        $response->exists = false;  // to keep the response simple

        $this->ncip->shouldReceive('lookupUser')->once()
            ->with($ltid)
            ->andReturn($response);

        $result = $user1->merge($user2);

        $this->assertNull($result);

        $this->assertTrue($user1->exists);
        $this->assertEquals($ltid, $user1->ltid);
        $this->assertEquals('Donald O.', $user1->firstname); // expects longest value to be preserved
        $this->assertEquals('Duck', $user1->lastname);
        $this->assertEquals('d.duck@andeby.com', $user1->email); // expects longest value to be preserved
        $this->assertEquals('1234', $user1->phone);

        $this->assertFalse($user2->exists);
    }

    /*
     * Merge that should fail due to differing LTIDs
     */
    public function testMergingCase3()
    {
        $ltid1 = 'eks0000003';
        $user1 = new User();
        $user1->ltid = $ltid1;
        $user1->firstname = 'Donald O.';
        $user1->lastname = 'Duck';
        $user1->email = 'd.duck@andeby.com';

        $ltid2 = 'eks0000004';
        $user2 = new User();
        $user2->ltid = $ltid2;
        $user2->firstname = 'Donald';
        $user2->lastname = 'Duck';
        $user2->email = 'd.duck@andeby.no';
        $user2->phone = '1234';

        $result = $user1->merge($user2);

        $this->assertNotNull($result);
    }
}
