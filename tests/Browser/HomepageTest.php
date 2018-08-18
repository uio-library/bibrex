<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class HomepageTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testHome()
    {
        $this->browse(
            function (Browser $browser) {
                $browser->visit('/')
                    ->waitForText('Logg inn for å bruke BIBREX')
                    ->assertSee('Logg inn');
            }
        );
    }
}
