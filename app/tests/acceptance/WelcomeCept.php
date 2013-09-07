<?php

$I = new WebGuy($scenario);

$I->wantTo('Checkout and return a document');
$I->amOnPage('/');

$I->seeInCurrentUrl('loans');
$I->see('Nytt utlån');

$I->fillField('Til hvem?', 'Duck, Donald');
$I->selectOption('Hva?','BIBSYS-dokument');
$I->fillField('DOKID:', '94nf00228');
$I->click('Lån ut!');

$I->see('Dokumentet ble lånt ut');
$I->seeLink('The quark and the jaguar');
$I->click('The quark and the jaguar');

$I->seeLink('Returnér dokument');
$I->click('Returnér dokument');

$I->see('ble levert inn');

?>