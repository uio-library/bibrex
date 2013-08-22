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
		$this->call('POST', 'loans/store', array(
			'ltid' => 'Duck, Donald', 
			'thing' => '1',
			'ltid' => 'umn1002157',
			'dokid' => '12k211446'
		));

		$this->assertResponseStatus(302);
		$this->assertSessionHasErrors('loan_save_error');
    }

}