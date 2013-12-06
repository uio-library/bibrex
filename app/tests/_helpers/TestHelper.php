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

    function configureGuestCard() {
		$this->getModule('Laravel4')->amOnPage('/my/account');

	    $this->debug('Configuring guest card: eks1234567');
		$this->getModule('Laravel4')->fillField('LTID for gjestekort', 'eks1234567');
		$this->getModule('Laravel4')->checkOption('Bruk gjestekort hvis brukers kort ikke virker');
		$this->getModule('Laravel4')->checkOption('Bruk gjestekort for kortløse utlån');
		$this->getModule('Laravel4')->click('Lagre');

    }

}
