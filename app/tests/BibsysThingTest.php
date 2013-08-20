<?php


class BibsysThingTest extends TestCase {
 	
	public function testId()
	{
		$thing = Thing::where('name','BIBSYS-dokument')->first();

		$this->assertEquals(1, $thing->id);
    }

}