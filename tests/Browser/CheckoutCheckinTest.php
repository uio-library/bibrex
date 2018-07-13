<?php

namespace Tests\Browser;

use App\Item;
use App\Loan;
use App\Thing;
use App\User;
use Closure;
use Facebook\WebDriver\WebDriverBy;
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

        $faker = app('Faker\Generator');
        $faker->seed(1234);

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
                    ->waitForText('Til hvem?')
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
                    ->waitForText('Til hvem?')
                    ->type('user', $this->users[0]->name)
                    ->type('thing', $this->items[0]->barcode)
                    ->clickLink('Lån ut', 'button')
                    ->waitForText('registrert')
                    ->waitForText('nå nettopp');
            }
        );
    }

    /**
     * Test that we can enter the name of an non-existing user and get guided through the creation of a local user.
     */
    public function testCheckoutUsingNameOfNonExistingUser()
    {
        $this->browse(
            function (Browser $browser) {
                $faker = app('Faker\Generator');
                $browser->loginAs('post@eksempelbiblioteket.no');

                $lastname = $faker->lastName;
                $firstname = $faker->firstName;
                $fullname = "$lastname, $firstname";

                $browser->visit(new LoansPage)
                    ->waitForText('Til hvem?')
                    ->type('user', $fullname)
                    ->type('thing', $this->items[0]->barcode)
                    ->clickLink('Lån ut', 'button')
                    ->waitForText('Brukeren ble ikke funnet lokalt eller i Alma')
                    ->clickLink('opprette en lokal bruker')
                    ->waitForText('Opprett lokal bruker')
                    ->assertInputValue('lastname', $lastname)
                    ->assertInputValue('firstname', $firstname)
                    ->type('email', $faker->email)
                    ->press('Lagre')
                    ->waitForText('Brukeren ble opprettet')
                    ->assertInputValue('user', $fullname)
                    ->type('thing', $this->items[0]->barcode)
                    ->clickLink('Lån ut', 'button')
                    ->waitForText('registrert')
                    ->waitForText('nå nettopp');
            }
        );
    }

    /**
     * Test that we can enter the barcode of an non-existing user and get guided through the creation of a local user.
     */
    public function testCheckoutUsingBarcodeOfNonExistingUser()
    {
        $this->browse(
            function (Browser $browser) {
                $faker = app('Faker\Generator');
                $faker->addProvider(new \Tests\Faker\Library($faker));

                $browser->loginAs('post@eksempelbiblioteket.no');

                $barcode = $faker->userBarcode;
                $lastname = $faker->lastname;
                $firstname = $faker->firstname;
                $fullname = "$lastname, $firstname";

                $browser->visit(new LoansPage)
                    ->waitForText('Til hvem?')
                    ->type('user', $barcode)
                    ->type('thing', $this->items[0]->barcode)
                    ->clickLink('Lån ut', 'button')
                    ->waitForText('Strekkoden ble ikke funnet lokalt eller i Alma')
                    ->clickLink('opprett en lokal bruker')
                    ->waitForText('Opprett lokal bruker')
                    ->assertInputValue('barcode', $barcode)
                    ->type('lastname', $lastname)
                    ->type('firstname', $firstname)
                    ->type('email', $faker->email)
                    ->press('Lagre')
                    ->waitForText('Brukeren ble opprettet')
                    ->assertInputValue('user', $fullname)
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

                // First try a thing that cannot be loaned by name
                $browser->visit(new LoansPage)
                    ->waitForText('Til hvem?')
                    ->type('user', $this->users[0]->barcode)
                    ->type('thing', $this->things[0]->name)
                    ->clickLink('Lån ut', 'button')
                    ->waitForText('Utlån av denne tingen må gjøres med strekkode');

                // Then try a thing that can be loaned by name
                $browser->visit(new LoansPage)
                    ->waitForText('Til hvem?')
                    ->type('user', $this->users[0]->barcode)
                    ->type('thing', $this->things[1]->name)
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
                    ->waitForText('Til hvem?')
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
                $browser->loginAs('post@eksempelbiblioteket.no')
                    ->visit(new LoansPage)
                    ->waitForText('Til hvem?');

                $bn = $browser->driver->getCapabilities()->getBrowserName();
                if (!preg_match('/(chrome)/i', $bn)) {
                    $this->markTestSkipped('Skipping keyboard tests in unsupported browsers');

                    // IE11 problem 1:
                    // - Initially the active element is "body". Pressing TAB once shifts the focus to "html". Whaaaat?
                    //   https://automate.browserstack.com/builds/ea3e5eccf1332e734d4b9e9b3275d82ec3b46b40/sessions/635d8d15ce72cbcb8b0129aa0a02ee088796d72d#automate_button

                    // IE11 problem 2:
                    // - pressing Tab causes the text input to be selected instead :/
                    //   https://automate.browserstack.com/builds/ea3e5eccf1332e734d4b9e9b3275d82ec3b46b40/sessions/f9785ef217417a1744575c52b414da446a31c36d#automate_button

                    // Firefox problems: See CI-status.md

                    // Edge also has problems. At least it requires an initial tab.
                }

                $this->type($browser, $this->users[0]->barcode);
                $this->type($browser, WebDriverKeys::TAB);

                $this->type($browser, $this->items[0]->barcode);
                $this->type($browser, WebDriverKeys::ENTER);

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

                $browser->visit(new LoansPage);
                $browser->waitForText('Til hvem?');

                $bn = $browser->driver->getCapabilities()->getBrowserName();
                if (!preg_match('/(chrome)/i', $bn)) {
                    $this->markTestSkipped('Skipping keyboard tests in unsupported browsers');

                    // IE11 problem 1:
                    // - Initially the active element is "body". Pressing TAB once shifts the focus to "html". Whaaaat?
                    //   https://automate.browserstack.com/builds/ea3e5eccf1332e734d4b9e9b3275d82ec3b46b40/sessions/635d8d15ce72cbcb8b0129aa0a02ee088796d72d#automate_button

                    // IE11 problem 2:
                    // - pressing Tab causes the text input to be selected instead :/
                    //   https://automate.browserstack.com/builds/ea3e5eccf1332e734d4b9e9b3275d82ec3b46b40/sessions/f9785ef217417a1744575c52b414da446a31c36d#automate_button

                    // Firefox problems: See CI-status.md
                }

                $this->type($browser, $this->users[0]->barcode);
                $this->type($browser, WebDriverKeys::ENTER);

                $this->type($browser, $this->items[0]->barcode);
                $this->type($browser, WebDriverKeys::ENTER);

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
                $browser->loginAs('post@eksempelbiblioteket.no')
                    ->visit(new LoansPage)
                    ->waitForText('Til hvem?');

                $bn = $browser->driver->getCapabilities()->getBrowserName();
                if (!preg_match('/(chrome)/i', $bn)) {
                    $this->markTestSkipped('Skipping keyboard tests in unsupported browsers');

                    // IE11 problem 1:
                    // - Initially the active element is "body". Pressing TAB once shifts the focus to "html". Whaaaat?
                    //   https://automate.browserstack.com/builds/ea3e5eccf1332e734d4b9e9b3275d82ec3b46b40/sessions/635d8d15ce72cbcb8b0129aa0a02ee088796d72d#automate_button

                    // IE11 problem 2:
                    // - pressing Tab causes the text input to be selected instead :/
                    //   https://automate.browserstack.com/builds/ea3e5eccf1332e734d4b9e9b3275d82ec3b46b40/sessions/f9785ef217417a1744575c52b414da446a31c36d#automate_button

                    // Firefox problems: See CI-status.md
                }

                if (in_array(strtolower($browser->driver->getCapabilities()->getPlatform()), ['windows', 'win8', 'xp'])) {
                    // https://www.browserstack.com/automate/capabilities
                    $this->type($browser, [WebDriverKeys::ALT, 'r']);
                    //$activeElement->releaseKey(WebDriverKeys::ALT);
                } else {
                    $this->type($browser, [WebDriverKeys::CONTROL, 'r']);
                    //$activeElement->releaseKey(WebDriverKeys::CONTROL);
                }

                $browser->waitForText('Strekkode:');
                // $browser->pause(500);

                $this->type($browser, $item->barcode);
                $this->type($browser, WebDriverKeys::ENTER);

                $browser->waitForText('ble returnert')
                    ->pause(1000); // Give the loans table some time to update, to avoid errors in the log from the xhr request.
            }
        );
    }

    protected function type(Browser $browser, $keys)
    {
        $browser->pause(300);  // To avoid Internet Explorer 11 from choking
        $activeElement = $browser->driver->switchTo()->activeElement();

        // print("\nSending keys to " . $activeElement->getTagName() . ': ' . json_encode($keys) . "\n");
        $activeElement->sendKeys($keys);
    }
}
