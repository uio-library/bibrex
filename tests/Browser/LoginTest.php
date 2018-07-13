<?php

namespace Tests\Browser;

use App\Library;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Browser\Pages\LoginPage;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $faker = app('Faker\Generator');
        $faker->seed(1234);
    }

    public function testLogin()
    {
        $user = factory(Library::class)->create();

        $this->browse(
            function (Browser $browser) use ($user) {
                $browser->visit(new LoginPage)
                    ->waitForText('Logg inn')
                    ->pause(500)
                    ->type('email', $user->email)
                    ->type('password', 'secret')
                    ->press('Logg inn')
                    ->waitForText('Utlån')
                    ->assertPathIs('/loans');
            }
        );
    }
}
