<?php

namespace Tests\Browser;

use App\Item;
use App\Loan;
use App\Thing;
use App\User;
use Facebook\WebDriver\WebDriverKeys;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoansPage;
use function Stringy\create as s;

class CheckoutCheckinTest extends DuskTestCase
{
    use DatabaseMigrations;

    /* @var Thing[] */
    private $things;

    /* @var Item[] */
    private $items;

    /* @var User[] */
    private $users;

    /**
     * Create a few things, a few items and a few patrons
     */
    public function setUp()
    {
        parent::setUp();

        // Make a few things
        $this->things = factory(Thing::class, 5)->create();

        // And a few items
        $this->items = $this->things->flatMap(
            function (Thing $thing) {
                $items = factory(Item::class, 3)->make();
                $thing->items()->saveMany($items);
                return $items;
            }
        );

        // And a few patrons
        $this->users = factory(User::class, 10)->create();
    }

    /**
     * Test that we can checkout an item using barcodes of item and user.
     */
    public function testCheckoutUsingBarcodes()
    {
        $this->browse(
            function (Browser $browser) {
                $browser->loginAs('post@eksempelbiblioteket.no');

                $browser->visit(new LoansPage)
                    ->type('user', $this->users[0]->barcode)
                    ->type('thing', $this->items[0]->barcode)
                    ->clickLink('Lån ut', 'button')
                    ->waitForText('registrert')
                    ->waitForText('nå nettopp');
            }
        );
    }

    /**
     * Test that we can checkout an item using the name of a user.
     */
    public function testCheckoutUsingNameOfUser()
    {
        $this->browse(
            function (Browser $browser) {
                $browser->loginAs('post@eksempelbiblioteket.no');

                $browser->visit(new LoansPage)
                    ->type('user', $this->users[0]->name)
                    ->type('thing', $this->items[0]->barcode)
                    ->clickLink('Lån ut', 'button')
                    ->waitForText('registrert')
                    ->waitForText('nå nettopp');
            }
        );
    }

    /**
     * Test that we can checkout an item using the name of a thing if loans_without_barcode is activated.
     */
    public function testCheckoutUsingNameOfThing()
    {
        $settings = $this->things[1]->getLibrarySettingsAttribute($this->currentLibrary);
        $settings->loans_without_barcode = true;
        $settings->save();

        $this->browse(
            function (Browser $browser) {
                $browser->loginAs('post@eksempelbiblioteket.no');

                $browser->visit(new LoansPage)
                    ->type('user', $this->users[0]->barcode)
                    ->type('thing', $this->things[0]->name)
                    ->clickLink('Lån ut', 'button')
                    ->waitForText('Utlån av denne tingen må gjøres med strekkode');

                $browser->type('thing', $this->things[1]->name)
                    ->clickLink('Lån ut', 'button')
                    ->waitForText('registrert')
                    ->waitForText('nå nettopp');
            }
        );
    }

    /**
     * Test that we can make a checkin easily.
     */
    public function testCheckin()
    {
        // Make a loan
        $item = $this->items[0];
        $user = $this->users[0];

        $item->loans()->save(
            factory(Loan::class)->make([
                'user_id' => $user->id,
                'library_id' => $this->currentLibrary->id,
                'as_guest' => false,
            ])
        );

        $this->browse(
            function (Browser $browser) use ($item) {
                $browser->loginAs('post@eksempelbiblioteket.no');

                $browser->visit(new LoansPage)
                    ->click('#nav-checkin-tab')
                    ->waitForText('Strekkode:')
                    ->type('barcode', $item->barcode)
                    ->clickLink('Returner', 'button')
                    ->waitForText('ble returnert')
                        ->pause(1000); // Give the loans table some time to update, to avoid errors in the log from the xhr request.
            }
        );
    }

    /**
     * Test that we can make a loan easily without using the mouse.
     */
    public function testCheckoutUsingKeyboardOnly()
    {
        $this->browse(
            function (Browser $browser) {
                $browser->loginAs('post@eksempelbiblioteket.no');

                $keyboard = $browser->visit(new LoansPage)
                    ->driver->getKeyboard();

                $keyboard->sendKeys([
                    $this->users[0]->barcode,
                    WebDriverKeys::TAB,
                    $this->items[0]->barcode,
                    WebDriverKeys::ENTER,
                ]);

                $browser->waitForText('registrert')
                    ->waitForText('nå nettopp');
            }
        );
    }

    /**
     * Test that Enter does not submit the form too early.
     */
    public function testCheckoutUsingEnterOnly()
    {
        $this->browse(
            function (Browser $browser) {
                $browser->loginAs('post@eksempelbiblioteket.no');

                $keyboard = $browser->visit(new LoansPage)
                    ->driver->getKeyboard();

                $keyboard->sendKeys([
                    $this->users[0]->barcode,
                    WebDriverKeys::ENTER,
                    $this->items[0]->barcode,
                    WebDriverKeys::ENTER,
                ]);

                $browser->waitForText('registrert')
                    ->waitForText('nå nettopp');
            }
        );
    }

    /**
     * Test that we can make a checkin easily without using the mouse.
     */
    public function testCheckinUsingKeyboardOnly()
    {
        // Make a loan
        $item = $this->items[0];
        $user = $this->users[0];

        $item->loans()->save(
            factory(Loan::class)->make([
                'user_id' => $user->id,
                'library_id' => $this->currentLibrary->id,
                'as_guest' => false,
            ])
        );

        $this->browse(
            function (Browser $browser) use ($item) {
                $browser->loginAs('post@eksempelbiblioteket.no');

                $keyboard = $browser->visit(new LoansPage)
                    ->driver->getKeyboard();

                if (s(config('testing.caps.browser'))->contains('internet explorer')) {
                    $this->markTestSkipped('Keyboard shortcuts do not work in IE11.');
                    return;
                } elseif (s(config('testing.caps.platform'))->contains('Windows')) {
                    $keyboard->sendKeys([WebDriverKeys::ALT, 'r']);
                    $keyboard->releaseKey(WebDriverKeys::ALT);
                } else {
                    $keyboard->sendKeys([WebDriverKeys::CONTROL, 'r']);
                    $keyboard->releaseKey(WebDriverKeys::CONTROL);
                }

                $browser->waitForText('Strekkode:');
                // $browser->pause(500);

                $keyboard->sendKeys([$item->barcode, WebDriverKeys::ENTER]);
                $browser->waitForText('ble returnert')
                    ->pause(1000); // Give the loans table some time to update, to avoid errors in the log from the xhr request.
            }
        );
    }
}
