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

    public function setUp(): void
    {
        parent::setUp();

        $faker = app('Faker\Generator');
        $faker->seed(1234);
    }

    public function testLogin()
    {
        $user = Library::factory()->create();

        $this->browse(
            function (Browser $browser) use ($user) {
                $browser->visit(new LoginPage)
                    ->waitForText('Logg inn')
                    ->type('email', $user->email)
                    ->type('password', 'secret')
                    ->press('Logg inn')
                    ->waitForText('Utlån')
                    ->assertPathIs('/loans');
            }
        );
    }
}
