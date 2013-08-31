<?php

class LoansControllerTest extends TestCase {

	public function tearDown()
	{
		Mockery::close();
	}

	public function setUp()
	{
        parent::setUp();
		$this->ncip = Mockery::mock();
		App::instance('NcipClient', $this->ncip);
	}

	public function testIndexView()
	{
		$this->call('GET', 'loans');

		$this->assertResponseOk();
		$this->assertViewHas('loans');
		$this->assertViewHas('things');
		$this->assertViewHas('loan_ids');
    }

	public function testStoreBlankLoan()
	{
		$this->call('POST', 'loans/store');

		$this->assertResponseStatus(302);
		$this->assertSessionHasErrors('ltid');
    }

	public function testStoreLoanWithInvalidThing()
	{
		$this->call('POST', 'loans/store', array(
			'ltid' => 'Duck, Donald',
			'thing' => '999'
		));

		$this->assertResponseStatus(302);
		$this->assertSessionHasErrors('thing_not_found');
    }

	public function testStoreBibsysLoanWithoutDokid()
	{
		$this->call('POST', 'loans/store', array(
			'ltid' => 'Duck, Donald',
			'thing' => '1',
			'dokid' => ''
		));

		$this->assertResponseStatus(302);
		$this->assertSessionHasErrors('dokid_empty');
    }

    public function testStoreLoanWithUnknownDokid()
	{
		$this->call('POST', 'loans/store', array(
			'ltid' => 'Duck, Donald',
			'thing' => '1',
			'dokid' => '99ns00000'
		));

		$this->assertResponseStatus(302);
		$this->assertSessionHasErrors('document_not_found');
    }

    public function testStoreLoanUsingGuestNumber()
	{

		// Define the guest LTID
		$dummy_ltid = 'umn1000000';
		Config::set('app.guest_ltid', $dummy_ltid); // could we mock this? I've not found an elegant way to do it yet

		// Mock the NCIP client
		$response = new Danmichaelo\Ncip\UserResponse;
		$this->ncip->shouldReceive('lookupUser')->once()
			 ->andReturn($response);

		// Store a loan to a *new* User using the guest LTID
		// This should not be allowed, since the guest LTID
		// should not be connected to any specific user
		$this->call('POST', 'loans/store', array(
			'ltid' => 'Duck, Donald',
			'thing' => '1',
			'ltid' => $dummy_ltid,
			'dokid' => '12k211446'
		));

		$this->assertResponseStatus(302);
		$this->assertSessionHasErrors('loan_save_error');
    }

    public function testNcipLookupResponse()
	{
		$response = $this->call('GET', 'users/ncip-lookup/1');
		$this->assertResponseStatus(200);

		$json = $response->getContent();
		$this->assertJSON($json);

		$json = json_decode($json);
		$this->assertFalse($json->exists);
    }

}