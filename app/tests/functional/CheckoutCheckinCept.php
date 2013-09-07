<?php

$I = new TestGuy($scenario);

$I->wantTo('Checkout and return a document');
$I->amOnPage('/');

$I->seeInCurrentUrl('loans');
$I->see('Nytt utlån');

$I->fillField('Til hvem?', 'Duck, Donald');
$I->selectOption('Hva?','BIBSYS-dokument');
$I->fillField('DOKID:', '94nf00228');
$I->click('Lån ut!');

$I->see('Dokumentet ble lånt ut');
$I->seeInDatabase('loans', ['document_id' => '1']);
$I->seeInDatabase('documents', ['thing_id' => '1']);
$I->seeInDatabase('documents', ['dokid' => '94nf00228']);

$I->see('Siden dette er en ny låner');

$I->fillField('Mobil', '90207510');
$I->fillField('Epost', 'danmichaelo@gmail.com');
$I->click('Lagre');

$I->see('Informasjonen ble lagret');

$I->seeLink('The quark and the jaguar : adventures in the simple and the complex (94nf00228)');
$I->click('The quark and the jaguar : adventures in the simple and the complex (94nf00228)');

$I->seeLink('Returnér dokument');
$I->click('Returnér dokument');

$I->see('ble levert inn');

?>