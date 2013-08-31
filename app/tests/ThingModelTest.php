<?php


class ThingModelTest extends TestCase {

	public function testBibsysDocumentHasCorrectId()
	{
		$thing = Thing::where('name','BIBSYS-dokument')->first();

		$this->assertEquals(1, $thing->id);
    }

}