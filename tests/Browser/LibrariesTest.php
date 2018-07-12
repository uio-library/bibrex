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
                ->assertSee('Bibliotek (1)')
                ->clickLink('Nytt bibliotek')
                ->assertSee('Opprett nytt bibliotek')
                ->type('name', $name)
                ->type('name_eng', $faker->company)
                ->type('email', $faker->email)
                ->type('password', $pw)
                ->type('password2', $pw)
                ->press('Lagre')
                ->waitForText('Biblioteket ble opprettet')
                ->assertSee('Bibliotek (2)');
        });
    }
}
