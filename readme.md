## BIBREX

Simple lending system using NCIP to connect to a library system.

### Install

1. `composer update` to update server-side deps. 
   If you get some `PHP Fatal error:  Class '...' not found`, comment out the 
   relevant lines under "Autoloaded Service Providers", do `composer update` 
   and then uncomment the lines.
2. Make sure `app/storage` is writable by the www user.
3. `php artisan config:publish danmichaelo/ncip` to create 
   the config file `app/config/packages/danmichael/ncip/config.php`
4. `php artisan config:publish loic-sharma/profiler` to create 
   the config file `app/config/packages/loic-sharma/profiler/config.php`
5. Update config files in `app/config`