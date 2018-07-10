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

    public function testLogin()
    {
        $user = factory(Library::class)->create(
            [
            'email' => 'user@example.net',
            ]
        );

        $this->browse(
            function (Browser $browser) use ($user) {
                $browser->visit(new LoginPage)
                    ->type('email', $user->email)
                    ->type('password', 'secret')
                    ->press('Logg inn')
                    ->pause(500)
                    ->assertPathIs('/loans');
            }
        );
    }
}
