[![CircleCI](https://circleci.com/gh/scriptotek/bibrex.svg?style=svg)](https://circleci.com/gh/scriptotek/bibrex)
[![BrowserStack Status](https://www.browserstack.com/automate/badge.svg?badge_key=V0wybHdCbS9TQW9oRSs1ZitMMGxrdm04MWdQc0xWcU1NYzd5eTF1OFlRMD0tLXA5QktBekZUeEtTMnY0SnJPTXBoMkE9PQ==--b995a549fd2d22ceb6ee2ad93d5956d5254223ea)](https://www.browserstack.com/automate/public-build/V0wybHdCbS9TQW9oRSs1ZitMMGxrdm04MWdQc0xWcU1NYzd5eTF1OFlRMD0tLXA5QktBekZUeEtTMnY0SnJPTXBoMkE9PQ==--b995a549fd2d22ceb6ee2ad93d5956d5254223ea)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/scriptotek/bibrex/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/scriptotek/bibrex/?branch=master)

## BIBREX

A simple lending system for things that integrates with Alma.

### Install

1. `composer install` to update server-side deps.
2. Update config files in `app/config`
3. `php artisan migrate` to create the database tables
4. `php artisan db:seed` to seed initial database data
5. Make sure `app/storage` is writable by the www user.

### Development

Refresh database:

Disable logging to database when refreshing the database:

    LOG_CHANNEL=single php artisan migrate:refresh

### Tests

To run browser tests, download and start Selenium, then run `artisan dusk`

	wget https://selenium-release.storage.googleapis.com/3.8/selenium-server-standalone-3.13.0.jar
	java -jar selenium-server-standalone-3.13.0.jar &
	TEST_BROWSER=chrome artisan dusk

Unfortunately, testing with Firefox doesn't work at the moment due to an incompability between Selenium and php-webdriver.
See https://github.com/facebook/php-webdriver/issues/469.

Continuous integration browser testing supported by <br>
<a href="https://www.browserstack.com/"><img width="160" src="./doc/browserstack.svg" alt="BrowserStack"></a>

### Anonymizing returned loans

	php artisan anonymize

will anonymize all returned loans by moving them to an anonymous user.

### Større endringer

* [2018-02-12](https://github.com/scriptotek/bibrex/commit/c700caf4a9508679643f45b66af5cd5dd0e1c4b2) Påminnelser og Alma-import av brukerdata.
* [2016-18-10](https://github.com/scriptotek/bibrex/commit/ae059198c7f0a59a94e1742914060d53f75efdaf) Anonymisering av utlån.
* [2013-11-04](https://github.com/scriptotek/bibrex/commit/d8377cd1e2aa8feec105d2a106a0f172d7cba908) Institusjonsbasert pålogging, med mulighet for autopålogging fra bestemte IP-adresser.
* [2013-10-19](https://github.com/scriptotek/bibrex/commit/4e6263c7760dfb9bafe9a4996637b8f231bf18c6) Sync som Artisan-kommando for enkel kjøring fra cron.
* [2013-09-09](https://github.com/scriptotek/bibrex/commit/7a90441e68396e1ad3d6ebb2c3add1b30d680760) Sync: Hvis LTID har blitt aktivert i BIBSYS blir lån gjort på midlertid kort automatisk overført til brukerens LTID.
* [2013-09-09](https://github.com/scriptotek/bibrex/commit/394c3e4608114e4fba9e00b9fe58d78f8ef8f001) Mulighet for å deaktivere ting for utlån
* [2013-09-05](https://github.com/scriptotek/bibrex/commit/0ae2d9e929da84ced1520fa676c83b280683e767) Mulighet for å slette ting, men bare hvis ikke utlånt enda

