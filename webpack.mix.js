let mix = require('laravel-mix');
require('laravel-mix-purgecss');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css')
   .copy('node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css', 'public/css/')
   .purgeCss({
        whitelistPatterns: [
            /^alert-/, /^visible/,               // Alerts
            /^tt-/, /typeahead/,                 // Typeahead.vue
            /(bs-)?tooltip/, /^(fade|show)$/,    // VueBootstrap tooltip
            /dataTable/,                         // DataTables
            /^badge-/,                           // logs.index
            /^col-/,                             // Bootstrap column layout
        ],
   })
   .version();
