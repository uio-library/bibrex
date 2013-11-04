<?php
namespace Codeception\Module;

// here you can define custom functions for TestGuy 

class TestHelper extends \Codeception\Module
{

    function login() {
		$this->getModule('Laravel4')->seeCurrentUrlEquals('/login');
		$this->getModule('Laravel4')->fillField('Epost', 'post@localhost');
		$this->getModule('Laravel4')->fillField('Passord', 'admin');
		$this->getModule('Laravel4')->click('Logg inn');
    }

}
