<?php

namespace Tests\Browser\Pages;

use App\Thing;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;

class ThingsPage extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/things';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathIs($this->url());
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@element' => '#selector',
        ];
    }

    /**
     * Create a new thing.
     *
     * @param  \Laravel\Dusk\Browser $browser
     * @param  string                $name
     * @return void
     */
    public function createThing(Browser $browser, $name)
    {
        $browser->clickLink('Ny ting')
            ->type('name', $name)
            ->press('Lagre');
    }

    /**
     * Activate a thing for the current library.
     *
     * @param  \Laravel\Dusk\Browser $browser
     * @param  Thing                 $thing
     * @return void
     */
    public function activateThing(Browser $browser, Thing $thing)
    {
        $el = $browser->driver->findElement(WebDriverBy::xpath("//a[@href='/things/{$thing->id}']/.."));
        // TODO: Do things
    }
}
