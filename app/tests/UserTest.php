<?php

use Way\Tests\Factory;

class UserTest extends TestCase {
    use Way\Tests\ModelHelpers;

    /*
     * Validation should fail if a user is created with the guest number LTID
     */
    public function testUsingGuestNumber()
    {
		// Define some guest LTID
		$guest_ltid = 'umn1000000';
		$some_other_ltid = 'umn1000001';
		Config::set('app.guest_ltid', $guest_ltid); // could we mock this? I've not found an elegant way to do it yet

        // Using the guest LTID should not be allowed
        $loan = Factory::make('User', ['ltid' => $guest_ltid]);
        $this->assertNotValid($loan);

        // Using some other LTID should be allowed
        $loan = Factory::make('User', ['ltid' => $some_other_ltid]);
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

        $user2 = new User();
        $user2->ltid = 'uo00000001';
        $user2->firstname = 'Donald';
        $user2->lastname = 'Duck';
        $user2->email = 'd.duck@andeby.no';
        $user2->phone = '1234';

        $result = $user1->merge($user2);

        $this->assertNull($result);

        $this->assertTrue($user1->exists);
        $this->assertEquals('uo00000001', $user1->ltid);
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

        $user2 = new User();
        $user2->ltid = 'uo00000001';
        $user2->firstname = 'Donald';
        $user2->lastname = 'Duck';
        $user2->email = 'd.duck@andeby.no';
        $user2->phone = '1234';

        $result = $user1->merge($user2);

        $this->assertNull($result);

        $this->assertTrue($user1->exists);
        $this->assertEquals('uo00000001', $user1->ltid);
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
        $user1 = new User();
        $user1->ltid = 'uo00000001';
        $user1->firstname = 'Donald O.';
        $user1->lastname = 'Duck';
        $user1->email = 'd.duck@andeby.com';

        $user2 = new User();
        $user2->ltid = 'uo00000002';
        $user2->firstname = 'Donald';
        $user2->lastname = 'Duck';
        $user2->email = 'd.duck@andeby.no';
        $user2->phone = '1234';

        $result = $user1->merge($user2);

        $this->assertNotNull($result);
    }
}
