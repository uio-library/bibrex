<?php

$I = new TestGuy($scenario);

$I->wantTo('Check that database has been seeded');

$I->seeInDatabase('things', array('id' => '1', 'name' => 'BIBSYS-dokument'));
$I->seeInDatabase('libraries', array('id' => '1', 'name' => 'Eksempelbiblioteket'));

$I->amOnPage('/things');

// defined in tests/_helpers/TestHelper.php
$I->login();

$I->amOnPage('/things');
$I->seeInCurrentUrl('/things');
$I->see('Ting (3)');
$I->see('BIBSYS-dokument');

?>