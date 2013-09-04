## BIBREX

Simple lending system using NCIP to connect to a library system.

### Install

1. `composer install` to update server-side deps. 
2. Update config files in `app/config`
3. `php artisan migrate` to create the database tables
4. `php artisan db:seed` to seed initial database data
5. Make sure `app/storage` is writable by the www user.

[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/danmichaelo/bibrex/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

