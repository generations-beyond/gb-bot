let mix = require('laravel-mix');
require('mix-env-file');
mix.env(process.env.ENV_FILE);

const homedir = require('os').homedir();
const domain = process.env.DOMAIN;
const useHTTPS = process.env.USEHTTPS === "true";

mix.js('src/scripts/frontend.js', 'assets/scripts')
    .js('src/scripts/backend/general.js', 'assets/scripts')
    .js('src/scripts/backend/settings.js', 'assets/scripts')

mix.sass('src/scss/main.scss', 'assets/styles')
    .sass('src/scss/backend/admin.scss', 'assets/styles')
    .sass('src/scss/backend/protection.scss', 'assets/styles');

mix.browserSync({
    proxy: `http${useHTTPS ? 's' : ''}://${domain}`,
    "host": domain,
    https: useHTTPS ? {
        key: homedir + '/.config/valet/Certificates/' + domain + '.key',
        cert: homedir + '/.config/valet/Certificates/' + domain + '.crt',
    } : false,
    files: ['assets/**/*', '**/*.php'],
    open: 'external'
});