<?php


class LoansControllerTest extends TestCase {
 	
	public function testIndex()
	{
		$this->call('GET', 'loans');

		$this->assertResponseOk();
		$this->assertViewHas('loans');
		$this->assertViewHas('things');
		$this->assertViewHas('loan_ids');
    }

	public function testStoreEmpty()
	{
		$this->call('POST', 'loans/store');

		$this->assertResponseStatus(302);
		$this->assertSessionHasErrors('ltid');
    }

	public function testStoreEmptyDokid()
	{
		$this->call('POST', 'loans/store', array(
			'ltid' => 'Duck, Donald', 
			'thing' => '1',
			'dokid' => ''
		));

		$this->assertResponseStatus(302);
		$this->assertSessionHasErrors('dokid_empty');
    }
}