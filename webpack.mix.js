const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.setPublicPath('public');
mix.setResourceRoot('../');

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
        require('tailwindcss'),
    ])
    .copy('node_modules/quill/dist/quill.snow.css', 'public/css/quill.snow.css')
    .copy('node_modules/quill/dist/quill.js', 'public/js/quill.js')
    .copy('node_modules/@fullcalendar/core/index.global.min.js', 'public/js/fullcalendar.min.js')
    .copy('node_modules/@fullcalendar/core/locales-all.global.min.js', 'public/js/fullcalendar-locales.min.js');

if (mix.inProduction()) {
    mix.version();
}
