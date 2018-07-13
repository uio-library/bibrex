<?php

namespace Tests\Browser;

use Tests\Browser\Pages\LibrariesPage;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LibrariesTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testCanCreateLibrary()
    {
        $this->browse(function (Browser $browser) {
            $faker = $this->app->make('Faker\Generator');
            $browser->loginAs('post@eksempelbiblioteket.no');

            $name = $faker->company;
            $pw = $faker->password(8);

            $browser->visit(new LibrariesPage)
                ->waitForText('Bibliotek (1)')
                ->clickLink('Nytt bibliotek')
                ->waitForText('Opprett nytt bibliotek')
                ->type('name', $name)
                ->pause(300)
                ->type('name_eng', $faker->company)
                ->pause(300)
                ->type('email', $faker->email)
                ->pause(300)
                ->type('password', $pw)
                ->pause(300)
                ->type('password2', $pw)
                ->pause(300)
                ->press('Lagre')
                ->waitForText('Biblioteket ble opprettet')
                ->assertSee('Bibliotek (2)');
        });
    }
}
