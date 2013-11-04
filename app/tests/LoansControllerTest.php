<?php

class LoansControllerTest extends TestCase {

	public function tearDown()
	{
		Mockery::close();
	}

	public function setUp()
	{
        parent::setUp();

        // Mock NCIP
		$this->ncip = Mockery::mock();
		App::instance('NcipClient', $this->ncip);

        // Mock Curl
		$this->curl = Mockery::mock();
		App::instance('Curl', $this->curl);

	}

	public function testIndexView()
	{
		$this->curl->shouldReceive('simple_get')->never();
		$this->call('GET', 'loans');

		$this->assertResponseOk();
		$this->assertViewHas('loans');
		$this->assertViewHas('things');
		$this->assertViewHas('loan_ids');
    }

	public function testStoreBlankLoan()
	{
		$this->curl->shouldReceive('simple_get')->never();
		$this->call('POST', 'loans/store');

		$this->assertResponseStatus(302);
		$this->assertSessionHasErrors('ltid');
    }

	public function testStoreLoanWithInvalidThing()
	{
		$this->curl->shouldReceive('simple_get')->never();
		$this->call('POST', 'loans/store', array(
			'ltid' => 'Duck, Donald',
			'thing' => '999'
		));

		$this->assertResponseStatus(302);
		$this->assertSessionHasErrors('thing_not_found');
    }

	/*
	public function testStoreBibsysLoanWithoutDokid()
	{
		$this->call('POST', 'loans/store', array(
			'ltid' => 'Duck, Donald',
			'thing' => '1',
			'dokid' => ''
		));

		$this->assertResponseStatus(302);
		$this->assertSessionHasErrors('dokid_empty');
    }*/

    public function testStoreLoanWithUnknownDokid()
	{
		$this->curl->shouldReceive('simple_get')
			->once()
			->andReturn('{"objektid":"","dokid":"","heftid":""}');

		$this->call('POST', 'loans/store', array(
			'ltid' => 'Duck, Donald',
			'thing' => '1',
			'dokid' => '99ns00000'
		));

		$this->assertResponseStatus(302);
		$this->assertSessionHasErrors('document_not_found');
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

	public function testNcipStoreitemForNewUserWithoutLtid()
	{
		$ltid = 'uo00000000';
		$dokid = '99ns00000';
		//Config::set('app.guest_ltid', $ltid);
		//$ltid = Auth::user()->guest_ltid;

		// TODO: Mock Auth
		//Auth::loginUsingId(1);

		$this->curl->shouldReceive('simple_get')
			->andReturnUsing(function($url) {
				$url = explode('=', $url);
				$this->assertEquals('http://linode.biblionaut.net/services/getids.php?id', $url[0]);
				$dokid = $url[1];
				return '{"objektid":"","dokid":"' . $dokid .'","heftid":""}';
			});

		$this->ncip->shouldReceive('lookupUser')->never();

		$c = new Danmichaelo\Ncip\CheckOutResponse(null);
		$this->ncip->shouldReceive('checkOutItem')->once()
			->with($ltid, $dokid)
			->andReturn($c);

		$this->call('POST', 'loans/store', array(
			'ltid' => 'Duck, Donald',
			'thing' => '1',
			'dokid' => $dokid
		));

		$this->assertResponseStatus(302);
    }

	public function testNcipStoreitemForNewUserWithLtid()
	{

		$ltid = 'uo00000001';
		$dokid = '99ns00000';

		$this->curl->shouldReceive('simple_get')
			->andReturnUsing(function($url) {
				$url = explode('=', $url);
				$this->assertEquals('http://linode.biblionaut.net/services/getids.php?id', $url[0]);
				$dokid = $url[1];
				return '{"objektid":"","dokid":"' . $dokid .'","heftid":""}';
			});

		$u = new Danmichaelo\Ncip\UserResponse;
		$u->exists = true;
		$u->userId = $ltid;
		$u->firstName = 'Donald';
		$u->lastName = 'Duck';

		$this->ncip->shouldReceive('lookupUser')->once()
			->with($ltid)
			->andReturn($u);

		$c = new Danmichaelo\Ncip\CheckOutResponse(null);
		$this->ncip->shouldReceive('checkOutItem')->once()
			->with($ltid, $dokid)
			->andReturn($c);

		$this->call('POST', 'loans/store', array(
			'ltid' => $ltid,
			'thing' => '1',
			'dokid' => $dokid
		));

		$this->assertResponseStatus(302);
    }

}