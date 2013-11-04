<?php

$I = new TestGuy($scenario);

$I->wantTo('Check that database has been seeded');

$I->seeInDatabase('things', ['id' => '1', 'name' => 'BIBSYS-dokument']);
$I->seeInDatabase('users', ['id' => '1', 'firstname' => 'Admin']);

$I->amOnPage('/things');
$I->login();

$I->amOnPage('/things');
$I->seeInCurrentUrl('/things');
$I->see('Ting (3)');
$I->see('BIBSYS-dokument');

?>