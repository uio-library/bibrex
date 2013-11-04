<?php
namespace Codeception\Module;

// here you can define custom functions for TestGuy

class TestHelper extends \Codeception\Module
{

	// Remember to run `codecept build` after adding new methods here

    function login() {
		$this->getModule('Laravel4')->seeCurrentUrlEquals('/libraries/login');
		$this->getModule('Laravel4')->fillField('Bibliotek', 'Eksempelbiblioteket');
		$this->getModule('Laravel4')->fillField('Passord', 'admin');
		$this->getModule('Laravel4')->click('Logg inn');
    }

    function setupGuestNumber() {
		$this->getModule('Laravel4')->amOnPage('/libraries/my');
	    $this->debug('Filling "LTID for gjestekort": eks1234567');
		$this->getModule('Laravel4')->fillField('LTID for gjestekort', 'eks1234567');
		$this->getModule('Laravel4')->click('Lagre');
    }

}
