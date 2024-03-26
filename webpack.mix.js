let mix = require('laravel-mix');

mix.js('src/scripts/frontend.js', 'assets/scripts')
    .js('src/scripts/backend/general.js', 'assets/scripts')
    .js('src/scripts/backend/settings.js', 'assets/scripts')

mix.sass('src/scss/main.scss', 'assets/styles')
    .sass('src/scss/backend/admin.scss', 'assets/styles')
    .sass('src/scss/backend/protection.scss', 'assets/styles');
