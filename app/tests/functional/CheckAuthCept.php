<?php

$I = new TestGuy($scenario);

$I->wantTo('Check that I\'m redirected to the login page');
$I->amOnPage('/');

$I->seeCurrentUrlEquals('/login');
$I->login();
$I->seeCurrentUrlEquals('/loans/index');
