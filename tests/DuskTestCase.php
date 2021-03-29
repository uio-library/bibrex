<?php

namespace Tests;

use App\Library;
use Closure;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\RequestOptions;
use PHPUnit\Runner\BaseTestRunner;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /* @var Library */
    protected $currentLibrary;

    protected $sessionId;
    protected $failing = false;

    /**
     * Override browse() so we can do some setup and teardown needed for browserstack.
     *
     * @param  \Closure  $callback
     * @throws \Exception
     * @throws \Throwable
     */
    public function browse(Closure $callback)
    {
        // If we are using BrowserStack, check if we have sessions available before starting
        if (config('testing.browserstack.key')) {
            $http = new HttpClient();
            while (true) {
                $response = $http->request('GET', 'https://api.browserstack.com/automate/plan.json', [
                    RequestOptions::AUTH => [
                        config('testing.browserstack.user'),
                        config('testing.browserstack.key'),
                    ],
                ]);
                $response = json_decode($response->getBody());
                if ($response->parallel_sessions_running < $response->parallel_sessions_max_allowed) {
                    break;
                }
                print("\nWaiting for free browserstack sessions...\n");
                sleep(10);
            }
        }

        // Create a new Browser
        parent::browse(function (Browser $browser) use ($callback) {

            // Store the session ID, so we can use it in tearDown later.
            $this->sessionId = $browser->driver->getSessionID();

            // Run test
            $callback($browser);
        });
    }

    /**
     * Setup the test environment before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionId = null;

        $this->currentLibrary = Library::factory()->create([
            'email' => 'post@eksempelbiblioteket.no',
        ]);
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Mark tests as passed or failed on BrowserStack
        // https://www.browserstack.com/automate/rest-api

        // If we are using BrowserStack, check if we have sessions available before starting
        if (config('testing.browserstack.key')) {
            $status = ($this->getStatus() == BaseTestRunner::STATUS_PASSED) ? 'passed' : 'failed';
            $reason = $this->getStatusMessage();
            $reportStatus = ($this->sessionId && $this->getStatus() != BaseTestRunner::STATUS_SKIPPED);
            if ($status == 'failed') {
                $this->failing = true;
            }

            if ($this->failing && $status == 'passed') {
                // If one of the tests failed, we consider the whole session to be failing.
                $reportStatus = false;
            }

            if ($reportStatus) {
                $http = new HttpClient();
                $http->request('PUT', sprintf('https://api.browserstack.com/automate/sessions/%s.json', $this->sessionId), [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'application/json',
                    ],
                    RequestOptions::JSON => [
                        'status' => $status,
                        'reason' => $reason,
                    ],
                    RequestOptions::AUTH => [
                        config('testing.browserstack.user'),
                        config('testing.browserstack.key'),
                    ],
                ]);
            }
        }

        parent::tearDown();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        Browser::$waitSeconds = 15;

        $caps = config('testing.caps');
        if (is_null($caps['browserVersion'])) {
            unset($caps['browserVersion']);
        }

        if ($caps['browserName'] == 'chrome') {
            $caps['chromeOptions'] = (new ChromeOptions)->addArguments([
                '--disable-gpu',
                // '--headless'
            ]);
        }

        $caps['build'] = env('CIRCLE_SHA1');
        $caps['name'] = get_class($this);

        return RemoteWebDriver::create(config('testing.host'), $caps);
    }

    /**
     * Return the default user to authenticate.
     *
     * @return \App\Library|int|null
     * @throws \Exception
     */
    protected function user()
    {
        return $this->currentLibrary;
    }
}
