<?php


class LoansControllerTest extends TestCase {
 	
	public function test()
	{

    	$view = 'loans.index';
		$this->registerNestedView($view);
		$this->call('GET', 'loans');


		$this->assertResponseOk();


		$this->assertNestedViewHas($view, 'loans');
    }

}