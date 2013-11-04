## BIBREX

Simple lending system using NCIP to connect to a library system.

### Install

1. `composer install` to update server-side deps. 
2. Update config files in `app/config`
3. `php artisan migrate` to create the database tables
4. `php artisan db:seed` to seed initial database data
5. Make sure `app/storage` is writable by the www user.

### Større endringer

* [4.11](https://github.com/danmichaelo/bibrex/commit/d8377cd1e2aa8feec105d2a106a0f172d7cba908) Institusjonsbasert pålogging, med mulighet for autopålogging fra bestemte IP-adresser.
* [19.10](https://github.com/danmichaelo/bibrex/commit/4e6263c7760dfb9bafe9a4996637b8f231bf18c6) Sync som Artisan-kommando for enkel kjøring fra cron.
* [9.9](https://github.com/danmichaelo/bibrex/commit/7a90441e68396e1ad3d6ebb2c3add1b30d680760) Sync: Hvis LTID har blitt aktivert i BIBSYS blir lån gjort på midlertid kort automatisk overført til brukerens LTID.
* [9.9](https://github.com/danmichaelo/bibrex/commit/394c3e4608114e4fba9e00b9fe58d78f8ef8f001) Mulighet for å deaktivere ting for utlån
* [5.9](https://github.com/danmichaelo/bibrex/commit/0ae2d9e929da84ced1520fa676c83b280683e767) Mulighet for å slette ting, men bare hvis ikke utlånt enda

[![Build Status](https://travis-ci.org/danmichaelo/bibrex.png?branch=master)](https://travis-ci.org/danmichaelo/bibrex)
[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/danmichaelo/bibrex/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

