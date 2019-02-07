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

// Extend Mix with the "i18n" method, that loads the vue-i18n-loader
mix.extend( 'i18n', new class {
        webpackRules() {
            return [
                {
                    resourceQuery: /blockType=i18n/,
                    type:          'javascript/auto',
                    loader:        '@kazupon/vue-i18n-loader',
                },
            ];
        }
    }(),
);

mix.i18n()
   .js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css')
   .copy('node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css', 'public/css/')
   .purgeCss({
        whitelistPatterns: [
            /^alert-/, /^visible/,               // Alerts
            /^tt-/, /typeahead/,                 // Typeahead.vue
            /(bs-)?tooltip/, /^(fade|show)$/,    // VueBootstrap tooltip
            /popover/, /arrow/,                  // VueBootstrap popover
            /dataTable/,                         // DataTables
            /^badge-/,                           // logs.index
            /col-/,                              // Bootstrap column layout used by datatables
        ],
   });