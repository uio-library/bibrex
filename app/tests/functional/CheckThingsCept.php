<?php

$I = new TestGuy($scenario);

$I->wantTo('Check that database has been seeded');
$I->amOnPage('/things');

$I->seeInCurrentUrl('things');
$I->see('Ting (3)');
$I->see('BIBSYS-dokument');

?>