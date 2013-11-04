<?php

$I = new TestGuy($scenario);

$I->wantTo('Check that I\'m able to login');
$I->amOnPage('/');

$I->seeCurrentUrlEquals('/libraries/login');

// defined in tests/_helpers/TestHelper.php
$I->login();

$I->seeCurrentUrlEquals('/loans/index');
